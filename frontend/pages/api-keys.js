import { useState, useEffect } from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { useRouter } from 'next/router';
import Navbar from '../components/Navbar';
import { Key, Copy, Check, ShieldCheck, Download, Bot } from 'lucide-react';

export default function ApiKeys() {
  const router = useRouter();
  const [token, setToken] = useState('');
  const [copied, setCopied] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const t = localStorage.getItem('tersuite_token');
    if (!t) {
      router.push('/login');
    } else {
      setToken(t);
      setLoading(false);
    }
  }, [router]);

  function copyToClipboard() {
    navigator.clipboard.writeText(token);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center font-sans">
        <div className="text-sm text-slate-400 font-semibold flex items-center gap-2">
          <Bot className="w-5 h-5 text-indigo-400 animate-spin" /> Verifying authentication session...
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">
      <Head>
        <title>API Keys & Integration — Tersuite AI Studio</title>
      </Head>

      <Navbar />

      <main className="max-w-4xl mx-auto px-6 py-12">
        <div className="flex items-center gap-3 mb-2">
          <div className="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
            <Key className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-3xl font-extrabold text-white">WordPress Integration API Key</h1>
            <p className="text-xs text-slate-400">Protected Account Credentials</p>
          </div>
        </div>

        <p className="text-sm text-slate-400 mb-8 leading-relaxed">
          Use this authenticated token inside your WordPress plugin settings page to connect WP Admin directly to the Tersuite AI Studio backend.
        </p>

        <div className="glass-card rounded-2xl p-8 mb-8 border border-slate-800 shadow-2xl">
          <div className="flex items-center justify-between mb-4">
            <span className="text-xs font-bold uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-md border border-indigo-500/20">
              Active User DRF Token
            </span>
            <span className="text-xs text-emerald-400 flex items-center gap-1.5 font-semibold">
              <ShieldCheck className="w-4 h-4 text-emerald-400" /> Authenticated & Active
            </span>
          </div>

          <div className="flex items-center gap-3 bg-slate-950 border border-slate-800 rounded-xl p-4 font-mono text-sm text-slate-200 overflow-x-auto">
            <span className="flex-1 select-all">{token}</span>
            <button
              onClick={copyToClipboard}
              className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-2.5 rounded-lg text-xs transition flex items-center gap-1.5 shrink-0"
            >
              {copied ? <Check className="w-4 h-4 text-emerald-300" /> : <Copy className="w-4 h-4" />}
              {copied ? 'Copied!' : 'Copy Token'}
            </button>
          </div>
        </div>

        <div className="glass-card rounded-2xl p-8 border border-slate-800">
          <h3 className="text-base font-bold text-white mb-4 flex items-center gap-2">
            <Download className="w-5 h-5 text-cyan-400" /> How to Connect to WordPress:
          </h3>
          <ol className="space-y-4 text-sm text-slate-300 list-decimal list-inside leading-relaxed">
            <li>Open your WordPress site dashboard (`http://localhost/your-wp-site/wp-admin`).</li>
            <li>Go to **Plugins → Add New → Upload Plugin** and install `agentforge-plugin-generator.php` (located inside <code className="bg-slate-950 text-cyan-400 px-2 py-0.5 rounded text-xs font-mono">wp-plugin/</code>).</li>
            <li>Navigate to **Tersuite AI Studio → Settings**.</li>
            <li>Paste **Backend API URL**: <code className="bg-slate-950 text-indigo-400 px-2 py-0.5 rounded text-xs font-mono">http://localhost:8000/api</code></li>
            <li>Paste **API Key**: <code className="bg-slate-950 text-indigo-400 px-2 py-0.5 rounded text-xs font-mono">{token}</code></li>
            <li>Click **Save Settings**. Your WP Admin is now connected to your AI Studio backend!</li>
          </ol>
        </div>
      </main>
    </div>
  );
}
