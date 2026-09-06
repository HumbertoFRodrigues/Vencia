# Publicar a Vencia num cPanel

Guia passo-a-passo para colocar a aplicação online num alojamento partilhado normal. Para a verificação diária de vencimentos e outras tarefas do dia-a-dia já em produção, ver `OPERACOES.md`.

## 1. Confirmar com o fornecedor de hospedagem

- [ ] **PHP 8.3 ou mais recente** — normalmente activável no "MultiPHP Manager" do cPanel.
- [ ] **Acesso SSH ou "Terminal"** no cPanel — precisas de correr `composer` e `php artisan`. Sem isto ainda é possível (upload manual da pasta `vendor/` já instalada + importar `database/vencia.sql` via phpMyAdmin), mas dá mais trabalho.
- [ ] **Base de dados MySQL** — o cPanel cria isto em "Bases de Dados MySQL".

## 2. Enviar os ficheiros

Por Git (se o cPanel tiver "Git Version Control", o mais simples: aponta directamente para `https://github.com/HumbertoFRodrigues/Vencia.git`) ou por upload/FTP de tudo **excepto** `vendor/` e `.env` — esses ficam de fora do repositório de propósito e têm de ser gerados/enviados à parte.

## 3. Apontar o domínio para `public/`

No cPanel, ao criar o domínio ou subdomínio, o campo **"Document Root"** deve apontar para a pasta `public/` do projecto — nunca para a raiz. Este é o erro mais comum; se apontares para a raiz, o site mostra uma listagem de ficheiros em vez da aplicação.

## 4. Criar a base de dados

Em "Bases de Dados MySQL": criar a base, um utilizador, e associar o utilizador à base com todos os privilégios. Anota os três nomes (base, utilizador, password) — precisas deles no passo seguinte.

## 5. Configurar o `.env` de produção

Copiar `.env.example` para `.env` e preencher a sério. **Não copiar o `.env` do teu computador** — os valores têm de ser os do servidor:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://oteudominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=nome_da_base_do_cpanel
DB_USERNAME=utilizador_do_cpanel
DB_PASSWORD=password_do_cpanel

MAIL_MAILER=smtp
MAIL_HOST=smtp.oteudominio.com
MAIL_PORT=587
MAIL_USERNAME=cobrancas@oteudominio.com
MAIL_PASSWORD=a_password_do_email

ADMIN_EMAIL=o-teu-email-de-administrador
ADMIN_PASSWORD=uma-password-forte-nova
```

## 6. Correr os comandos (via Terminal/SSH, dentro da pasta do projecto)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force        # sem --seed em produção, a não ser que queiras os dados de demonstração
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Sem acesso SSH?** Em vez do `migrate`, gera um dump local (`mysqldump -u root vencia --no-tablespaces --skip-comments > vencia.sql`, depois de correres `php artisan migrate:fresh --seed` no teu computador) e importa esse ficheiro directamente via phpMyAdmin no servidor — fica com a estrutura e os dados de demonstração de uma vez. Os comandos `cache` do passo 6 ficam por fazer; a aplicação funciona igual, só um pouco mais lenta.

## 7. Agendar a verificação diária

Em **"Cron Jobs"** no cPanel:

```
0 7 * * * php /home/utilizador/oteudominio.com/artisan vencia:verificar-vencimentos
```

(ajusta o caminho para onde o cPanel realmente colocou os ficheiros — confirma com `pwd` no Terminal).

## 8. Depois de publicar

- [ ] Entra com o `ADMIN_EMAIL`/`ADMIN_PASSWORD` que definiste no `.env` de produção.
- [ ] Em Configurações → Empresa, confirma que o SMTP está preenchido e usa o botão **Enviar email de teste**.
- [ ] Corre a tarefa cron uma vez à mão para confirmar que funciona, antes de confiares no agendamento: `php artisan vencia:verificar-vencimentos`.

> ⚠️ Nunca publiques o `.env` de desenvolvimento por cima do de produção — tem a password da tua base de dados local e a chave da app antigas.

## Se algo correr mal

| Sintoma | Onde olhar primeiro |
|---|---|
| Página em branco ou erro genérico | `storage/logs/laravel.log`; confirma `APP_DEBUG=false` só depois de resolver — enquanto testas, muda temporariamente para `true` para ver o erro real. |
| "500 Internal Server Error" logo à entrada | Permissões das pastas `storage/` e `bootstrap/cache/` — têm de ser graváveis pelo servidor. |
| Site mostra a lista de ficheiros em vez da app | O Document Root do domínio não está a apontar para `public/`. |
| Emails não chegam | Confirmar `MAIL_MAILER=smtp` (não `log`) e testar com o botão de email de teste em Configurações. |
