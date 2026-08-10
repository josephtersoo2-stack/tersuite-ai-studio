import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';

// API base URL points to Django backend
const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function Dashboard() {
  const router = useRouter();
  const [projects, setProjects] = useState([]);
  const [token, setToken] = useState('');

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token');
    setToken(t || '');
    if (t) loadProjects(t);
  }, []);

  async function loadProjects(authToken) {
    try {
      const res = await fetch(API_BASE + 'projects/', {
        headers: { 'Authorization': 'Token ' + authToken }
      });
      const data = await res.json();
      setProjects(data.results || data || []);
    } catch (e) {
      console.error('Failed to load projects:', e);
    }
  }

  return (
    <div className="min-h-screen bg-slate-950 text-white p-8">
      <nav className="flex items-center justify-between mb-12">
        <h1 className="text-3xl font-bold">Tersuite AI Studio</h1>
        <div className="flex gap-4">
          <a href="/subscription" className="text-slate-300 hover:text-white">Subscription</a>
          <a href="/api-keys" className="text-slate-300 hover:text-white">API Keys</a>
        </div>
      </nav>

      <section className="max-w-4xl mx-auto">
        <h2 className="text-4xl font-extrabold mb-4">Generate WordPress Plugins with AI Agents</h2>
        <p className="text-slate-400 mb-8">Multi-agent pipeline. Real-time streaming. Industry-grade security.</p>
        <a href="/register" className="inline-block bg-green-600 hover:bg-green-700 text-white font-bold px-8 py-4 rounded-lg transition">Get Started</a>
      </section>

      <section className="max-w-5xl mx-auto mt-20 grid md:grid-cols-3 gap-6">
        <div className="bg-slate-900 rounded-xl p-6 border border-slate-800">
          <h3 className="text-xl font-bold mb-2">Multi-Agent Pipeline</h3>
          <p className="text-slate-400">Coordinator + 7 specialist sub-agents work in parallel.</p>
        </div>
        <div className="bg-slate-900 rounded-xl p-6 border border-slate-800">
          <h3 className="text-xl font-bold mb-2">Real-Time Streaming</h3>
          <p className="text-slate-400">Django Channels streams agent progress to your dashboard.</p>
        </div>
        <div className="bg-slate-900 rounded-xl p-6 border border-slate-800">
          <h3 className="text-xl font-bold mb-2">MicroVM Sandbox</h3>
          <p className="text-slate-400">Firecracker isolation tests plugins before delivery.</p>
        </div>
      </section>
    </div>
  );
}
