import { useState, useEffect } from 'react';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function APIKeys() {
  const [keys, setKeys] = useState([]);

  useEffect(() => {
    async function load() {
      try {
        const res = await fetch(API_BASE + 'projects/', {
          headers: { 'Authorization': 'Token ' + (localStorage.getItem('tersuite_token') || '') }
        });
        const data = await res.json();
        setKeys(data.results || data || []);
      } catch (e) {
        console.error('Failed to load keys/projects:', e);
      }
    }
    load();
  }, []);

  async function generateKey() {
    try {
      const res = await fetch(API_BASE + 'projects/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Token ' + (localStorage.getItem('tersuite_token') || '') },
        body: JSON.stringify({ name: 'Default Key' })
      });
      const data = await res.json();
      alert('New API key/project created! (In production: creates key via Django backend)');
    } catch (e) {
      alert('Failed: ' + e.message);
    }
  }

  return (
    <div className="min-h-screen bg-slate-950 p-8">
      <div className="max-w-2xl mx-auto">
        <h1 className="text-3xl font-extrabold mb-2">API Keys</h1>
        <p className="text-slate-400 mb-6">Generate and manage API keys to connect your WordPress plugin to the backend.</p>
        <button onClick={generateKey} className="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg mb-6">Generate New Key</button>
        <table className="w-full bg-slate-900 rounded-xl overflow-hidden border border-slate-800">
          <thead className="bg-slate-800 text-slate-300 text-sm uppercase tracking-wider">
            <tr><th className="p-4 text-left">Key Name</th><th className="p-4 text-left">Status</th><th className="p-4 text-left">Created</th></tr>
          </thead>
          <tbody>
            {keys.map((k, i) => (
              <tr key={i} className="border-t border-slate-800"><td className="p-4">{k.name || 'Default Key'}</td><td className="p-4">Active</td><td className="p-4">2026-08-09</td></tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
