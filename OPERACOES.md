# Operações

## Verificação diária de vencimentos

`php artisan vencia:verificar-vencimentos` recalcula o estado de todos os serviços não suspensos/cancelados e envia os lembretes de vencimento devidos no dia (nunca duplica um lembrete para o mesmo vencimento). Não há cron nativo no Windows, por isso corre via **Task Scheduler**, uma vez por dia às 07:00.

Criar a tarefa (correr uma vez, em PowerShell/cmd com permissões de administrador, ajustando o caminho do PHP se não for `C:\xampp\php\php.exe`):

```
schtasks /create /tn "Vencia - Verificar Vencimentos" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\Vencia\artisan vencia:verificar-vencimentos" /sc daily /st 07:00 /f
```

Testar imediatamente sem esperar pelo agendamento: `schtasks /run /tn "Vencia - Verificar Vencimentos"`. Resultado de cada corrida fica em `storage/logs/laravel.log` (nível `info`/`error`) e nos históricos dos serviços afectados.

O interruptor "verificação diária automática" (Configurações → Lembretes) desliga o envio sem desligar a tarefa: a tarefa continua a correr, mas o serviço regista no log que foi ignorado e não faz nada.

## Publicar num cPanel (hospedagem partilhada)

1. Confirmar com o fornecedor de hospedagem: PHP 8.3+ e acesso SSH/Terminal (para `composer install` e `php artisan`).
2. Apontar o domínio/subdomínio para a pasta `public/` do projecto (Document Root), nunca para a raiz.
3. Criar a base de dados MySQL no cPanel e preencher `DB_*` no `.env` de produção (não copiar o `.env` de desenvolvimento — gerar uma `APP_KEY` nova com `php artisan key:generate`, pôr `APP_ENV=production`, `APP_DEBUG=false`).
4. Preencher `MAIL_*` com as credenciais SMTP reais assim que existirem (hoje fica em `MAIL_MAILER=log`).
5. Correr: `composer install --no-dev --optimize-autoloader`, `php artisan migrate --seed --force` (ou sem `--seed` se não quiseres os dados de demonstração em produção), `php artisan storage:link`, `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
6. Em vez do Task Scheduler do Windows, usar os **Cron Jobs** do próprio cPanel para o mesmo comando: `php /home/utilizador/vencia/artisan vencia:verificar-vencimentos` (ajustar o caminho), agendado para as 07:00 diariamente — é mais simples do que no Windows.
