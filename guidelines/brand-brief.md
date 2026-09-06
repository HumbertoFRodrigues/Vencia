# Vencia — brief para criação do logo

## O que é o produto
Um sistema web de **gestão de assinaturas e serviços recorrentes**, para uso individual de um administrador (não é ERP, não é contabilidade).
Estrutura: **cliente → vários serviços → pagamentos → vencimentos → lembretes → histórico**.

Serviços geridos: domínios, hospedagem, email corporativo, manutenção de sites, acessos a ferramentas de IA (ChatGPT, Claude), software revendido.
Mercado: Moçambique. Moeda MZN. Interface em português (pt-PT). Pagamentos por M-Pesa, e-Mola, transferência bancária e dinheiro.

## Para quem
Um profissional/pequena agência de informática que revende serviços e precisa saber, em segundos:
quem pagou, quanto entrou este mês, quem está em atraso, o que vence nos próximos dias, que acesso precisa de suspender.

## Promessa numa frase
"Cadastro o cliente uma vez e o sistema lembra-me quando cobrar, renovar ou remover o acesso."

## Personalidade
Operacional, calmo, preciso, discreto. Software de trabalho, não app de consumo.
**Não é:** divertido, colorido, futurista, "fintech neon", nem corporativo-pesado.
Palavras-chave: prazo, controlo, clareza, recorrência, confiança.

## Sistema visual já definido (o logo deve encaixar aqui)
- **Cor principal:** Cobalto `#3348d8`. Tinta escura `#14171d`. Fundo claro `#f8f9fb`.
- **Cores de estado (reservadas, não usar no logo):** verde `#12805c` activo, âmbar `#a76308` a vencer, vermelho `#c4342b` vencido.
- **Tipografia:** Geist (interface) + Geist Mono (todos os números). Espaçamento negativo apertado, peso semibold.
- **Formas:** cantos de 6–12 px, linhas de 1 px, sombras quase inexistentes, superfícies planas.
- **Ícones:** Lucide, contorno de ~1,75 px, sem preenchimento.
- **Modo escuro obrigatório:** fundo `#0d1014`, cartões `#15191f`.

## O que preciso do logo
1. **Marca completa (horizontal):** símbolo + "Vencia".
2. **Símbolo isolado (quadrado):** funciona a 26 px na barra lateral e a 28 px como favicon — o desenho tem de aguentar 26 px sem detalhe perdido.
3. **Versões:** positiva (sobre branco), negativa (sobre `#14171d` e sobre `#0d1014`) e monocromática.
4. **Formato:** SVG (vector, traço convertido em forma), mais PNG 512×512 com fundo transparente.
5. **Substitui:** o quadrado provisório "SM" que está hoje na barra lateral e no thumbnail.

## Direcções de conceito que combinam com o sistema
- Ciclo/renovação: seta circular, mas geométrica e fechada — sem parecer "reciclagem".
- Prazo: marca de calendário reduzida ao mínimo (um traço superior + um ponto).
- Recorrência: duas formas repetidas com deslocamento constante (ritmo mensal).
- Monograma "SM" geométrico com um corte que sugere movimento.
Evitar: cifrões, moedas, gráficos de barras a subir, engrenagens, escudos, foguetões, gradientes arco-íris.

## Onde colocar depois
- `assets/logo.svg` (e `assets/logo-mono.svg`, `assets/logo.png`).
- Actualizar: `components/navigation/SidebarNav.jsx` (monograma), `guidelines/brand-wordmark.html`, `thumbnail.html`.
- Logo da empresa nos emails: campo já existe em Configurações (`LogoSlot`).

## Ainda em falta (além do logo)
- Ficheiros de logo dos serviços conhecidos, em `assets/logos/`: `chatgpt.svg`, `claude.svg`, `canva.svg`, `google-workspace.svg`, `microsoft-365.svg`, `github.svg`, `adobe.svg`, `dropbox.svg`, `zoom.svg`, `notion.svg`, `figma.svg`, `wordpress.svg`, `cpanel.svg`. Aparecem automaticamente em todo o sistema.
- Logo de banco para a transferência bancária (ou um por banco: BCI, Millennium BIM, Standard Bank, Absa).
- Ficheiros de fonte licenciados, se não quiser usar Geist do Google Fonts.
