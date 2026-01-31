export function getToken() {
  return localStorage.getItem('helpdesk_token') || ''
}

export function setToken(token) {
  if (!token) localStorage.removeItem('helpdesk_token')
  else localStorage.setItem('helpdesk_token', token)
}

function getApiBaseUrl() {
  const v = (import.meta.env?.VITE_API_BASE_URL || '').trim()
  return v.endsWith('/') ? v.slice(0, -1) : v
}

function apiUrl(path) {
  const base = getApiBaseUrl()
  if (!base) return path
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `${base}${path.startsWith('/') ? path : `/${path}`}`
}

async function apiFetch(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json' }
  const token = getToken()
  if (token) headers.Authorization = `Bearer ${token}`
  if (body !== undefined) headers['Content-Type'] = 'application/json'

  const res = await fetch(apiUrl(path), {
    method,
    headers,
    body: body === undefined ? undefined : JSON.stringify(body),
  })

  const text = await res.text()
  let json = null
  if (text) {
    try {
      json = JSON.parse(text)
    } catch {
      json = null
    }
  }

  if (!res.ok) {
    const msg =
      json?.message ||
      (text ? `Request failed (${res.status}): ${text.slice(0, 140)}` : `Request failed (${res.status})`)
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

