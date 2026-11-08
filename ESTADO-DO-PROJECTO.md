# Estado do projecto Vencia

Última actualização: 9 de Novembro de 2026. O projecto fica em pausa, a funcionar.

## O que é
SaaS para pequenas empresas: cada conta tem a **Empresa** (clientes, serviços com renovação, lembretes automáticos por email, pagamentos com recibo PDF, finanças, equipa) e cada pessoa tem o seu painel **Pessoal** privado. O super-admin tem a área **Administração** (contas, utilizadores, planos Grátis/Pro, faturação manual, envio de email, anúncios, cópias de segurança).

Stack: Laravel 13, Livewire 4, MySQL/MariaDB, PHP 8.4+. Sem Node no servidor (CSS em `public/css`, copiado de `resources/css`).

## Onde está
| | |
|---|---|
| Produção | https://vencia.mundotutors.com |
| Pasta no servidor | `/home/portald1/public_html/Outros/vencia.mundotutors.com` |
| Repositório | https://github.com/HumbertoFRodrigues/Vencia |
| Local (XAMPP) | http://localhost/vencia, pasta `C:\xampp\htdocs\Vencia` |
| Pacotes de publicação | `C:\xampp\htdocs\vencia-deploy\` |

## Como publicar uma actualização (sem terminal)
1. Gerar um ZIP só com os ficheiros alterados, com a data dos ficheiros = agora (senão o Laravel continua a mostrar páginas da cache):
   `git diff --name-only <commit-publicado> HEAD`, e no ZIP usar `ZipArchive::setMtimeName($f, time())`.
2. cPanel → File Manager → pasta da app → Upload → Extract (substituir) → apagar o ZIP.
3. Se houver migrações novas: importar o SQL equivalente no phpMyAdmin (não há terminal).
4. Pôr no ZIP um ficheiro `VERSAO` com o commit (ex.: `26f8841`): as páginas compiladas ficam em `storage/framework/views/<VERSAO>`, por isso cada versão começa com a cache limpa. Sem ele, apagar o conteúdo de `storage/framework/views/`.
5. Depois de mudar pacotes (`vendor/`): apagar `bootstrap/cache/packages.php` e `services.php`.

Detalhe completo da instalação: `DEPLOY.md`.

## Configuração em produção
- **Envio de email:** Administração → Plataforma → *Servidor do alojamento* (função `mail()`), de `no_replay@mundotutors.com`. O alojamento bloqueia SMTP para fora (Gmail dá "Connection refused"). A opção Brevo (API por HTTPS) está pronta se um dia o spam voltar a ser problema.
- **DNS de `mundotutors.com`** (email no Google Workspace): SPF `v=spf1 ip4:162.55.65.66 include:_spf.google.com ~all`, DKIM do cPanel (`default._domainkey`), DMARC `p=none` com relatórios, MX só `SMTP.GOOGLE.com`. cPanel → Email Routing = *Remote Mail Exchanger*.
- **Extensões PHP** `fileinfo` e `mbstring`: se faltarem, a Vencia usa substitutos (`bootstrap/extensoes.php`).
- **Tarefas diárias** (lembretes 07:00, planos 06:30, cópia 03:00): se não houver Cron Job, correm depois da primeira visita ao site a seguir à hora (`CorrerTarefasEmFalta`). Com cron correm à hora certa:
  ```
  0 3 * * *   cd /home/portald1/public_html/Outros/vencia.mundotutors.com && /usr/local/bin/php artisan vencia:copia-seguranca
  30 6 * * *  cd /home/portald1/public_html/Outros/vencia.mundotutors.com && /usr/local/bin/php artisan vencia:planos
  0 7 * * *   cd /home/portald1/public_html/Outros/vencia.mundotutors.com && /usr/local/bin/php artisan vencia:verificar-vencimentos
  ```
- **Cópias de segurança:** o servidor guarda 14; o email diário só avisa (anexar é opcional, em Plataforma).
- **AdSense:** ID `ca-pub-9666351884551817` em Plataforma → Anúncios; `/ads.txt` é gerado.
- **Sessão:** termina após 15 min sem actividade, com aviso 2 min antes (`SESSAO_INACTIVIDADE` no `.env`; 0 desliga).
- **Estado de tudo isto:** Administração → Visão geral → *Estado do sistema*.

## Por fazer quando voltar
- Preencher **Dados de pagamento dos planos** (M-Pesa/e-Mola/banco) para vender o Pro.
- **M-Pesa automático:** colar as chaves da Vodacom em Plataforma (código pronto e testado contra uma API simulada; falta testar com a real).
- Revisão jurídica de `/termos` e `/privacidade`.
- Criar os 3 Cron Jobs acima (opcional, por causa da alternativa automática).
- Ideias não feitas: link copiável nos convites de equipa; editor de imagens do site em Administração (hoje trocam-se ficheiros em `public/assets/inicio/`); pagamento automático e-Mola.

## Testes
`php artisan test` (170 testes, inclui isolamento entre contas e segurança). `vendor/bin/pint` para o estilo. O CI no GitHub corre os dois em PHP 8.4 e 8.5.
