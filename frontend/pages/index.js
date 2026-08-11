import { useState, useEffect } from 'react';
import Head from 'next/head';
import Link from 'next/link';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function Dashboard() {
  const [projects, setProjects] = useState([]);
  const [user, setUser] = useState(null);
  const [token, setToken] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token');
    const u = localStorage.getItem('tersuite_user');
    setToken(t || '');
    if (u) setUser(JSON.parse(u));
    if (t) loadProjects(t);
    else setLoading(false);
  }, []);

  async function loadProjects(authToken) {
    try {
      const res = await fetch(API_BASE + 'projects/', {
        headers: { 'Authorization': 'Token ' + authToken }
      });
      if (res.ok) {
        const data = await res.json();
        setProjects(data.results || data || []);
      }
    } catch (e) {
      console.error('Failed to load projects:', e);
    } finally {
      setLoading(false);
    }
  }

  function handleLogout() {
    localStorage.removeItem('tersuite_token');
    localStorage.removeItem('tersuite_user');
    setToken('');
    setUser(null);
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">
      <Head>
        <title>Tersuite AI Studio — AI WordPress Plugin Generator</title>
        <meta name="description" content="Build WordPress plugins with Gemini 3.6 AI agent teams" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="true" />
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
      </Head>

      {/* Navigation Header */}
      <header className="border-b border-slate-800/80 bg-slate-900/50 backdrop-blur-xl sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-400 p-0.5 shadow-lg shadow-indigo-500/20">
              <div className="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center font-black text-indigo-400">
                ⚡
              </div>
            </div>
            <div>
              <span className="text-xl font-extrabold bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
                Tersuite AI Studio
              </span>
              <span className="ml-2 px-2 py-0.5 text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full">
                Gemini 3.6 Powered
              </span>
            </div>
          </div>

          <nav className="flex items-center gap-8 text-sm font-medium">
            <Link href="/" className="text-indigo-400 font-semibold">Dashboard</Link>
            <Link href="/api-keys" className="text-slate-400 hover:text-slate-200 transition">API Keys</Link>
            <Link href="/subscription" className="text-slate-400 hover:text-slate-200 transition">Subscriptions</Link>
            <a href="http://localhost:8000/admin/" target="_blank" rel="noreferrer" className="text-slate-400 hover:text-slate-200 transition">Admin Portal ↗</a>
          </nav>

          <div className="flex items-center gap-4">
            {token ? (
              <div className="flex items-center gap-3">
                <span className="text-xs text-slate-400 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-lg">
                  🔑 API Token Active
                </span>
                <button onClick={handleLogout} className="text-xs bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 px-3 py-1.5 rounded-lg font-medium transition">
                  Logout
                </button>
              </div>
            ) : (
              <div className="flex items-center gap-3">
                <Link href="/login" className="text-sm font-medium text-slate-300 hover:text-white px-4 py-2">Log in</Link>
                <Link href="/register" className="text-sm font-semibold bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25 transition">
                  Get Started
                </Link>
              </div>
            )}
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="relative overflow-hidden py-16 border-b border-slate-800/50">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.15),rgba(255,255,255,0))] pointer-events-none" />
        <div className="max-w-6xl mx-auto px-6 text-center">
          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/80 border border-slate-800 text-xs font-semibold text-cyan-400 mb-6">
            <span className="w-2 h-2 rounded-full bg-cyan-400 animate-pulse" />
            Active Models: Gemini 3.6 Flash & Gemini 3.1 Pro
          </div>
          <h1 className="text-5xl md:text-6xl font-extrabold tracking-tight text-white mb-6">
            Generate Production WordPress Plugins with <span className="bg-gradient-to-r from-indigo-400 via-cyan-400 to-emerald-400 bg-clip-text text-transparent">Multi-Agent AI</span>
          </h1>
          <p className="text-lg text-slate-400 max-w-2xl mx-auto mb-8 leading-relaxed">
            Connect your WordPress site to autonomous Gemini 3.6 agents. 7 sub-agents (Coordinator, UI/UX, Backend, Frontend, Security, Reviewer, Sandbox) design, test, and zip plugins in real-time.
          </p>

          <div className="flex flex-wrap items-center justify-center gap-4">
            <Link href="/api-keys" className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-8 py-3.5 rounded-xl shadow-xl shadow-indigo-600/30 transition">
              Get API Key for WordPress
            </Link>
            <a href="http://localhost:8000/api/projects/" target="_blank" rel="noreferrer" className="bg-slate-900 hover:bg-slate-800 text-slate-200 font-semibold border border-slate-800 px-8 py-3.5 rounded-xl transition">
              Explore API Docs ↗
            </a>
          </div>
        </div>
      </section>

      {/* Active AI Agent Pipeline Visualizer */}
      <section className="py-16 max-w-7xl mx-auto px-6">
        <h2 className="text-2xl font-bold text-slate-200 mb-2">Autonomous Agent Team Pipeline</h2>
        <p className="text-sm text-slate-400 mb-8">Each generation executes concurrently across 7 specialized CrewAI workers.</p>

        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
          {[
            { title: "1. Coordinator", desc: "Interactive Planning", icon: "🎯", color: "border-indigo-500/30 bg-indigo-500/5 text-indigo-400" },
            { title: "2. UI/UX Agent", desc: "WP Admin Layouts", icon: "🎨", color: "border-cyan-500/30 bg-cyan-500/5 text-cyan-400" },
            { title: "3. Frontend", desc: "JS/AJAX Controls", icon: "⚡", color: "border-blue-500/30 bg-blue-500/5 text-blue-400" },
            { title: "4. Backend", desc: "PHP & WP Hooks", icon: "⚙️", color: "border-emerald-500/30 bg-emerald-500/5 text-emerald-400" },
            { title: "5. Security", desc: "Nonces & ABSPATH", icon: "🛡️", color: "border-amber-500/30 bg-amber-500/5 text-amber-400" },
            { title: "6. Reviewer", desc: "AST Code Check", icon: "🔍", color: "border-purple-500/30 bg-purple-500/5 text-purple-400" },
            { title: "7. Sandbox", desc: "MicroVM Test & Zip", icon: "📦", color: "border-rose-500/30 bg-rose-500/5 text-rose-400" }
          ].map((agent, i) => (
            <div key={i} className={`p-4 rounded-xl border ${agent.color} transition hover:scale-105`}>
              <div className="text-2xl mb-2">{agent.icon}</div>
              <div className="text-xs font-bold text-slate-200">{agent.title}</div>
              <div className="text-[11px] text-slate-400 mt-1">{agent.desc}</div>
            </div>
          ))}
        </div>
      </section>

      {/* WordPress Plugin Direct Download */}
      <section className="py-12 bg-slate-900/40 border-y border-slate-800/80">
        <div className="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 rounded-xl bg-blue-600/10 border border-blue-500/30 flex items-center justify-center text-2xl">
              🔌
            </div>
            <div>
              <h3 className="text-lg font-bold text-white">Connect Your Local or Live WordPress Site</h3>
              <p className="text-sm text-slate-400">Install the Tersuite WordPress plugin to chat with agents and build plugins inside your WP Admin.</p>
            </div>
          </div>
          <a href="/api-keys" className="whitespace-nowrap bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-emerald-600/20 transition">
            Generate Plugin Connection Key
          </a>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-8 text-center text-xs text-slate-500 border-t border-slate-900">
        Tersuite AI Studio © 2026 — Multi-Agent WordPress Generation Platform
      </footer>
    </div>
  );
}
