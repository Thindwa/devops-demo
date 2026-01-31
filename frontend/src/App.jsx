import { useEffect, useMemo, useState } from 'react'
import { api, setToken } from './api'
import './App.css'

function Card({ title, children }) {
  return (
    <div className="card">
      <div className="card-title">{title}</div>
      {children}
    </div>
  )
}

function NavButton({ active, label, badge, onClick }) {
  return (
    <button type="button" className={`nav-btn${active ? ' active' : ''}`} onClick={onClick}>
      <span>{label}</span>
      {badge !== undefined ? <span className="badge">{badge}</span> : null}
    </button>
  )
}

function App() {
  const [auth, setAuth] = useState({ user: null, loading: true })
  const [email, setEmail] = useState('user@example.com')
  const [password, setPassword] = useState('password')
  const [error, setError] = useState('')

  const [tickets, setTickets] = useState([])
  const [users, setUsers] = useState([])
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [active, setActive] = useState('tickets')

  const role = auth.user?.role || ''
  const canManageTickets = role === 'agent' || role === 'admin'
  const isAdmin = role === 'admin'

  async function refresh() {
    const t = await api.listTickets()
    setTickets(t)
    if (isAdmin) setUsers(await api.listUsers())
  }

  useEffect(() => {
    ;(async () => {
      try {
        const me = await api.me()
        setAuth({ user: me, loading: false })
        await refresh()
      } catch {
        setAuth({ user: null, loading: false })
      }
    })()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    if (!auth.user) return
    setActive(auth.user.role === 'admin' ? 'dashboard' : 'tickets')
  }, [auth.user?.role]) // eslint-disable-line react-hooks/exhaustive-deps

  const loginHint = useMemo(
    () => (
      <div className="muted" style={{ fontSize: 12 }}>
        Demo users:
        <div>
          <code>user@example.com</code>, <code>agent@example.com</code>, <code>admin@example.com</code> (password:{' '}
          <code>password</code>)
        </div>
      </div>
    ),
    [],
  )

  async function onLogin(e) {
    e.preventDefault()
    setError('')
    try {
      const res = await api.login(email, password)
      setToken(res.token)
      setAuth({ user: res.user, loading: false })
      await refresh()
    } catch (err) {
      setError(err.message || 'Login failed')
    }
  }

  async function onLogout() {
    try {
      await api.logout()
    } finally {
      setToken('')
      setAuth({ user: null, loading: false })
      setTickets([])
      setUsers([])
      setActive('tickets')
    }
  }

  async function onCreateTicket(e) {
    e.preventDefault()
    setError('')
    try {
      await api.createTicket(title, description)
      setTitle('')
      setDescription('')
      await refresh()
      setActive('tickets')
    } catch (err) {
      setError(err.message || 'Failed to create ticket')
    }
  }

  async function onChangeStatus(id, status) {
    setError('')
    try {
      await api.updateTicketStatus(id, status)
      await refresh()
    } catch (err) {
      setError(err.message || 'Failed to update status')
    }
  }

  const ticketCounts = useMemo(() => {
    const out = { open: 0, in_progress: 0, closed: 0 }
    for (const t of tickets) {
      if (t?.status && Object.prototype.hasOwnProperty.call(out, t.status)) out[t.status] += 1
    }
    return out
  }, [tickets])

  const pageTitle = useMemo(() => {
    if (!auth.user) return 'Help Desk'
    if (active === 'dashboard') return 'Admin dashboard'
    if (active === 'tickets') return 'Tickets'
    if (active === 'create') return 'Create ticket'
    if (active === 'users') return 'Users'
    if (active === 'session') return 'Session'
    return 'Help Desk'
  }, [active, auth.user])

  function TicketList() {
    return (
      <Card title={`Tickets (${tickets.length})`}>
        <div className="grid">
          {tickets.map((t) => (
            <div key={t.id} className="ticket">
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, alignItems: 'center' }}>
                <div>
                  <div className="ticket-title">{t.title}</div>
                  <div className="ticket-meta">
                    by {t.user?.name} ({t.user?.role}) • #{t.id}
                  </div>
                </div>
                <div>
                  {canManageTickets ? (
                    <select className="select" value={t.status} onChange={(e) => onChangeStatus(t.id, e.target.value)}>
                      <option value="open">open</option>
                      <option value="in_progress">in_progress</option>
                      <option value="closed">closed</option>
                    </select>
                  ) : (
                    <span className="pill">{t.status}</span>
                  )}
                </div>
              </div>
              <div className="ticket-body">{t.description}</div>
            </div>
          ))}
          {tickets.length === 0 ? <div className="muted">No tickets yet.</div> : null}
        </div>
      </Card>
    )
  }

  function CreateTicket() {
    return (
      <Card title="Create ticket">
        <form onSubmit={onCreateTicket} className="grid">
          <div className="field">
            <div className="muted" style={{ fontSize: 12 }}>
              Title
            </div>
            <input className="input" placeholder="Title" value={title} onChange={(e) => setTitle(e.target.value)} />
          </div>
          <div className="field">
            <div className="muted" style={{ fontSize: 12 }}>
              Description
            </div>
            <textarea
              className="textarea"
              placeholder="Description"
              rows={5}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>
          <button className="btn primary" type="submit">
            Submit
          </button>
        </form>
      </Card>
    )
  }

  function UsersTable() {
    if (!isAdmin) return null
    return (
      <Card title={`Users (${users.length})`}>
        <table className="table">
          <thead>
            <tr>
              <th align="left">ID</th>
              <th align="left">Name</th>
              <th align="left">Email</th>
              <th align="left">Role</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id}>
                <td>{u.id}</td>
                <td>{u.name}</td>
                <td>{u.email}</td>
                <td>{u.role}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>
    )
  }

  function Dashboard() {
    if (!isAdmin) return <TicketList />
    return (
      <>
        <Card title="Overview">
          <div className="grid cols-3">
            <div className="stat">
              <div className="stat-k">Total tickets</div>
              <div className="stat-v">{tickets.length}</div>
            </div>
            <div className="stat">
              <div className="stat-k">Open</div>
              <div className="stat-v">{ticketCounts.open}</div>
            </div>
            <div className="stat">
              <div className="stat-k">Users</div>
              <div className="stat-v">{users.length}</div>
            </div>
          </div>
        </Card>
        <TicketList />
        <UsersTable />
      </>
    )
  }

  return (
    <div className="app">
      {auth.loading ? (
        <div className="login-shell">
          <h1 className="login-title">Help Desk</h1>
          <div className="muted">Loading…</div>
        </div>
      ) : null}

      {!auth.loading && !auth.user ? (
        <div className="login-shell">
          <h1 className="login-title">Help Desk</h1>
          <div className="muted" style={{ marginBottom: 14 }}>
            Backend: Laravel API + Sanctum. Frontend: React (Vite).
          </div>
          {error ? <div className="alert">{error}</div> : null}
          <Card title="Login">
            <form onSubmit={onLogin} className="grid" style={{ maxWidth: 460 }}>
              <div className="field">
                <div className="muted" style={{ fontSize: 12 }}>
                  Email
                </div>
                <input className="input" value={email} onChange={(e) => setEmail(e.target.value)} />
              </div>
              <div className="field">
                <div className="muted" style={{ fontSize: 12 }}>
                  Password
                </div>
                <input
                  className="input"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </div>
              <button className="btn primary" type="submit">
                Login
              </button>
              {loginHint}
            </form>
          </Card>
        </div>
      ) : null}

      {!auth.loading && auth.user ? (
        <div className="shell">
          <aside className="sidebar">
            <div className="brand">
              <div className="brand-name">Help Desk</div>
              <div className="pill">
                <span style={{ fontWeight: 800 }}>{auth.user.name}</span>
                <span className="muted">•</span>
                <span className="muted">{auth.user.role}</span>
              </div>
              <div className="muted" style={{ fontSize: 12 }}>
                {auth.user.email}
              </div>
            </div>

            <div className="sidebar-section">
              <div className="sidebar-label">Navigation</div>
              <div className="nav">
                {isAdmin ? (
                  <NavButton active={active === 'dashboard'} label="Dashboard" onClick={() => setActive('dashboard')} />
                ) : null}
                <NavButton
                  active={active === 'tickets'}
                  label="Tickets"
                  badge={tickets.length}
                  onClick={() => setActive('tickets')}
                />
                <NavButton active={active === 'create'} label="Create ticket" onClick={() => setActive('create')} />
                {isAdmin ? (
                  <NavButton
                    active={active === 'users'}
                    label="Users"
                    badge={users.length}
                    onClick={() => setActive('users')}
                  />
                ) : null}
                <NavButton active={active === 'session'} label="Session" onClick={() => setActive('session')} />
              </div>
            </div>

            <div className="sidebar-footer">
              <button className="btn danger full" type="button" onClick={onLogout}>
                Logout
              </button>
            </div>
          </aside>

          <main className="main">
            <div className="topbar">
              <div className="topbar-title">{pageTitle}</div>
              <div className="muted" style={{ fontSize: 12 }}>
                Backend: Laravel • Frontend: React
              </div>
            </div>

            <div className="content">
              {error ? <div className="alert">{error}</div> : null}

              {active === 'dashboard' ? <Dashboard /> : null}
              {active === 'tickets' ? <TicketList /> : null}
              {active === 'create' ? <CreateTicket /> : null}
              {active === 'users' ? <UsersTable /> : null}
              {active === 'session' ? (
                <Card title="Session">
                  <div className="grid">
                    <div>
                      Logged in as <b>{auth.user.name}</b> ({auth.user.email})
                    </div>
                    <div>
                      Role: <b>{auth.user.role}</b>
                    </div>
                    <button className="btn" type="button" onClick={refresh}>
                      Refresh data
                    </button>
                  </div>
                </Card>
              ) : null}
            </div>
          </main>
        </div>
      ) : null}
    </div>
  )
}

export default App
