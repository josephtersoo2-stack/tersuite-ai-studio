import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { Sparkles, Key, CreditCard, LayoutDashboard, LogOut, LogIn, UserPlus, ExternalLink, Bot } from 'lucide-react';

export default function Navbar() {
  const router = useRouter();
  const [token, setToken] = useState('');
  const [username, setUsername] = useState('');

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token');
    const u = localStorage.getItem('tersuite_username');
    setToken(t || '');
    setUsername(u || 'Developer');
  }, []);

  function handleLogout() {
    localStorage.removeItem('tersuite_token');
    localStorage.removeItem('tersuite_username');
    setToken('');
    router.push('/login');
  }

  const isActive = (path) => router.pathname === path;

  return (
    <header className="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-xl border-b border-slate-800/80">
      <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        {/* Brand Logo */}
        <Link href="/" className="flex items-center gap-3 group">
          <div className="w-11 h-11 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 p-0.5 shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/40 transition">
            <div className="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center font-black text-indigo-400">
              <Bot className="w-6 h-6 text-cyan-400 group-hover:scale-110 transition-transform" />
            </div>
          </div>
          <div>
            <div className="flex items-center gap-2">
              <span className="text-xl font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">
                Tersuite AI
              </span>
              <span className="text-xs font-bold text-cyan-400 bg-cyan-400/10 border border-cyan-400/20 px-2 py-0.5 rounded-full">
                Studio
              </span>
            </div>
            <p className="text-[11px] text-slate-400 font-medium">Gemini 3.6 Multi-Agent Platform</p>
          </div>
        </Link>

        {/* Center Nav Links */}
        <nav className="hidden md:flex items-center gap-1 bg-slate-900/60 border border-slate-800 p-1.5 rounded-xl">
          <Link
            href="/"
            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition ${
              isActive('/') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
            }`}
          >
            <Sparkles className="w-4 h-4" /> Home
          </Link>
          {token && (
            <Link
              href="/dashboard"
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition ${
                isActive('/dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
              }`}
            >
              <LayoutDashboard className="w-4 h-4" /> Dashboard
            </Link>
          )}
          <Link
            href="/api-keys"
            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition ${
              isActive('/api-keys') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
            }`}
          >
            <Key className="w-4 h-4" /> API Keys
          </Link>
          <Link
            href="/subscription"
            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition ${
              isActive('/subscription') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
            }`}
          >
            <CreditCard className="w-4 h-4" /> Pricing
          </Link>
        </nav>

        {/* User Auth Buttons */}
        <div className="flex items-center gap-4">
          <a
            href="http://localhost:8000/admin/"
            target="_blank"
            rel="noreferrer"
            className="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-slate-200 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl transition"
          >
            Django Admin <ExternalLink className="w-3.5 h-3.5" />
          </a>

          {token ? (
            <div className="flex items-center gap-3">
              <div className="hidden sm:flex items-center gap-2 text-xs font-medium text-slate-300 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl">
                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                <span>{username}</span>
              </div>
              <button
                onClick={handleLogout}
                className="flex items-center gap-1.5 text-xs font-bold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-4 py-2 rounded-xl transition"
              >
                <LogOut className="w-3.5 h-3.5" /> Logout
              </button>
            </div>
          ) : (
            <div className="flex items-center gap-3">
              <Link
                href="/login"
                className="flex items-center gap-1.5 text-sm font-semibold text-slate-300 hover:text-white px-4 py-2 transition"
              >
                <LogIn className="w-4 h-4" /> Login
              </Link>
              <Link
                href="/register"
                className="flex items-center gap-1.5 text-sm font-bold bg-gradient-to-r from-indigo-500 via-indigo-600 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25 transition glow-btn"
              >
                <UserPlus className="w-4 h-4" /> Register
              </Link>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
