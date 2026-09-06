window.SM_DATA = (() => {
  const clientes = [
    { id: "c1", nome: "João da Silva", email: "joao@email.com", tel: "+258 84 512 0034", empresa: "Silva Consultoria", status: "activo", desde: "12/03/2025" },
    { id: "c2", nome: "Maria Costa", email: "maria.costa@email.com", tel: "+258 82 771 9920", empresa: "", status: "activo", desde: "04/06/2025" },
    { id: "c3", nome: "Carlos Manuel", email: "carlos@cmdigital.co.mz", tel: "+258 87 330 1188", empresa: "CM Digital", status: "activo", desde: "22/01/2026" },
    { id: "c4", nome: "Ana Mucavele", email: "ana@mucavele.co.mz", tel: "+258 84 220 7745", empresa: "Mucavele & Filhos", status: "activo", desde: "09/09/2025" },
    { id: "c5", nome: "Hélder Tembe", email: "helder.tembe@email.com", tel: "+258 86 909 4412", empresa: "", status: "inactivo", desde: "17/11/2025" },
  ];
  const servicos = [
    { id: "s1", clienteId: "c1", descricao: "Alojamento do site institucional, 10 GB SSD, backups semanais.", nome: "Hospedagem", plano: "Plano Business", categoria: "hospedagem", valor: 1500, periodo: "ano", periodicidade: "Anual", inicio: "02/09/2025", vencimento: "02/09/2026", status: "a_vencer", dias: 3 },
    { id: "s2", clienteId: "c1", descricao: "Registo e renovação do domínio principal, DNS gerido por mim.", nome: "exemplo.co.mz", plano: "Domínio .co.mz", categoria: "dominio", valor: 800, periodo: "ano", periodicidade: "Anual", inicio: "02/09/2025", vencimento: "02/09/2027", status: "activo", dias: 368 },
    { id: "s3", clienteId: "c1", descricao: "Acesso partilhado à conta de equipe — 2 utilizadores.", nome: "ChatGPT", plano: "Acesso Equipe", categoria: "ia", valor: 500, periodo: "mês", periodicidade: "Mensal", inicio: "01/09/2026", vencimento: "01/10/2026", status: "activo", dias: 32 },
    { id: "s4", clienteId: "c2", descricao: "Acesso individual para redacção de conteúdos.", nome: "ChatGPT", plano: "Acesso Equipe", categoria: "ia", valor: 500, periodo: "mês", periodicidade: "Mensal", inicio: "05/08/2026", vencimento: "05/09/2026", status: "a_vencer", dias: 6 },
    { id: "s5", clienteId: "c2", descricao: "Licença Pro para materiais de marketing da loja.", nome: "Canva", plano: "Pro", categoria: "software", valor: 1000, periodo: "ano", periodicidade: "Anual", inicio: "20/10/2025", vencimento: "20/10/2026", status: "activo", dias: 51 },
    { id: "s6", clienteId: "c3", descricao: "Domínio da agência, renovação anual automática.", nome: "Domínio cmdigital.co.mz", plano: "Domínio .co.mz", categoria: "dominio", valor: 800, periodo: "ano", periodicidade: "Anual", inicio: "08/09/2024", vencimento: "08/09/2026", status: "a_vencer", dias: 9 },
    { id: "s7", clienteId: "c3", descricao: "Manutenção mensal do site: actualizações, backups e 4h de alterações.", nome: "Manutenção do site", plano: "4h / mês", categoria: "manutencao", valor: 2500, periodo: "mês", periodicidade: "Mensal", inicio: "01/08/2026", vencimento: "01/09/2026", status: "vencido", dias: -1 },
    { id: "s8", clienteId: "c4", descricao: "Acesso individual usado para análise de documentos.", nome: "Claude", plano: "Acesso individual", categoria: "ia", valor: 700, periodo: "mês", periodicidade: "Mensal", inicio: "15/08/2026", vencimento: "15/09/2026", status: "activo", dias: 16 },
    { id: "s9", clienteId: "c4", descricao: "5 contas de email corporativo com webmail e antispam.", nome: "Email corporativo", plano: "5 contas", categoria: "email", valor: 1200, periodo: "ano", periodicidade: "Anual", inicio: "09/09/2025", vencimento: "09/09/2026", status: "a_vencer", dias: 10 },
    { id: "s10", clienteId: "c5", descricao: "Acesso suspenso por falta de pagamento; conta ainda não removida.", nome: "ChatGPT", plano: "Acesso Equipe", categoria: "ia", valor: 500, periodo: "mês", periodicidade: "Mensal", inicio: "05/07/2026", vencimento: "05/08/2026", status: "suspenso", dias: -25 },
    { id: "s11", clienteId: "c2", descricao: "Alojamento partilhado do site da loja online, 5 GB SSD.", nome: "Hospedagem", plano: "Plano Start", categoria: "hospedagem", valor: 900, periodo: "ano", periodicidade: "Anual", inicio: "28/08/2026", vencimento: "28/08/2027", status: "activo", dias: 363 },
  ];
  const pagamentos = [
    { id: "p1", data: "30/08/2026", clienteId: "c2", servicoId: "s11", valor: 900, metodo: "mpesa", periodo: "1 ano" },
    { id: "p2", data: "28/08/2026", clienteId: "c2", servicoId: "s4", valor: 500, metodo: "transferencia", periodo: "1 mês" },
    { id: "p3", data: "20/08/2026", clienteId: "c4", servicoId: "s8", valor: 700, metodo: "emola", periodo: "1 mês" },
    { id: "p4", data: "15/08/2026", clienteId: "c1", servicoId: "s3", valor: 500, metodo: "mpesa", periodo: "1 mês" },
    { id: "p5", data: "12/08/2026", clienteId: "c3", servicoId: "s7", valor: 2500, metodo: "dinheiro", periodo: "1 mês" },
    { id: "p6", data: "04/08/2026", clienteId: "c4", servicoId: "s9", valor: 1200, metodo: "transferencia", periodo: "1 ano" },
    { id: "p7", data: "18/08/2026", clienteId: "c1", servicoId: "s1", valor: 1500, metodo: "mpesa", periodo: "1 ano" },
    { id: "p8", data: "14/08/2026", clienteId: "c2", servicoId: "s5", valor: 1000, metodo: "emola", periodo: "1 ano" },
    { id: "p9", data: "11/08/2026", clienteId: "c3", servicoId: "s6", valor: 800, metodo: "transferencia", periodo: "1 ano" },
    { id: "p10", data: "08/08/2026", clienteId: "c4", servicoId: "s8", valor: 700, metodo: "mpesa", periodo: "1 mês" },
    { id: "p11", data: "02/08/2026", clienteId: "c1", servicoId: "s2", valor: 800, metodo: "mpesa", periodo: "1 ano" },
  ];
  const historico = [
    { date: "01/09/2025", title: "Serviço criado", description: "Hospedagem — Plano Business", kind: "criado" },
    { date: "02/09/2025", title: "Pagamento recebido", description: "1.500 MZN — M-Pesa · 1 ano", kind: "pagamento" },
    { date: "02/09/2025", title: "Serviço activado", kind: "activado" },
    { date: "03/08/2026", title: "Lembrete enviado", description: "30 dias antes — joao@email.com", kind: "lembrete" },
    { date: "18/08/2026", title: "Lembrete enviado", description: "15 dias antes — joao@email.com", kind: "lembrete" },
    { date: "26/08/2026", title: "Lembrete enviado", description: "7 dias antes — joao@email.com", kind: "lembrete" },
  ];
  const byId = (arr) => Object.fromEntries(arr.map((x) => [x.id, x]));
  /* Single source of truth for every headline figure — no screen hard-codes money. */
  const MES = "08/2026";
  const doMes = pagamentos.filter((p) => p.data.slice(3) === MES);
  const aReceber = servicos.filter((s) => s.status === "vencido" || s.status === "a_vencer");
  const mensal = (s) => (s.periodo === "mês" ? s.valor : s.periodo === "trimestre" ? s.valor / 3 : s.valor / 12);
  const activos = servicos.filter((s) => s.status !== "cancelado" && s.status !== "suspenso");
  const totais = {
    mes: "Agosto 2026",
    entradas: doMes.reduce((t, p) => t + p.valor, 0),
    nPagamentos: doMes.length,
    aReceber: aReceber.reduce((t, s) => t + s.valor, 0),
    nAReceber: aReceber.length,
    emAtraso: servicos.filter((s) => s.status === "vencido").reduce((t, s) => t + s.valor, 0),
    nEmAtraso: servicos.filter((s) => s.status === "vencido").length,
    aVencer: servicos.filter((s) => s.status === "a_vencer").length,
    clientesActivos: clientes.filter((c) => c.status === "activo").length,
    servicosActivos: activos.length,
    mrr: Math.round(activos.reduce((t, s) => t + mensal(s), 0)),
  };
  totais.esperada = totais.entradas + totais.aReceber;
  totais.arr = totais.mrr * 12;
  return { clientes, servicos, pagamentos, historico, aReceber, pagamentosDoMes: doMes, totais, clientesById: byId(clientes) };
})();
