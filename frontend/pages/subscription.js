import { useState } from 'react';
import Head from 'next/head';
import Link from 'next/link';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function Subscription() {
  const [selectedGateway, setSelectedGateway] = useState('paypal');
  const [loading, setLoading] = useState(false);

  async function handleSubscribe(plan) {
    setLoading(true);
    try {
      const token = localStorage.getItem('tersuite_token') || 'ec33c4db14d5bffcc6d3c8c0e81595e3bd020622';
      const res = await fetch(API_BASE + 'subscriptions/subscribe/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Token ' + token
        },
        body: JSON.stringify({ gateway: selectedGateway, plan: plan })
      });
      const data = await res.json();
      alert(`Subscription ${plan.toUpperCase()} via ${selectedGateway.toUpperCase()}: ${data.message || 'Initiated successfully!'}`);
    } catch (e) {
      alert('Subscription error: ' + e.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans">
      <Head>
        <title>Subscriptions & Billing — Tersuite AI Studio</title>
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
            <Link href="/api-keys" className="text-slate-400 hover:text-white">API Keys</Link>
            <Link href="/subscription" className="text-indigo-400 font-semibold">Subscriptions</Link>
          </nav>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-6 py-16">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h1 className="text-4xl font-extrabold text-white mb-4">Flexible AI Generation Plans</h1>
          <p className="text-slate-400 text-base">Select your plan to unlock Gemini 3.6 Flash & 3.1 Pro agent pipelines, MicroVM sandboxing, and live streaming.</p>
        </div>

        {/* Payment Gateway Selector */}
        <div className="max-w-xl mx-auto mb-12 bg-slate-900 border border-slate-800 rounded-2xl p-6">
          <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 text-center">Select Preferred Payment Gateway</label>
          <div className="grid grid-cols-3 gap-4">
            {[
              { id: 'paypal', name: 'PayPal', icon: '💳' },
              { id: 'flutterwave', name: 'Flutterwave', icon: '🌍' },
              { id: 'monnify', name: 'Monnify', icon: '⚡' }
            ].map(gw => (
              <button
                key={gw.id}
                onClick={() => setSelectedGateway(gw.id)}
                className={`flex flex-col items-center justify-center p-4 rounded-xl border text-sm font-bold transition ${
                  selectedGateway === gw.id
                    ? 'border-indigo-500 bg-indigo-500/10 text-white shadow-lg shadow-indigo-500/10'
                    : 'border-slate-800 bg-slate-950/50 text-slate-400 hover:text-slate-200 hover:border-slate-700'
                }`}
              >
                <span className="text-2xl mb-1">{gw.icon}</span>
                {gw.name}
              </button>
            ))}
          </div>
        </div>

        {/* Pricing Cards */}
        <div className="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          {/* Starter Plan */}
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-8 flex flex-col justify-between">
            <div>
              <span className="text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-800 px-3 py-1 rounded-md">Starter Developer</span>
              <div className="my-6">
                <span className="text-5xl font-extrabold text-white">$29</span>
                <span className="text-slate-400 font-medium ml-2">/ month</span>
              </div>
              <ul className="space-y-4 text-sm text-slate-300 mb-8">
                <li className="flex items-center gap-3"><span className="text-emerald-400">✓</span> 15 Complete WordPress Plugin Generations</li>
                <li className="flex items-center gap-3"><span className="text-emerald-400">✓</span> Gemini 3.6 Flash Agent Pipeline</li>
                <li className="flex items-center gap-3"><span className="text-emerald-400">✓</span> Standard MicroVM Security Testing</li>
                <li className="flex items-center gap-3"><span className="text-emerald-400">✓</span> Email & Community Support</li>
              </ul>
            </div>
            <button
              onClick={() => handleSubscribe('starter')}
              disabled={loading}
              className="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-4 rounded-xl transition border border-slate-700"
            >
              Subscribe via {selectedGateway.toUpperCase()}
            </button>
          </div>

          {/* Pro Plan */}
          <div className="bg-slate-900 border-2 border-indigo-500 rounded-2xl p-8 flex flex-col justify-between relative shadow-2xl shadow-indigo-500/10">
            <div className="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-500 to-cyan-500 text-white text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wider shadow-md">
              Most Popular
            </div>
            <div>
              <span className="text-xs font-bold uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-md">Pro Studio</span>
              <div className="my-6">
                <span className="text-5xl font-extrabold text-white">$99</span>
                <span className="text-slate-400 font-medium ml-2">/ month</span>
              </div>
              <ul className="space-y-4 text-sm text-slate-300 mb-8">
                <li className="flex items-center gap-3"><span className="text-indigo-400">✓</span> Unlimited Plugin & Theme Generations</li>
                <li className="flex items-center gap-3"><span className="text-indigo-400">✓</span> All 7 Sub-Agents Enabled (Gemini 3.1 Pro & 3.6)</li>
                <li className="flex items-center gap-3"><span className="text-indigo-400">✓</span> Firecracker MicroVM Isolation Testing</li>
                <li className="flex items-center gap-3"><span className="text-indigo-400">✓</span> Custom Security Boilerplates & Nonces</li>
                <li className="flex items-center gap-3"><span className="text-indigo-400">✓</span> Priority WebSocket Streaming</li>
              </ul>
            </div>
            <button
              onClick={() => handleSubscribe('pro')}
              disabled={loading}
              className="w-full bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-indigo-500/25"
            >
              Subscribe via {selectedGateway.toUpperCase()}
            </button>
          </div>
        </div>
      </main>
    </div>
  );
}
