import { useState, useEffect } from 'react';
import Head from 'next/head';
import Link from 'next/link';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function ApiKeys() {
  const [token, setToken] = useState('');
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token') || 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622';
    setToken(t);
  }, []);

  function copyToClipboard() {
    navigator.clipboard.writeText(token);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans">
      <Head>
        <title>API Keys & Integration — Tersuite AI Studio</title>
      </Head>

      <header className="border-b border-slate-800/80 bg-slate-900/50 backdrop-blur-xl">
        <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
          <Link href="/" className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-400 p-0.5">
              <div className="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center font-black text-indigo-400">⚡</div>
            </div>
            <span className="text-xl font-extrabold text-white">Tersuite AI Studio</span>
          </Link>
          <nav className="flex items-center gap-8 text-sm font-medium">
            <Link href="/" className="text-slate-400 hover:text-white">Dashboard</Link>
            <Link href="/api-keys" className="text-indigo-400 font-semibold">API Keys</Link>
            <Link href="/subscription" className="text-slate-400 hover:text-white">Subscriptions</Link>
          </nav>
        </div>
      </header>

      <main className="max-w-4xl mx-auto px-6 py-16">
        <h1 className="text-4xl font-extrabold text-white mb-2">WordPress Integration API Key</h1>
        <p className="text-slate-400 mb-10">Use this token inside your WordPress plugin settings page to connect WP Admin directly to the AI Backend.</p>

        <div className="bg-slate-900 border border-slate-800 rounded-2xl p-8 mb-10 shadow-2xl">
          <div className="flex items-center justify-between mb-4">
            <span className="text-xs font-bold uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-md border border-indigo-500/20">
              Active DRF Token
            </span>
            <span className="text-xs text-emerald-400 flex items-center gap-1.5 font-medium">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" /> Valid & Authorized
            </span>
          </div>

          <div className="flex items-center gap-3 bg-slate-950 border border-slate-800 rounded-xl p-4 font-mono text-sm text-slate-200 overflow-x-auto">
            <span className="flex-1">{token}</span>
            <button
              onClick={copyToClipboard}
              className="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg text-xs transition"
            >
              {copied ? '✓ Copied!' : 'Copy Token'}
            </button>
          </div>
        </div>

        <div className="bg-slate-900/50 border border-slate-800 rounded-2xl p-8">
          <h3 className="text-lg font-bold text-white mb-4">How to Connect to WordPress:</h3>
          <ol className="space-y-4 text-sm text-slate-300 list-decimal list-inside leading-relaxed">
            <li>Open your local WordPress site dashboard (`http://localhost/your-wp-site/wp-admin`).</li>
            <li>Go to **Plugins → Add New → Upload Plugin** and install `agentforge-plugin-generator.php` (found inside `wp-plugin/`).</li>
            <li>Navigate to **Tersuite AI Studio → Settings**.</li>
            <li>Paste **Backend API URL**: <code className="bg-slate-950 text-indigo-400 px-2 py-1 rounded">http://localhost:8000/api</code></li>
            <li>Paste **API Key**: <code className="bg-slate-950 text-indigo-400 px-2 py-1 rounded">{token}</code></li>
            <li>Click **Save Settings**. You can now create projects and generate plugins inside WP Admin!</li>
          </ol>
        </div>
      </main>
    </div>
  );
}
