import { useEffect, useMemo, useState } from 'react'
import { api, setToken } from './api'
import './App.css'

function Card({ title, children }) {
  return (
    <div style={{ border: '1px solid #e5e7eb', borderRadius: 12, padding: 16, marginBottom: 16 }}>
      <div style={{ fontWeight: 700, marginBottom: 12 }}>{title}</div>
      {children}
    </div>
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

  const loginHint = useMemo(
    () => (
      <div style={{ fontSize: 12, color: '#6b7280' }}>
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

  return (
    <div style={{ maxWidth: 900, margin: '0 auto', padding: 24 }}>
      <h1 style={{ marginBottom: 8 }}>Help Desk</h1>
      <div style={{ color: '#6b7280', marginBottom: 16 }}>Backend: Laravel API + Sanctum. Frontend: React (Vite).</div>
      {auth.loading ? <div style={{ padding: 12 }}>Loading…</div> : null}
      {error ? (
        <div style={{ background: '#fee2e2', padding: 12, borderRadius: 8, marginBottom: 16 }}>{error}</div>
      ) : null}

      {!auth.loading && !auth.user ? (
        <Card title="Login">
          <form onSubmit={onLogin} style={{ display: 'grid', gap: 8, maxWidth: 420 }}>
            <label>
              Email
              <input value={email} onChange={(e) => setEmail(e.target.value)} style={{ width: '100%' }} />
            </label>
            <label>
              Password
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                style={{ width: '100%' }}
              />
            </label>
            <button type="submit">Login</button>
            {loginHint}
          </form>
        </Card>
      ) : null}

      {!auth.loading && auth.user ? (
        <Card title="Session">
          <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, alignItems: 'center' }}>
            <div>
              Logged in as <b>{auth.user.name}</b> ({auth.user.email}) — role: <b>{auth.user.role}</b>
            </div>
            <button onClick={onLogout}>Logout</button>
          </div>
        </Card>
      ) : null}

      {!auth.loading && auth.user ? (
        <>
          <Card title="Create ticket">
            <form onSubmit={onCreateTicket} style={{ display: 'grid', gap: 8 }}>
              <input placeholder="Title" value={title} onChange={(e) => setTitle(e.target.value)} />
              <textarea
                placeholder="Description"
                rows={4}
                value={description}
                onChange={(e) => setDescription(e.target.value)}
              />
              <button type="submit">Submit</button>
            </form>
          </Card>

          <Card title={`Tickets (${tickets.length})`}>
            <div style={{ display: 'grid', gap: 12 }}>
              {tickets.map((t) => (
                <div key={t.id} style={{ border: '1px solid #e5e7eb', borderRadius: 10, padding: 12 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12 }}>
                    <div>
                      <div style={{ fontWeight: 700 }}>{t.title}</div>
                      <div style={{ color: '#6b7280', fontSize: 12 }}>
                        by {t.user?.name} ({t.user?.role}) • #{t.id}
                      </div>
                    </div>
                    <div>
                      {canManageTickets ? (
                        <select value={t.status} onChange={(e) => onChangeStatus(t.id, e.target.value)}>
                          <option value="open">open</option>
                          <option value="in_progress">in_progress</option>
                          <option value="closed">closed</option>
                        </select>
                      ) : (
                        <span style={{ fontFamily: 'monospace' }}>{t.status}</span>
                      )}
                    </div>
                  </div>
                  <div style={{ marginTop: 8 }}>{t.description}</div>
                </div>
              ))}
              {tickets.length === 0 ? <div>No tickets yet.</div> : null}
            </div>
          </Card>

          {isAdmin ? (
            <Card title={`Users (${users.length})`}>
              <table style={{ width: '100%', borderCollapse: 'collapse' }}>
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
          ) : null}
        </>
      ) : null}
    </div>
  )
}

export default App
