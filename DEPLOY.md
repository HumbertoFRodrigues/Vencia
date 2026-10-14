# Publicar a Vencia num cPanel

Guia passo-a-passo para colocar a aplicação online num alojamento partilhado normal. Para a verificação diária de vencimentos e outras tarefas do dia-a-dia já em produção, ver `OPERACOES.md`.

## 1. Confirmar com o fornecedor de hospedagem

- [ ] **PHP 8.4 ou mais recente** (obrigatório — as dependências não instalam em 8.3) — normalmente activável no "MultiPHP Manager" do cPanel, com as extensões `mbstring`, `intl`, `gd`, `pdo_mysql`.
- [ ] **Base de dados MySQL** — o cPanel cria isto em "Bases de Dados MySQL".
- [ ] **Cron Jobs** — existe em praticamente todos os cPanel e é o que permite correr comandos `php artisan` mesmo **sem SSH** (ver passo 6).

## 2. Preparar os ficheiros (no teu computador)

```bash
composer install --no-dev --optimize-autoloader
```

Envia tudo **excepto** `.env`, `node_modules/`, `.git/` e os ficheiros `bootstrap/cache/*.php` (gera-os o próprio servidor; uma cópia feita no teu PC pode referir pacotes de desenvolvimento que não existem em produção e partir todos os pedidos). Por Git ("Git Version Control" do cPanel, a apontar para `https://github.com/HumbertoFRodrigues/Vencia.git`) ou num `.zip` enviado pelo File Manager e extraído lá.

Põe a aplicação numa pasta **fora** de `public_html` (ex.: `~/vencia_app`), para o código não ficar ao alcance da web.

## 3. Apontar o domínio para `public/`

No cPanel, ao criar o domínio ou subdomínio, o **Document Root** deve ser a pasta `public/` do projecto (ex.: `vencia_app/public`) — nunca a raiz. Se por engano apontar para a raiz, o `.htaccess` que está lá encaminha tudo para `public/`, por isso o `.env` e o código continuam inacessíveis — mas o correcto é `public/`.

## 4. Criar a base de dados

Em "Bases de Dados MySQL": criar a base, um utilizador, e associar o utilizador à base com todos os privilégios. Anota os três nomes (base, utilizador, password).

## 5. Configurar o `.env` de produção

Copiar `.env.example` para `.env` e preencher com os valores **do servidor** (nunca copiar o `.env` do teu computador):

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...            # gera no teu PC com: php artisan key:generate --show
APP_URL=https://oteudominio.com
APP_TIMEZONE=Africa/Maputo

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=nome_da_base_do_cpanel
DB_USERNAME=utilizador_do_cpanel
DB_PASSWORD=password_do_cpanel

SESSION_SECURE_COOKIE=true
LOG_LEVEL=error

ADMIN_EMAIL=o-teu-email-de-administrador
ADMIN_PASSWORD=uma-password-forte-nova
```

O SMTP não precisa de ir para o `.env`: preenche-se depois em **Configurações → Empresa** (fica guardado encriptado).

## 6. Correr os comandos — com ou sem SSH

**Com SSH/Terminal**, dentro da pasta do projecto:

```bash
php artisan migrate --force
php artisan db:seed --class=AdminSeeder --force   # conta de admin + configurações, sem dados de demonstração
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**Sem SSH**: em **Cron Jobs**, cria uma tarefa "a cada minuto" com o comando abaixo (ajusta o caminho — o cPanel mostra-o no File Manager), espera 2 minutos e **apaga a tarefa**:

```
cd /home/utilizador/vencia_app && php artisan migrate --force && php artisan db:seed --class=AdminSeeder --force && php artisan config:cache && php artisan route:cache
```

A saída fica no email do cPanel. O mesmo truque serve para cada actualização futura que traga migrações novas (só o `migrate --force` e o `config:cache`).

Não é preciso `storage:link`: os logótipos carregados são gravados directamente em `public/storage`.

## 7. Agendar a verificação diária

Em **Cron Jobs**, uma vez por dia às 07:00:

```
0 7 * * * cd /home/utilizador/vencia_app && php artisan vencia:verificar-vencimentos
```

E uma segunda tarefa, às 03:00, para a **cópia de segurança diária** (guarda as últimas 14 em `storage/app/copias` e envia a nova por email para o email da empresa):

```
0 3 * * * cd /home/utilizador/vencia_app && php artisan vencia:copia-seguranca
```

Também podes criar e descarregar uma cópia a qualquer momento em Configurações → Empresa → Cópias de segurança. Para restaurar: phpMyAdmin → base de dados vazia → Importar o ficheiro `.sql.gz`.

Em produção, qualquer erro inesperado é enviado por email para o email da empresa (no máximo uma vez por hora para o mesmo erro). Desliga-se com `ALERTAS_ERRO=false` no `.env`.

A primeira tarefa, `vencia:verificar-vencimentos`, é a que envia os lembretes por email. Os **estados** dos serviços (activo / a vencer / vencido) acertam-se também sozinhos no primeiro acesso de cada dia, por isso os totais nunca ficam errados mesmo num dia em que a tarefa falhe — e um lembrete que falhe num dia é recuperado no seguinte.

## 8. Depois de publicar

- [ ] Entra com o `ADMIN_EMAIL`/`ADMIN_PASSWORD` do `.env` de produção.
- [ ] Em Configurações → Empresa, preenche nome, email, logótipo e o SMTP; usa **Enviar email de teste**.
- [ ] Corre a verificação uma vez à mão (Terminal, ou o truque do Cron do passo 6) para confirmar que funciona.

> ⚠️ Nunca publiques o `.env` de desenvolvimento por cima do de produção — tem a password da tua base de dados local e a chave da app antiga. Mudar a `APP_KEY` depois de configurado o SMTP obriga a reintroduzir a password SMTP.

## Se algo correr mal

| Sintoma | Onde olhar primeiro |
|---|---|
| Página "Algo correu mal do nosso lado" | `storage/logs/laravel.log`. Enquanto investigas podes pôr `APP_DEBUG=true` por momentos — volta a `false` logo a seguir. |
| "500 Internal Server Error" logo à entrada | Permissões de `storage/` e `bootstrap/cache/` (graváveis pelo servidor) ou ficheiros antigos em `bootstrap/cache/*.php` — apaga-os. |
| Site mostra a lista de ficheiros ou o código | O Document Root não está a apontar para `public/`. |
| Emails não chegam | Configurações → Empresa → SMTP e o botão de email de teste. |
| Logótipo não aparece | Confirmar que `public/storage/` existe e é gravável. |
