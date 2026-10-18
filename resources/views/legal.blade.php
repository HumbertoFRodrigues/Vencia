<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · Vencia</title>
    @include('partials.estilos')
    @include('partials.tema')
    @include('partials.site.anuncios')
</head>
<body class="site">
    @include('partials.site.topo')

    <main id="conteudo" class="seccao">
        <article class="site__largura site__largura--estreita legal">
            <span class="seccao__kicker">Vencia · HTech Group</span>
            <h1>{{ $titulo }}</h1>
            <p class="legal__data">Última actualização: {{ $actualizado }}</p>

            @if($pagina === 'termos')
                <p>Estes termos regulam o uso da Vencia, um serviço da HTech Group para gerir clientes, serviços recorrentes, pagamentos e finanças pessoais. Ao criar conta, aceitas estes termos.</p>

                <h2>1. A tua conta</h2>
                <p>Tens de indicar um email válido e manter a palavra-passe em segredo. És responsável pelo que for feito com a tua conta e pelas pessoas que convidas para a tua equipa. Recomendamos activar a verificação em dois passos.</p>

                <h2>2. O que a Vencia faz e não faz</h2>
                <p>A Vencia regista os teus clientes e serviços, envia lembretes por email e gera recibos. A Vencia não cobra os teus clientes nem movimenta dinheiro em teu nome: os pagamentos são feitos directamente entre ti e os teus clientes. Os recibos emitidos são documentos de controlo interno e não substituem facturas fiscais quando estas sejam exigidas por lei.</p>

                <h2>3. Planos e pagamentos</h2>
                <p>O plano Grátis tem limites indicados na página de planos. O plano Pro é pago por mês ou por ano, por M-Pesa, e-Mola ou transferência, e fica activo depois de confirmarmos o pagamento. Se não renovares, a conta volta ao plano Grátis: os dados ficam guardados, mas não podes acrescentar mais do que o Grátis permite. Os preços podem mudar; uma mudança nunca afecta um período já pago.</p>

                <h2>4. Uso aceitável</h2>
                <p>Não podes usar a Vencia para enviar mensagens não solicitadas (spam), para actividades ilegais, nem tentar aceder a dados de outras contas. Podemos suspender uma conta que viole estes termos, avisando o dono sempre que possível.</p>

                <h2>5. Os teus dados</h2>
                <p>Os dados que registas são teus. Tratamo-los como descrito na <a href="{{ route('privacidade') }}">Política de privacidade</a>. Podes descarregar uma cópia dos teus dados ou apagar a tua conta a qualquer momento, em O meu perfil.</p>

                <h2>6. Disponibilidade e responsabilidade</h2>
                <p>Fazemos cópias de segurança diárias e trabalhamos para manter o serviço sempre disponível, mas podem ocorrer interrupções. A Vencia é fornecida tal como está; não somos responsáveis por perdas indirectas resultantes de interrupções, de emails não entregues ou de dados mal introduzidos.</p>

                <h2>7. Alterações</h2>
                <p>Podemos actualizar estes termos. Se a mudança for importante, avisamos o dono de cada conta por email antes de entrar em vigor.</p>
            @else
                <p>Esta política explica que dados a Vencia (um serviço da HTech Group) guarda, para quê, e os teus direitos.</p>

                <h2>1. Que dados guardamos</h2>
                <ul>
                    <li><strong>Da tua conta:</strong> nome, email, palavra-passe (guardada cifrada) e, se activares, o segredo da verificação em dois passos.</li>
                    <li><strong>Da tua empresa:</strong> os clientes, serviços, pagamentos, recibos, configurações e logótipos que registas.</li>
                    <li><strong>Do teu painel pessoal:</strong> movimentos, categorias, saldos e metas. Só tu os vês; nem a tua equipa nem a HTech Group os usam para outro fim.</li>
                    <li><strong>Da faturação do plano:</strong> as referências e comprovativos de pagamento que envias.</li>
                    <li><strong>Técnicos:</strong> registos de erros e de sessão necessários para o serviço funcionar e ser seguro.</li>
                </ul>

                <h2>2. Para que os usamos</h2>
                <p>Só para prestar o serviço: mostrar os teus dados, enviar os lembretes e recibos aos teus clientes em nome da tua empresa, enviar-te emails sobre a conta e o plano, e dar suporte quando o pedes. Não vendemos nem partilhamos os teus dados para publicidade.</p>

                <h2>3. Os dados dos teus clientes</h2>
                <p>Quando registas um cliente, és tu o responsável por esses dados e por teres autorização para lhe enviar emails. A Vencia trata-os apenas por tua conta, para os lembretes e recibos que configuras.</p>

                <h2>4. Quem tem acesso</h2>
                <p>Cada empresa só vê os seus próprios dados. Os membros da tua equipa vêem os dados da empresa de acordo com o papel que lhes deste. A equipa de suporte da HTech Group só acede a uma conta para resolver um problema e esse acesso fica registado.</p>

                <h2>5. Quanto tempo guardamos</h2>
                <p>Enquanto a conta existir. Se pedires a eliminação, apagamos os dados da empresa e da conta; as cópias de segurança são substituídas ao fim de 14 dias.</p>

                <h2>6. Os teus direitos</h2>
                <p>Podes consultar e corrigir os teus dados na própria Vencia, e em <strong>O meu perfil</strong> descarregar uma cópia (ficheiros CSV) ou apagar a tua conta.@if($contacto) Para qualquer dúvida, escreve para <a href="mailto:{{ $contacto }}">{{ $contacto }}</a>.@endif</p>
            @endif
        </article>
    </main>

    @include('partials.site.rodape')
</body>
</html>
