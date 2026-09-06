# Vencia

Aplicação de gestão de clientes, serviços recorrentes, pagamentos e vencimentos para a **Comusanas**. Moeda MZN, interface em português europeu.

O ciclo que a aplicação serve: **cliente → serviços → pagamentos → vencimentos → lembretes → histórico**. O admin (uma pessoa só, sem registo público) precisa de responder em segundos: quem pagou, o que vence a seguir, quem está atrasado, o que suspender.

## Stack

- **Laravel 13** + **Livewire 4** (componentes PHP, sem separar API/frontend)
- **MySQL**
- **Blade** para as views, com um kit de componentes de UI próprio em `resources/views/components/ui/` (botões, cartões, tabelas, badges de estado...) que reproduz o sistema de design original — ver `docs/design-original/`
- **Sem build de JavaScript** (sem Vite, sem npm) — o CSS é ficheiro estático simples, servido a partir de `public/css/`. Só há JavaScript puro onde é mesmo preciso (alternar tema claro/escuro, abrir o menu de perfil), sem nenhuma framework de frontend.

## Estrutura

```
app/
  Enums/            Estados e categorias como enums nativos do PHP (ServicoStatus, MetodoPagamento...)
  Models/            Cliente, Servico, Pagamento, Historico, Configuracao...
  Services/          ServicoStatusService (máquina de estados + renovação),
                      Totais (todos os números derivados do dashboard/finanças),
                      VerificacaoDiariaService (o motor de lembretes)
  Livewire/          Um componente por ecrã/diálogo, agrupado por área
                      (Clientes/, Servicos/, Pagamentos/, Financas/, Vencimentos/,
                      Historico/, Configuracoes/, Shared/)
  Mail/              Os dois templates de email (lembrete de vencimento, serviço terminado)
  Console/Commands/  vencia:verificar-vencimentos — o comando do job diário

resources/
  views/livewire/    Uma view Blade por componente Livewire acima
  views/components/ui/  O kit de UI (botões, tabelas, badges...) partilhado por todos os ecrãs
  css/               tokens/ (cores, tipografia, espaçamento — copiados do design original,
                      byte a byte) + components.css (estilos do kit de UI)

public/css/          Cópia estática de resources/css/ — é o que o browser carrega.
                      Atenção: esta cópia é MANUAL. Se editares resources/css/components.css,
                      copia também para public/css/components.css, senão a alteração não aparece.

docs/design-original/  O sistema de design e o protótipo React que serviram de base para
                        construir esta aplicação. Já não é preciso para a app funcionar —
                        fica só como referência da ideia visual original.

lang/pt_PT/           Traduções das mensagens de erro de formulário e de autenticação.

database/
  migrations/         Esquema da base de dados
  seeders/            Dados de demonstração (5 clientes, 11 serviços, pagamentos...) —
                      as datas são calculadas relativamente a "agora", nunca ficam desactualizadas.
```

## Como correr localmente

Pré-requisitos: PHP 8.3+, Composer, MySQL.

```bash
composer install
cp .env.example .env        # depois preencher DB_*, ADMIN_EMAIL, ADMIN_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Abrir `http://127.0.0.1:8000/login` e entrar com o `ADMIN_EMAIL`/`ADMIN_PASSWORD` definidos no `.env`. Não há registo — é uma aplicação de administrador único.

`php artisan migrate:fresh --seed --force` repõe os dados de demonstração a qualquer momento (é o conjunto de dados fictício do protótipo original, portado para o seeder).

## As regras de negócio que importa perceber antes de mexer no código

- **`Servico::dias`** (em `app/Models/Servico.php`) é sempre calculado, nunca guardado — dias até/desde o vencimento.
- **A máquina de estados** (`activo → a_vencer → vencido → suspenso | cancelado`) vive inteira em `App\Services\ServicoStatusService`. `avaliar()` decide o estado; `renovar()` e `suspender()` são as duas únicas formas de o mudar manualmente (nunca editar `status` directamente num formulário).
- **Renovar soma o período ao vencimento actual, não a hoje** — mensal 01/09 → 01/10 → 01/11, mesmo que hoje já seja dia 20. Isto está isolado em `ServicoStatusService::calcularProximoVencimento()`, reutilizado tanto pela pré-visualização do diálogo de Renovar como pela renovação real, para nunca desalinhar.
- **MRR/ARR e todos os totais do Dashboard/Finanças** vêm de `App\Services\Totais::calcular()` — um único sítio, nunca duplicar a query nem calcular à mão noutro ecrã.
- **`Historico` nunca é editado nem apagado** — o próprio modelo bloqueia isso (`boot()` lança excepção). É o registo de auditoria da aplicação.
- **Lembretes têm dois níveis**: um valor global por omissão (Configurações → Lembretes) e uma excepção por serviço (`ServicoLembreteConfig`, os checkboxes no ecrã do serviço). `ServicoStatusService::intervaloActivo()` é o único sítio que resolve qual dos dois vale para um serviço concreto.
- **O motor de lembretes nunca envia o mesmo aviso duas vezes** para o mesmo vencimento — controlado pela tabela `lembretes_enviados` (chave única `servico_id`+`intervalo`+`vencimento_referencia`).
- **Todo o email enviado a um cliente vai também, em cópia oculta, para o email da empresa** configurado em Configurações → Empresa (`Configuracao::emailBccAdmin()`) — para o admin ficar sempre a par, mesmo sem abrir a aplicação.

## Email

Hoje `MAIL_MAILER=log` — os emails ficam registados em `storage/logs/laravel.log`, não saem de verdade. Para activar o envio real, preencher `MAIL_HOST`/`MAIL_USERNAME`/`MAIL_PASSWORD`/`MAIL_PORT` no `.env` com as credenciais SMTP verdadeiras; não é preciso mudar nenhum código.

## Verificação diária e publicação online

Ver `OPERACOES.md` — tem o comando exacto para agendar o job diário (Task Scheduler no Windows, Cron Jobs num cPanel) e os passos para publicar num alojamento partilhado normal.

## O que ainda não existe

- Testes automatizados.
- Reatribuir um serviço a outro cliente, ou apagar entradas da biblioteca de serviços (só arquivar).
- Multi-utilizador / permissões — é deliberadamente uma aplicação de administrador único.
