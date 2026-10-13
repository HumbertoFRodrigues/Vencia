# Operações

## Verificação diária de vencimentos

`php artisan vencia:verificar-vencimentos` recalcula o estado de todos os serviços não suspensos/cancelados e envia os lembretes de vencimento devidos (nunca duplica um lembrete para o mesmo vencimento; um dia em que não correu é recuperado no seguinte). Os estados também se acertam sozinhos no primeiro acesso de cada dia, por isso a tarefa serve sobretudo para os emails. Não há cron nativo no Windows, por isso corre via **Task Scheduler**, uma vez por dia às 07:00.

Criar a tarefa (correr uma vez, em PowerShell/cmd com permissões de administrador, ajustando o caminho do PHP se não for `C:\xampp\php\php.exe`):

```
schtasks /create /tn "Vencia - Verificar Vencimentos" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\Vencia\artisan vencia:verificar-vencimentos" /sc daily /st 07:00 /f
```

Testar imediatamente sem esperar pelo agendamento: `schtasks /run /tn "Vencia - Verificar Vencimentos"`. Resultado de cada corrida fica em `storage/logs/laravel.log` (nível `info`/`error`) e nos históricos dos serviços afectados.

O interruptor "verificação diária automática" (Configurações → Lembretes) desliga só os emails: a tarefa continua a actualizar os estados dos serviços e regista no log que os lembretes estão desligados.

## Testes

`php artisan test` corre a bateria completa numa base SQLite em memória (não toca nos dados locais). O GitHub corre-a, com a verificação de estilo `vendor/bin/pint --test`, em cada push para `main`.

## Publicar num cPanel

Ver [`DEPLOY.md`](DEPLOY.md) — guia passo-a-passo completo (requisitos, ficheiros, base de dados, `.env` de produção, comandos, cron job, o que verificar se algo correr mal).
