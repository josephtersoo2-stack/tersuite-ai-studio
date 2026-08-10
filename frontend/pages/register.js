import { useState } from 'react';
import { useRouter } from 'next/router';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export default function Register() {
  const [name,setName]=useState(''); const [email,setEmail]=useState(''); const [password,setPassword]=useState(''); const [error,setError]=useState(''); const router=useRouter();
  async function handleSubmit(e){ e.preventDefault(); setError(''); const res=await fetch(`${API_BASE}/auth/register/`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:email,email,password})}); const data=await res.json(); if(!res.ok||!data.token){setError(data.error||'Registration failed');return;} localStorage.setItem('tersuite_token',data.token); router.push('/'); }
  return <div className="min-h-screen bg-slate-950 flex items-center justify-center p-8"><form onSubmit={handleSubmit} className="bg-slate-900 rounded-2xl p-8 w-full max-w-md border border-slate-800"><h2 className="text-2xl font-bold mb-6">Create Account</h2>{error&&<p className="mb-4 text-red-400">{error}</p>}<input type="text" placeholder="Full Name" value={name} onChange={e=>setName(e.target.value)} className="w-full p-3 mb-4 bg-slate-800 rounded-lg border border-slate-700 text-white" /><input type="email" placeholder="Email" value={email} onChange={e=>setEmail(e.target.value)} className="w-full p-3 mb-4 bg-slate-800 rounded-lg border border-slate-700 text-white" required /><input type="password" placeholder="Password" value={password} onChange={e=>setPassword(e.target.value)} className="w-full p-3 mb-6 bg-slate-800 rounded-lg border border-slate-700 text-white" minLength={8} required /><button className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">Register</button></form></div>;
}
