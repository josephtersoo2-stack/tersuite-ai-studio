import { useState, useEffect } from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { useRouter } from 'next/router';
import Navbar from '../components/Navbar';
import {
  LayoutDashboard, User, Key, Layers, Bot, ShieldCheck, CheckCircle2,
  Copy, Check, RefreshCw, Cpu, Sparkles, Lock, Mail, Save, ExternalLink, Settings, Download, Trash2
} from 'lucide-react';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function UserDashboard() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState('projects');
  const [token, setToken] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(true);
  const [projects, setProjects] = useState([]);
  const [copiedToken, setCopiedToken] = useState(false);

  // Profile Edit State
  const [editUsername, setEditUsername] = useState('');
  const [editEmail, setEditEmail] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [saveSuccess, setSaveSuccess] = useState(false);

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token');
    const u = localStorage.getItem('tersuite_username') || 'Developer';
    const e = localStorage.getItem('tersuite_email') || 'dev@example.com';

    if (!t) {
      router.push('/login');
    } else {
      setToken(t);
      setUsername(u);
      setEmail(e);
      setEditUsername(u);
      setEditEmail(e);
      fetchProjects(t);
    }
  }, [router]);

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
    } catch (err) {
      console.error('Error fetching projects:', err);
    } finally {
      setLoading(false);
    }
  }

  async function handleDeleteProject(projectId) {
    if (!confirm('Are you sure you want to delete this generated plugin project? This action cannot be undone.')) return;
    try {
      const res = await fetch(API_BASE + `projects/${projectId}/`, {
        method: 'DELETE',
        headers: { 'Authorization': 'Token ' + token }
      });
      if (res.ok || res.status === 204 || res.status === 200) {
        setProjects(prev => prev.filter(p => p.id !== projectId));
      } else {
        alert('Failed to delete project.');
      }
    } catch (err) {
      console.error('Error deleting project:', err);
      alert('Connection error deleting project.');
    }
  }

  function handleSaveProfile(e) {
    e.preventDefault();
    localStorage.setItem('tersuite_username', editUsername);
    localStorage.setItem('tersuite_email', editEmail);
    setUsername(editUsername);
    setEmail(editEmail);
    setSaveSuccess(true);
    setTimeout(() => setSaveSuccess(false), 3000);
  }

  function copyToken() {
    navigator.clipboard.writeText(token);
    setCopiedToken(true);
    setTimeout(() => setCopiedToken(false), 2000);
  }

  if (loading && !token) {
    return (
      <div className="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center font-sans">
        <div className="text-sm text-slate-400 font-semibold flex items-center gap-2">
          <Bot className="w-5 h-5 text-indigo-400 animate-spin" /> Loading user dashboard...
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">
      <Head>
        <title>User Dashboard — Tersuite AI Studio</title>
      </Head>

      <Navbar />

      <main className="max-w-7xl mx-auto px-6 py-10">
        {/* User Account Header */}
        <div className="glass-card rounded-2xl p-6 mb-8 border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 p-0.5 shadow-lg shadow-indigo-500/20">
              <div className="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-xl font-black text-indigo-400">
                {username.substring(0, 2).toUpperCase()}
              </div>
            </div>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="text-2xl font-extrabold text-white">{username}</h1>
                <span className="px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                  Pro Studio Account
                </span>
              </div>
              <p className="text-xs text-slate-400 mt-1">{email} • Active Gemini 3.6 Agent Backend</p>
            </div>
          </div>

          {/* Navigation Tabs */}
          <div className="flex items-center gap-2 bg-slate-950/80 p-1.5 rounded-xl border border-slate-800/80">
            <button
              onClick={() => setActiveTab('projects')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition ${
                activeTab === 'projects' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'
              }`}
            >
              <Layers className="w-4 h-4" /> Plugin Generations History
            </button>
            <button
              onClick={() => setActiveTab('account')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition ${
                activeTab === 'account' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'
              }`}
            >
              <User className="w-4 h-4" /> Account Profile
            </button>
            <button
              onClick={() => setActiveTab('apikeys')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition ${
                activeTab === 'apikeys' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'
              }`}
            >
              <Key className="w-4 h-4" /> API Credentials
            </button>
          </div>
        </div>

        {/* TAB 1: PLUGIN GENERATIONS HISTORY */}
        {activeTab === 'projects' && (
          <div className="grid lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-6">
              {/* Plugin Generations Card */}
              <div className="glass-card rounded-2xl p-6 border border-slate-800">
                <div className="flex items-center justify-between mb-6">
                  <div>
                    <h2 className="text-lg font-bold text-white flex items-center gap-2">
                      <Layers className="w-5 h-5 text-cyan-400" /> WordPress Plugins Generated ({projects.length})
                    </h2>
                    <p className="text-xs text-slate-400 mt-0.5">History of plugins created via your connected WordPress Admin sites.</p>
                  </div>
                  <button
                    onClick={() => fetchProjects(token)}
                    className="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-lg"
                  >
                    <RefreshCw className="w-3.5 h-3.5" /> Refresh List
                  </button>
                </div>

                {loading ? (
                  <div className="py-12 text-center text-slate-500 text-sm flex items-center justify-center gap-2">
                    <RefreshCw className="w-4 h-4 animate-spin text-indigo-400" /> Syncing plugin history from Django backend...
                  </div>
                ) : projects.length === 0 ? (
                  <div className="py-12 text-center text-slate-500 border border-dashed border-slate-800 rounded-xl p-8">
                    <Bot className="w-10 h-10 mx-auto text-slate-600 mb-3" />
                    <p className="text-sm font-semibold text-slate-300 mb-1">No WordPress Plugins Generated Yet.</p>
                    <p className="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                      To create a plugin, install the <code className="text-indigo-400">tersostudio.zip</code> plugin in your WordPress Admin, connect your API key, and start a session with the AI Agent Coordinator!
                    </p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {projects.map((p, idx) => (
                      <div key={p.id || idx} className="bg-slate-950 border border-slate-800/80 rounded-xl p-5 hover:border-slate-700 transition">
                        <div className="flex items-start justify-between gap-4 mb-2">
                          <div>
                            <h3 className="font-bold text-white text-base">{p.name || 'Untitled Plugin'}</h3>
                            <span className="text-[11px] text-slate-500 font-mono">UUID: #{p.id}</span>
                          </div>
                          <div className="flex items-center gap-2">
                            <span className="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                              <CheckCircle2 className="w-3.5 h-3.5" /> {p.status ? p.status.toUpperCase() : 'COMPLETED'}
                            </span>
                            <button
                              onClick={() => handleDeleteProject(p.id)}
                              className="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 transition"
                              title="Delete Generated Plugin"
                            >
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </div>

                        <p className="text-xs text-slate-400 mb-4 line-clamp-2 leading-relaxed">{p.description || 'No description recorded.'}</p>

                        <div className="flex items-center justify-between text-xs text-slate-400 pt-3 border-t border-slate-900">
                          <span className="flex items-center gap-1">
                            <Sparkles className="w-3.5 h-3.5 text-cyan-400" />
                            Model: <strong className="text-slate-200">{p.metadata?.model || 'gemini-3.6-flash'}</strong>
                          </span>
                          <span className="text-slate-500">
                            Created: {p.created_at ? new Date(p.created_at).toLocaleDateString() : 'Recent'}
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Sidebar: How Generation Works */}
            <div className="space-y-6">
              <div className="glass-card rounded-2xl p-6 border border-slate-800">
                <h3 className="text-sm font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-2">
                  <Bot className="w-4 h-4 text-indigo-400" /> How Plugin Creation Works
                </h3>
                <ol className="space-y-3 text-xs text-slate-400 list-decimal list-inside leading-relaxed">
                  <li>Open your WordPress Admin (`wp-admin`).</li>
                  <li>Go to **TersoStudio → Command Center**.</li>
                  <li>Describe your plugin requirements to the AI Coordinator.</li>
                  <li>The 7-agent CrewAI backend generates, tests, and delivers the ready-to-install `.zip` plugin!</li>
                </ol>
              </div>

              <div className="glass-card rounded-2xl p-6 border border-slate-800">
                <h3 className="text-sm font-bold text-white mb-3">Download WordPress Client</h3>
                <p className="text-xs text-slate-400 mb-4 leading-relaxed">
                  Download <code className="text-cyan-400 font-mono">tersostudio.zip</code> to install on your WordPress sites.
                </p>
                <Link href="/api-keys" className="block text-center text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-xl transition shadow-md">
                  Get API Key & Download Package ↗
                </Link>
              </div>
            </div>
          </div>
        )}

        {/* TAB 2: ACCOUNT & PROFILE SETTINGS */}
        {activeTab === 'account' && (
          <div className="max-w-3xl mx-auto space-y-8">
            <div className="glass-card rounded-2xl p-8 border border-slate-800 shadow-2xl">
              <h2 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <Settings className="w-5 h-5 text-indigo-400" /> Account Profile Settings
              </h2>

              {saveSuccess && (
                <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold p-4 rounded-xl mb-6 flex items-center gap-2">
                  <CheckCircle2 className="w-4 h-4" /> Account profile updated successfully!
                </div>
              )}

              <form onSubmit={handleSaveProfile} className="space-y-6">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Username</label>
                  <div className="relative">
                    <input
                      type="text"
                      required
                      value={editUsername}
                      onChange={(e) => setEditUsername(e.target.value)}
                      className="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white rounded-xl pl-10 pr-4 py-3 text-sm outline-none transition"
                    />
                    <User className="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Email Address</label>
                  <div className="relative">
                    <input
                      type="email"
                      required
                      value={editEmail}
                      onChange={(e) => setEditEmail(e.target.value)}
                      className="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white rounded-xl pl-10 pr-4 py-3 text-sm outline-none transition"
                    />
                    <Mail className="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">New Password (Optional)</label>
                  <div className="relative">
                    <input
                      type="password"
                      value={newPassword}
                      onChange={(e) => setNewPassword(e.target.value)}
                      placeholder="Leave empty to keep current password"
                      className="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white rounded-xl pl-10 pr-4 py-3 text-sm outline-none transition"
                    />
                    <Lock className="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" />
                  </div>
                </div>

                <div className="pt-4 border-t border-slate-900 flex justify-end">
                  <button
                    type="submit"
                    className="bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white font-bold px-8 py-3 rounded-xl shadow-lg transition flex items-center gap-2 text-sm glow-btn"
                  >
                    <Save className="w-4 h-4" /> Save Profile Settings
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* TAB 3: API CREDENTIALS */}
        {activeTab === 'apikeys' && (
          <div className="max-w-3xl mx-auto space-y-8">
            <div className="glass-card rounded-2xl p-8 border border-slate-800 shadow-2xl">
              <h2 className="text-xl font-bold text-white mb-2 flex items-center gap-2">
                <Key className="w-5 h-5 text-indigo-400" /> WordPress Connection API Key
              </h2>
              <p className="text-xs text-slate-400 mb-6">Use this token inside your WordPress plugin settings page to connect WP Admin directly to the AI Backend.</p>

              <div className="flex items-center gap-3 bg-slate-950 border border-slate-800 rounded-xl p-4 font-mono text-sm text-slate-200 overflow-x-auto mb-6">
                <span className="flex-1 select-all">{token}</span>
                <button
                  onClick={copyToken}
                  className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-2.5 rounded-lg text-xs transition flex items-center gap-1.5 shrink-0"
                >
                  {copiedToken ? <Check className="w-4 h-4 text-emerald-300" /> : <Copy className="w-4 h-4" />}
                  {copiedToken ? 'Copied!' : 'Copy Token'}
                </button>
              </div>

              <div className="bg-slate-950/60 p-5 rounded-xl border border-slate-900 space-y-2 text-xs text-slate-300">
                <p><strong className="text-white">API URL:</strong> <code className="text-indigo-400">http://localhost:8000/api</code></p>
                <p><strong className="text-white">WebSocket Stream:</strong> <code className="text-cyan-400">ws://localhost:8000</code></p>
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
