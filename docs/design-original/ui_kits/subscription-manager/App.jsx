const { SidebarNav, TopBar, Toast, Button, NAV_ITEMS } = window.SubscriptionManagerDesignSystem_6a702b;

function App() {
  const [route, setRoute] = React.useState({ view: "dashboard" });
  const [toast, setToast] = React.useState(null);
  const [newOpen, setNewOpen] = React.useState(false);
  const [collapsed, setCollapsed] = React.useState(false);
  const showToast = (t) => { setToast(t); setTimeout(() => setToast(null), 3200); };
  const nav = (view) => setRoute({ view });
  const openService = (id) => setRoute({ view: "servico", id, from: route.view === "servico" ? "vencimentos" : route.view, clienteId: route.clienteId });
  const openClient = (id) => setRoute({ view: "cliente", clienteId: id });
  const navActive = route.view === "cliente" ? "clientes" : route.view === "servico" ? "servicos" : route.view;
  const t = window.SM_DATA.totais;
  const navItems = NAV_ITEMS.map((it) => (it.id === "vencimentos" ? { ...it, badge: t.aVencer } : it.id === "pagamentos" ? { ...it, badge: t.nAReceber } : it));

  let body;
  if (route.view === "dashboard") body = <DashboardScreen onOpenService={openService} onNew={() => setNewOpen(true)} />;
  else if (route.view === "clientes") body = <ClientsScreen onOpenClient={openClient} onNew={() => setNewOpen(true)} />;
  else if (route.view === "cliente") body = <ClientDetailScreen clienteId={route.clienteId} onBack={() => nav("clientes")} onOpenService={openService} />;
  else if (route.view === "servico") body = <ServiceDetailScreen servicoId={route.id} onBack={() => (route.clienteId ? openClient(route.clienteId) : nav(route.from || "vencimentos"))} onToast={showToast} />;
  else if (route.view === "servicos") body = <ServicesScreen onOpenService={openService} onNew={() => setNewOpen(true)} />;
  else if (route.view === "vencimentos") body = <DueDatesScreen onOpenService={openService} />;
  else if (route.view === "pagamentos") body = <PaymentsScreen />;
  else if (route.view === "financas") body = <FinanceScreen />;
  else if (route.view === "configuracoes") body = <SettingsScreen />;
  else body = <HistoryScreen />;

  return (
    <div style={{ display: "flex", height: "100vh", overflow: "hidden", background: "var(--surface-app)" }}>
      <SidebarNav items={navItems} active={navActive} onSelect={nav} logoSrc="../../assets/logo-mark.png" collapsed={collapsed} onToggleCollapse={() => setCollapsed((c) => !c)} footer={<div style={{ display: "flex", alignItems: "center", gap: 8, padding: "10px 8px", borderTop: "1px solid var(--border-subtle)", fontSize: "var(--text-xs)", color: "var(--text-muted)" }}><span style={{ width: 6, height: 6, borderRadius: "50%", background: "var(--green-600)" }} />Verificação diária às 07:00</div>} />
      <div style={{ flex: 1, minWidth: 0, display: "flex", flexDirection: "column" }}>
        <TopBar alertCount={t.aVencer + t.nEmAtraso} actions={<Button variant="primary" size="sm" icon="plus" onClick={() => setNewOpen(true)}>Nova assinatura</Button>} />
        <main style={{ flex: 1, overflowY: "auto", padding: "22px var(--gutter) 40px" }}>
          <div style={{ maxWidth: "var(--page-max)", margin: "0 auto" }}>{body}</div>
        </main>
      </div>
      {newOpen ? <NewSubscriptionDialog onClose={() => setNewOpen(false)} onDone={() => { setNewOpen(false); showToast({ title: "Assinatura criada", body: "Lembretes automáticos activados." }); }} /> : null}
      {toast ? <div style={{ position: "fixed", right: 20, bottom: 20, zIndex: 80 }}><Toast title={toast.title} tone={toast.tone} onClose={() => setToast(null)}>{toast.body}</Toast></div> : null}
    </div>
  );
}
ReactDOM.createRoot(document.getElementById("root")).render(<App />);
