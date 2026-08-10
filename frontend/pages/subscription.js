import { useState } from 'react';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/';

export default function Subscription() {
  const [selectedGateway, setSelectedGateway] = useState('paypal');

  async function subscribe(plan) {
    try {
      const res = await fetch(API_BASE + 'subscriptions/subscribe/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Token ' + (localStorage.getItem('tersuite_token') || '') },
        body: JSON.stringify({ gateway: selectedGateway, plan: plan })
      });
      const data = await res.json();
      alert('Subscription ' + plan + ' via ' + selectedGateway + ': ' + (data.message || 'Initiated'));
    } catch (e) {
      alert('Subscription error: ' + e.message);
    }
  }

  return (
    <div className="min-h-screen bg-slate-950 p-8">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-extrabold mb-8">Subscription & Credits</h1>
        <div className="grid md:grid-cols-2 gap-6 mb-10">
          <div className="bg-slate-900 rounded-xl p-6 border border-slate-800">
            <h2 className="text-xl font-bold mb-2">Starter</h2>
            <p className="text-3xl font-extrabold mb-4">$29/mo</p>
            <ul className="text-slate-300 mb-6 space-y-2">
              <li>• 10 plugin generations</li>
              <li>• Standard agent pipeline</li>
              <li>• Email support</li>
            </ul>
            <button onClick={() => subscribe('starter')} className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">Subscribe</button>
          </div>
          <div className="bg-slate-900 rounded-xl p-6 border border-green-600">
            <h2 className="text-xl font-bold mb-2">Pro</h2>
            <p className="text-3xl font-extrabold mb-4">$99/mo</p>
            <ul className="text-slate-300 mb-6 space-y-2">
              <li>• Unlimited generations</li>
              <li>• All sub-agents enabled</li>
              <li>• Priority sandbox testing</li>
              <li>• Custom templates</li>
            </ul>
            <button onClick={() => subscribe('pro')} className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">Subscribe</button>
          </div>
        </div>
        <div className="bg-slate-900 rounded-xl p-6 border border-slate-800">
          <h3 className="text-lg font-bold mb-4">Payment Methods</h3>
          <div className="flex gap-4">
            <label><input type="radio" name="gateway" value="paypal" checked onChange={e => setSelectedGateway(e.target.value)} /> PayPal</label>
            <label><input type="radio" name="gateway" value="flutterwave" onChange={e => setSelectedGateway(e.target.value)} /> Flutterwave</label>
            <label><input type="radio" name="gateway" value="monnify" onChange={e => setSelectedGateway(e.target.value)} /> Monnify</label>
          </div>
        </div>
      </div>
    </div>
  );
}
