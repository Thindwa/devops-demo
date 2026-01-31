export function getToken() {
  return localStorage.getItem('helpdesk_token') || ''
}

export function setToken(token) {
  if (!token) localStorage.removeItem('helpdesk_token')
  else localStorage.setItem('helpdesk_token', token)
}

async function apiFetch(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json' }
  const token = getToken()
  if (token) headers.Authorization = `Bearer ${token}`
  if (body !== undefined) headers['Content-Type'] = 'application/json'

  const res = await fetch(path, {
    method,
    headers,
    body: body === undefined ? undefined : JSON.stringify(body),
  })

  const text = await res.text()
  const json = text ? JSON.parse(text) : null

  if (!res.ok) {
    const msg = json?.message || `Request failed (${res.status})`
    const err = new Error(msg)
    err.status = res.status
    err.payload = json
    throw err
  }

  return json
}

export const api = {
  login: (email, password) => apiFetch('/api/login', { method: 'POST', body: { email, password } }),
  me: () => apiFetch('/api/me'),
  logout: () => apiFetch('/api/logout', { method: 'POST' }),
  listTickets: () => apiFetch('/api/tickets'),
  createTicket: (title, description) => apiFetch('/api/tickets', { method: 'POST', body: { title, description } }),
  updateTicketStatus: (id, status) => apiFetch(`/api/tickets/${id}/status`, { method: 'PATCH', body: { status } }),
  listUsers: () => apiFetch('/api/users'),
}

