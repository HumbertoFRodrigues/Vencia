# Publicar a Vencia num cPanel

Guia passo-a-passo para colocar a aplicação online num alojamento partilhado normal. Para a verificação diária de vencimentos e outras tarefas do dia-a-dia já em produção, ver `OPERACOES.md`.

## 1. Confirmar com o fornecedor de hospedagem

- [ ] **PHP 8.4 ou mais recente** (obrigatório — as dependências não instalam em 8.3) — normalmente activável no "MultiPHP Manager" do cPanel, com as extensões `mbstring`, `intl`, `gd`, `pdo_mysql`, `zlib` (cópias de segurança), `fileinfo` (logótipos) e `openssl`.
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

**Numa subpasta** (ex.: `oteudominio.com/vencia`, ou `localhost/vencia` no XAMPP): também funciona — o `.htaccess` da raiz encaminha para `public/` e a app descobre sozinha a pasta. Põe no `.env` o endereço completo, com a pasta: `APP_URL=https://oteudominio.com/vencia`.

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

Esta tarefa percorre todas as contas, cada uma com os seus lembretes, modelos e servidor de email.

Uma segunda tarefa, às 06:30, para os **planos** (avisa os donos 7 dias e 1 dia antes do fim do plano pago e passa para o Grátis as contas cujo plano terminou):

```
30 6 * * * cd /home/utilizador/vencia_app && php artisan vencia:planos
```

E uma terceira, às 03:00, para a **cópia de segurança diária** de toda a base de dados (guarda as últimas 14 em `storage/app/copias` e envia a nova por email ao super-admin):

```
0 3 * * * cd /home/utilizador/vencia_app && php artisan vencia:copia-seguranca
```

Também podes criar e descarregar uma cópia a qualquer momento em Admin → Plataforma → Cópias de segurança (só o super-admin vê esta opção, porque a cópia tem os dados de todas as contas). Para restaurar: phpMyAdmin → base de dados vazia → Importar o ficheiro `.sql.gz`.

Em produção, qualquer erro inesperado é enviado por email ao super-admin (no máximo uma vez por hora para o mesmo erro). Desliga-se com `ALERTAS_ERRO=false` no `.env`.

A primeira tarefa, `vencia:verificar-vencimentos`, é a que envia os lembretes por email. Os **estados** dos serviços (activo / a vencer / vencido) acertam-se também sozinhos no primeiro acesso de cada dia, por isso os totais nunca ficam errados mesmo num dia em que a tarefa falhe — e um lembrete que falhe num dia é recuperado no seguinte.

## 8. Depois de publicar

- [ ] Entra com o `ADMIN_EMAIL`/`ADMIN_PASSWORD` do `.env` de produção. Esta conta é o **super-admin** da plataforma (vê o separador **Admin**) e tem a sua própria empresa no plano Pro, sem data de fim.
- [ ] Em **Admin → Plataforma**, preenche o **SMTP da plataforma** (é por ele que saem a confirmação de conta, as passwords, os convites e os emails das empresas sem servidor próprio) e os **dados de pagamento** (M-Pesa, e-Mola, banco) que os clientes vêem ao pedir o Pro. Usa **Testar emails da plataforma**.
- [ ] Em **Admin → Planos**, confirma os preços e os limites do Grátis e do Pro.
- [ ] Em Configurações → Empresa, preenche nome, email e logótipo da tua empresa.
- [ ] Corre a verificação uma vez à mão (Terminal, ou o truque do Cron do passo 6) para confirmar que funciona.

> ⚠️ Nunca publiques o `.env` de desenvolvimento por cima do de produção — tem a password da tua base de dados local e a chave da app antiga. Mudar a `APP_KEY` depois de configurado o SMTP obriga a reintroduzir a password SMTP.

## Actualizar uma instalação antiga para a versão com contas

A migração `2026_10_15_000002` converte sozinha uma instalação que já tem dados:

- tudo o que existe (clientes, serviços, pagamentos, configurações) passa a ser **uma conta**, com o nome da empresa;
- o utilizador do `ADMIN_EMAIL` (ou o primeiro) fica **dono** dessa conta e **super-admin**; outros utilizadores ficam como gestores;
- a conta fica no plano **Pro sem data de fim**, e os números dos recibos antigos não mudam;
- o SMTP que estava configurado passa a ser também o SMTP da plataforma.

Antes de actualizar, faz uma cópia de segurança. Depois do `migrate --force`, acrescenta a tarefa `vencia:planos` ao Cron (passo 7).

## Ligar o pagamento automático por M-Pesa (quando tiveres as credenciais)

Está tudo pronto no código. Sem credenciais, os clientes pagam o Pro à mão (M-Pesa, e-Mola ou transferência) e tu confirmas em Admin → Faturação. Com credenciais, a página Plano mostra também **Pagar já com M-Pesa**: o cliente escreve o número, confirma com o PIN no telemóvel e o plano activa sozinho, com recibo por email.

1. No portal da Vodacom (developer.mpesa.vm.co.mz), copia a **API Key**, a **Public Key** e o **Service Provider Code**. São as mesmas chaves que o payment-api usa.
2. Na Vencia: **Admin → Plataforma → Pagamento automático por M-Pesa**. Cola as três, escolhe o ambiente **Sandbox (testes)**, marca **Ligar** e carrega em **Guardar**. As chaves ficam cifradas na base de dados.
3. Carrega em **Verificar chaves**: confirma que a API Key e a Public Key combinam, sem cobrar nada.
4. Faz um pagamento de teste de um plano, com o número de teste do portal da Vodacom.
5. Quando a Vodacom aprovar a conta para produção, muda o ambiente para **Produção (dinheiro real)** e guarda.

Alternativa para quem prefere o servidor: as mesmas chaves no `.env` (`PAGAMENTOS_GATEWAY=mpesa`, `MPESA_API_KEY`, `MPESA_PUBLIC_KEY`, `MPESA_SERVICE_PROVIDER_CODE`, `MPESA_SANDBOX`). O que estiver guardado no painel tem prioridade.

O pedido espera até 2,5 minutos pela confirmação do PIN. Se o alojamento cortar pedidos mais cedo (alguns cPanel limitam a 30–60 s), pede ao suporte para aumentar o `max_execution_time` do PHP para 180. Para desligar, desmarca **Ligar** no painel.

## 9. Segurança e telemóvel

- **Verificação em dois passos**: em Configurações → Segurança, lê o código QR com o Google Authenticator / Microsoft Authenticator e guarda os códigos de recuperação num sítio seguro.
- **App no telemóvel**: com o site em **HTTPS**, abre-o no Chrome (Android) ou Safari (iPhone) e escolhe "Adicionar ao ecrã principal". Sem HTTPS esta opção não aparece.

## 10. Publicação automática (opcional)

Depois da primeira instalação manual (passos 1–8), cada `git push` para `main` pode actualizar o site sozinho: o GitHub corre os testes e, se passarem, envia os ficheiros por FTP e corre as migrações. Enquanto não configurares nada, o workflow "Publicar" fica desligado (aparece como concluído, sem fazer nada).

1. No `.env` do **servidor**, acrescenta `DEPLOY_TOKEN=` com um texto aleatório de 32+ caracteres (ex.: gera com `php -r "echo bin2hex(random_bytes(24));"`). Corre o truque do Cron do passo 6 uma vez (`config:cache`) para o servidor o ler.
2. No cPanel → **Contas FTP**, cria uma conta cuja pasta seja a pasta-mãe da aplicação (ou usa a conta principal).
3. No GitHub → repositório → **Settings → Secrets and variables → Actions → New repository secret**, cria:

| Secret | Exemplo |
|---|---|
| `FTP_SERVER` | `ftp.oteudominio.com` |
| `FTP_USERNAME` | `deploy@oteudominio.com` |
| `FTP_PASSWORD` | a password dessa conta FTP |
| `FTP_DIR` | `vencia_app/` (caminho da app visto por essa conta, **terminado em `/`**) |
| `APP_URL` | `https://oteudominio.com` |
| `DEPLOY_TOKEN` | o mesmo valor do `.env` do servidor |
| `FTP_PROTOCOL` | opcional: `ftp` se o servidor não aceitar FTPS (por omissão `ftps`) |

Nunca são enviados: `.env`, `storage/`, `public/storage/` (logótipos), `tests/` nem `.git`. A primeira publicação demora (envia tudo); as seguintes só enviam o que mudou. Para publicar sem novo commit: separador **Actions → Publicar → Run workflow**.

## Se algo correr mal

| Sintoma | Onde olhar primeiro |
|---|---|
| Página "Algo correu mal do nosso lado" | `storage/logs/laravel.log`. Enquanto investigas podes pôr `APP_DEBUG=true` por momentos — volta a `false` logo a seguir. |
| "500 Internal Server Error" logo à entrada | Permissões de `storage/` e `bootstrap/cache/` (graváveis pelo servidor) ou ficheiros antigos em `bootstrap/cache/*.php` — apaga-os. |
| Site mostra a lista de ficheiros ou o código | O Document Root não está a apontar para `public/`. |
| Emails não chegam | Configurações → Empresa → SMTP e o botão de email de teste. |
| Logótipo não aparece | Confirmar que `public/storage/` existe e é gravável. |
