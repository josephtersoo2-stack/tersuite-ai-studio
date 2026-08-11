import { useState, useEffect } from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { useRouter } from 'next/router';
import Navbar from '../components/Navbar';
import {
  LayoutDashboard, User, Key, Layers, Plus, Bot, ShieldCheck, CheckCircle2,
  Copy, Check, RefreshCw, Cpu, Sparkles, Lock, Mail, Save, LogOut, Settings, CreditCard
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
  const [creating, setCreating] = useState(false);
  const [copiedToken, setCopiedToken] = useState(false);

  // Profile Edit State
  const [editUsername, setEditUsername] = useState('');
  const [editEmail, setEditEmail] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [saveSuccess, setSaveSuccess] = useState(false);

  // Project Form State
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [selectedModel, setSelectedModel] = useState('gemini-3.6-flash');

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
        alert('Project created successfully! Agents dispatched.');
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
          <Bot className="w-5 h-5 text-indigo-400 animate-spin" /> Verifying user dashboard...
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">
      <Head>
        <title>User Account & Studio Dashboard — Tersuite AI Studio</title>
      </Head>

      <Navbar />

      <main className="max-w-7xl mx-auto px-6 py-10">
        {/* User Account Banner */}
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
              <p className="text-xs text-slate-400 mt-1">{email} • Active Gemini 3.6 Agent Quota</p>
            </div>
          </div>

          {/* Tab Navigation */}
          <div className="flex items-center gap-2 bg-slate-950/80 p-1.5 rounded-xl border border-slate-800/80">
            <button
              onClick={() => setActiveTab('projects')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition ${
                activeTab === 'projects' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'
              }`}
            >
              <Layers className="w-4 h-4" /> Plugin Projects
            </button>
            <button
              onClick={() => setActiveTab('account')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition ${
                activeTab === 'account' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'
              }`}
            >
              <User className="w-4 h-4" /> Account Settings
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

        {/* TAB 1: PLUGIN PROJECTS & AGENT GENERATOR */}
        {activeTab === 'projects' && (
          <div className="grid lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-8">
              {/* Create Project Form */}
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
                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Plugin Requirements & Description</label>
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
                      className="w-full sm:w-auto bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-indigo-500/20 transition flex items-center justify-center gap-2 text-sm glow-btn"
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

            {/* Sidebar */}
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
            </div>
          </div>
        )}

        {/* TAB 2: ACCOUNT & PROFILE SETTINGS */}
        {activeTab === 'account' && (
          <div className="max-w-3xl mx-auto space-y-8">
            <div className="glass-card rounded-2xl p-8 border border-slate-800 shadow-2xl">
              <h2 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <Settings className="w-5 h-5 text-indigo-400" /> Account Profile & Credentials
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
