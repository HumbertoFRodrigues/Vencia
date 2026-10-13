<?php

namespace Tests\Feature\Negocio;

use App\Enums\ServicoStatus;
use App\Mail\LembreteVencimentoMail;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\LembreteEnviado;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Models\User;
use App\Services\ServicoStatusService;
use App\Services\Totais;
use App\Services\VerificacaoDiariaService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ServicosTest extends TestCase
{
    use RefreshDatabase;

    private ServicoStatusService $status;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-13 10:00:00');
        $this->status = app(ServicoStatusService::class);

        Configuracao::create(['chave' => 'empresa', 'valor' => ['nome' => 'Teste', 'email' => 'empresa@teste.mz', 'moeda' => 'MZN']]);
        Configuracao::create(['chave' => 'lembretes', 'valor' => [
            'verificacao_diaria' => true, 'd30' => false, 'd15' => false, 'd7' => true, 'd3' => true, 'd1' => false, 'no_dia' => true, 'apos_vencimento' => true,
        ]]);
        Configuracao::create(['chave' => 'email_template_renovacao', 'valor' => ['assunto' => 'Vence [DATA]', 'corpo' => "Olá [NOME].\n\nO serviço [SERVIÇO] vence em [DATA]."]]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function servico(array $attrs = [], ?string $email = 'cliente@teste.mz'): Servico
    {
        $cliente = Cliente::create(['nome' => 'Cliente', 'email' => $email, 'status' => 'activo', 'desde' => '2026-01-01']);

        return Servico::create($attrs + [
            'cliente_id' => $cliente->id, 'nome' => 'Hospedagem', 'categoria' => 'hospedagem', 'valor' => 1200,
            'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '2026-01-01', 'vencimento' => '2026-11-01', 'status' => 'activo',
        ]);
    }

    public function test_renewal_advances_from_the_current_vencimento(): void
    {
        $s = $this->servico(['vencimento' => '2026-09-01']);
        $this->status->renovar($s, ['metodo' => 'mpesa']);

        $this->assertSame('2026-10-01', $s->fresh()->vencimento->toDateString());
        $this->assertSame(1, Pagamento::count());
    }

    public function test_renewal_keeps_the_billing_day_through_short_months(): void
    {
        $s = $this->servico(['inicio' => '2026-01-31', 'vencimento' => '2027-01-31']);

        $this->status->renovar($s, ['metodo' => 'mpesa']);
        $this->assertSame('2027-02-28', $s->fresh()->vencimento->toDateString());

        $this->status->renovar($s->fresh(), ['metodo' => 'mpesa']);
        $this->assertSame('2027-03-31', $s->fresh()->vencimento->toDateString());
    }

    public function test_renewing_a_long_overdue_servico_keeps_it_vencido(): void
    {
        $s = $this->servico(['vencimento' => '2026-07-01', 'status' => 'vencido']);
        $this->status->renovar($s, ['metodo' => 'mpesa']);

        $this->assertSame(ServicoStatus::Vencido, $s->fresh()->status);
    }

    public function test_a_cancelled_servico_cannot_be_renewed(): void
    {
        $s = $this->servico(['status' => 'cancelado']);

        $this->expectException(RuntimeException::class);
        $this->status->renovar($s, ['metodo' => 'mpesa']);
    }

    public function test_one_off_servicos_never_become_overdue(): void
    {
        $s = $this->servico(['periodicidade' => 'unica', 'vencimento' => '2026-01-01', 'status' => 'vencido']);

        $this->assertSame(ServicoStatus::Activo, $this->status->avaliar($s));
    }

    public function test_stale_statuses_are_corrected_and_logged(): void
    {
        $atrasado = $this->servico(['vencimento' => '2026-10-01', 'status' => 'activo']);
        $quase = $this->servico(['vencimento' => '2026-10-16', 'status' => 'activo']);
        $suspenso = $this->servico(['vencimento' => '2026-01-01', 'status' => 'suspenso']);

        $this->assertSame(2, $this->status->sincronizarTodos());
        $this->assertSame(ServicoStatus::Vencido, $atrasado->fresh()->status);
        $this->assertSame(ServicoStatus::AVencer, $quase->fresh()->status);
        $this->assertSame(ServicoStatus::Suspenso, $suspenso->fresh()->status);
        $this->assertSame(2, Historico::count());
    }

    public function test_first_page_load_of_the_day_refreshes_statuses(): void
    {
        $s = $this->servico(['vencimento' => '2026-10-01', 'status' => 'activo']);
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertOk();

        $this->assertSame(ServicoStatus::Vencido, $s->fresh()->status);
    }

    public function test_a_missed_reminder_day_is_caught_up_once(): void
    {
        Mail::fake();
        // 6 days left: the 7-day reminder's day was missed.
        $s = $this->servico(['vencimento' => '2026-10-19']);
        $job = app(VerificacaoDiariaService::class);

        $job->executar();
        $job->executar();

        $this->assertSame(['d7'], LembreteEnviado::pluck('intervalo')->map->value->all());
        Mail::assertSentCount(1);

        Carbon::setTestNow('2026-10-17 10:00:00');
        $job->executar();
        $this->assertSame(['d7', 'd3'], LembreteEnviado::orderBy('id')->pluck('intervalo')->map->value->all());
    }

    public function test_a_client_without_email_still_alerts_the_empresa(): void
    {
        Mail::fake();
        $this->servico(['vencimento' => '2026-10-13'], email: '');

        app(VerificacaoDiariaService::class)->executar();

        Mail::assertSent(LembreteVencimentoMail::class, fn ($m) => $m->hasTo('empresa@teste.mz'));
        $this->assertSame('Lembrete enviado só à empresa', Historico::where('kind', 'lembrete')->value('title'));
    }

    public function test_reminders_off_still_updates_statuses(): void
    {
        Mail::fake();
        Configuracao::where('chave', 'lembretes')->update(['valor' => json_encode(['verificacao_diaria' => false, 'd7' => true])]);
        $s = $this->servico(['vencimento' => '2026-10-01', 'status' => 'activo']);

        app(VerificacaoDiariaService::class)->executar();

        $this->assertSame(ServicoStatus::Vencido, $s->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_monthly_revenue_counts_every_periodicity(): void
    {
        $this->servico(['valor' => 1200, 'periodicidade' => 'anual']);
        $this->servico(['valor' => 9000, 'periodicidade' => 'personalizada', 'duracao_dias' => 90]);
        $this->servico(['valor' => 500, 'periodicidade' => 'unica', 'vencimento' => null]);

        $this->assertEquals(100 + 3000, app(Totais::class)->calcular()['mrr']);
    }

    public function test_payments_cannot_be_wiped_by_deleting_their_servico(): void
    {
        $s = $this->servico();
        $this->status->renovar($s, ['metodo' => 'mpesa']);

        $this->expectException(QueryException::class);
        $s->delete();
    }
}
