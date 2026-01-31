import { describe, expect, it, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import App from './App'

describe('App', () => {
  it('renders header and shows login when unauthenticated', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => ({
      ok: false,
      status: 401,
      text: async () => JSON.stringify({ message: 'Unauthenticated' }),
    })))

    render(<App />)
    expect(screen.getByText('Help Desk')).toBeInTheDocument()
    expect(await screen.findByRole('button', { name: 'Login' })).toBeInTheDocument()
  })
})

