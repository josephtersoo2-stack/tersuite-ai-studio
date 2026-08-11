import { useState, useEffect } from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { useRouter } from 'next/router';
import Navbar from '../components/Navbar';
import { Plus, Bot, Key, Download, CheckCircle2, Clock, Terminal, Copy, RefreshCw, Cpu, Layers, Sparkles } from 'lucide-react';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function UserDashboard() {
  const router = useRouter();
  const [token, setToken] = useState('');
  const [username, setUsername] = useState('');
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [creating, setCreating] = useState(false);
  const [copiedToken, setCopiedToken] = useState(false);

  // New Project Form State
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [selectedModel, setSelectedModel] = useState('gemini-3.6-flash');

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token') || 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622';
    const u = localStorage.getItem('tersuite_username') || 'Developer';
    setToken(t);
    setUsername(u);
    fetchProjects(t);
  }, []);

  async function fetchProjects(authToken) {
    setLoading(true);
    try {
      const res = await fetch(API_BASE + 'projects/', {
        headers: { 'Authorization': 'Token ' + authToken }
      });
      if (res.ok) {
        const data = await res.json();
        setProjects(data.results || data || []);
      }
    } catch (e) {
      console.error('Error fetching projects:', e);
    } finally {
      setLoading(false);
    }
  }

  async function handleCreateProject(e) {
    e.preventDefault();
    if (!title.trim()) return;
    setCreating(true);
    try {
      const res = await fetch(API_BASE + 'projects/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Token ' + token
        },
        body: JSON.stringify({
          name: title,
          description: description,
          metadata: { model: selectedModel }
        })
      });
      if (res.ok) {
        setTitle('');
        setDescription('');
        fetchProjects(token);
        alert('Project created successfully!');
      } else {
        const err = await res.json();
        alert('Failed to create project: ' + JSON.stringify(err));
      }
    } catch (err) {
      alert('Error creating project: ' + err.message);
    } finally {
      setCreating(false);
    }
  }

  function copyToken() {
    navigator.clipboard.writeText(token);
    setCopiedToken(true);
    setTimeout(() => setCopiedToken(false), 2000);
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans">
      <Head>
        <title>User Dashboard — Tersuite AI Studio</title>
      </Head>

      <Navbar />

      <main className="max-w-7xl mx-auto px-6 py-10">
        {/* Welcome Header */}
        <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-10 pb-8 border-b border-slate-800">
          <div>
            <div className="flex items-center gap-3 mb-1">
              <h1 className="text-3xl font-extrabold text-white">Welcome back, {username}! 👋</h1>
              <span className="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-bold text-emerald-400">
                Studio Active
              </span>
            </div>
            <p className="text-sm text-slate-400">Manage AI plugin generation projects, API connection tokens, and agent workflows.</p>
          </div>

          <div className="flex items-center gap-3 bg-slate-900 border border-slate-800 px-4 py-2.5 rounded-xl font-mono text-xs text-slate-300">
            <Key className="w-4 h-4 text-indigo-400" />
            <span className="hidden sm:inline">Token:</span>
            <span className="text-slate-400">{token.substring(0, 12)}...</span>
            <button
              onClick={copyToken}
              className="ml-2 bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 px-2.5 py-1 rounded border border-indigo-500/30 transition text-[11px]"
            >
              {copiedToken ? '✓ Copied' : 'Copy'}
            </button>
          </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Main Column: Project Generator & List */}
          <div className="lg:col-span-2 space-y-8">
            {/* Create Project Card */}
            <div className="glass-card rounded-2xl p-6 border border-slate-800">
              <div className="flex items-center gap-2 mb-4">
                <Plus className="w-5 h-5 text-indigo-400" />
                <h2 className="text-lg font-bold text-white">Create New WordPress Plugin Project</h2>
              </div>

              <form onSubmit={handleCreateProject} className="space-y-4">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Plugin Title</label>
                  <input
                    type="text"
                    required
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    placeholder="e.g. WooCommerce Custom Discount Matrix"
                    className="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white rounded-xl px-4 py-3 text-sm outline-none transition"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Plugin Requirements / Description</label>
                  <textarea
                    rows={3}
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    placeholder="Describe what features, WP admin pages, nonces, or REST API endpoints this plugin needs..."
                    className="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white rounded-xl px-4 py-3 text-sm outline-none transition"
                  />
                </div>

                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                  <div className="flex items-center gap-2">
                    <Sparkles className="w-4 h-4 text-cyan-400" />
                    <span className="text-xs font-semibold text-slate-400">AI Model:</span>
                    <select
                      value={selectedModel}
                      onChange={(e) => setSelectedModel(e.target.value)}
                      className="bg-slate-950 border border-slate-800 text-cyan-400 font-semibold text-xs rounded-lg px-3 py-2 outline-none"
                    >
                      <option value="gemini-3.6-flash">Gemini 3.6 Flash (Recommended)</option>
                      <option value="gemini-3.5-flash">Gemini 3.5 Flash</option>
                      <option value="gemini-3.1-pro-preview">Gemini 3.1 Pro Preview</option>
                    </select>
                  </div>

                  <button
                    type="submit"
                    disabled={creating}
                    className="w-full sm:w-auto bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-indigo-500/20 transition flex items-center justify-center gap-2 text-sm"
                  >
                    {creating ? <RefreshCw className="w-4 h-4 animate-spin" /> : <Bot className="w-4 h-4" />}
                    {creating ? 'Initializing Agents...' : 'Start AI Generation'}
                  </button>
                </div>
              </form>
            </div>

            {/* Projects List */}
            <div className="glass-card rounded-2xl p-6 border border-slate-800">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-lg font-bold text-white flex items-center gap-2">
                  <Layers className="w-5 h-5 text-cyan-400" /> Your Projects ({projects.length})
                </h2>
                <button
                  onClick={() => fetchProjects(token)}
                  className="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition"
                >
                  <RefreshCw className="w-3.5 h-3.5" /> Refresh
                </button>
              </div>

              {loading ? (
                <div className="py-12 text-center text-slate-500 text-sm">Loading projects from backend API...</div>
              ) : projects.length === 0 ? (
                <div className="py-12 text-center text-slate-500 border border-dashed border-slate-800 rounded-xl p-8">
                  <Bot className="w-10 h-10 mx-auto text-slate-600 mb-3" />
                  <p className="text-sm font-semibold text-slate-400 mb-1">No plugin projects created yet.</p>
                  <p className="text-xs text-slate-500">Fill out the form above to deploy your 7-agent AI team!</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {projects.map((p, idx) => (
                    <div key={p.id || idx} className="bg-slate-950 border border-slate-800/80 rounded-xl p-5 hover:border-slate-700 transition">
                      <div className="flex items-start justify-between gap-4 mb-2">
                        <h3 className="font-bold text-white text-base">{p.name || 'Untitled Project'}</h3>
                        <span className="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                          <CheckCircle2 className="w-3 h-3" /> {p.status || 'Active'}
                        </span>
                      </div>
                      <p className="text-xs text-slate-400 mb-4 line-clamp-2">{p.description || 'No description provided.'}</p>
                      <div className="flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-900">
                        <span>ID: #{p.id || idx + 1}</span>
                        <span className="text-indigo-400 font-semibold font-mono">Model: {p.metadata?.model || 'gemini-3.6-flash'}</span>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Sidebar: Agent System & Connection Guide */}
          <div className="space-y-6">
            <div className="glass-card rounded-2xl p-6 border border-slate-800">
              <h3 className="text-sm font-bold uppercase tracking-wider text-slate-300 mb-4 flex items-center gap-2">
                <Cpu className="w-4 h-4 text-indigo-400" /> Active AI Provider Engine
              </h3>
              <div className="space-y-3 font-mono text-xs">
                <div className="flex justify-between items-center bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span className="text-slate-400">Provider:</span>
                  <span className="text-emerald-400 font-bold">Google Gemini</span>
                </div>
                <div className="flex justify-between items-center bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span className="text-slate-400">Default Model:</span>
                  <span className="text-indigo-400 font-bold">gemini-3.6-flash</span>
                </div>
                <div className="flex justify-between items-center bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span className="text-slate-400">Task Workers:</span>
                  <span className="text-cyan-400 font-bold">Celery + Redis</span>
                </div>
              </div>
            </div>

            <div className="glass-card rounded-2xl p-6 border border-slate-800">
              <h3 className="text-sm font-bold text-white mb-3">WordPress Plugin Connect</h3>
              <p className="text-xs text-slate-400 mb-4 leading-relaxed">
                Connect your WordPress site to this studio to view real-time WebSocket progress and download generated ZIP packages directly.
              </p>
              <Link href="/api-keys" className="block text-center text-xs font-bold bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 py-2.5 rounded-xl transition">
                View Connection Credentials ↗
              </Link>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
