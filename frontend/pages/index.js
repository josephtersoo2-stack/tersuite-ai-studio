import Head from 'next/head';
import Link from 'next/link';
import Navbar from '../components/Navbar';
import { Bot, Sparkles, ShieldCheck, Zap, Code2, Cpu, CheckCircle2, ArrowRight, Download, Terminal, Layers, Key } from 'lucide-react';

export default function Home() {
  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">
      <Head>
        <title>Tersuite AI Studio — Multi-Agent WordPress Plugin Engine</title>
        <meta name="description" content="Build security-hardened WordPress plugins with Gemini 3.6 autonomous AI agent teams." />
      </Head>

      <Navbar />

      {/* Hero Section */}
      <section className="relative pt-20 pb-24 overflow-hidden glow-gradient">
        <div className="max-w-7xl mx-auto px-6 text-center relative z-10">
          <div className="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-bold text-indigo-400 mb-8 backdrop-blur-md">
            <Sparkles className="w-4 h-4 text-cyan-400 animate-spin" />
            <span>Powered by Gemini 3.6 Flash & Gemini 3.1 Pro AI Agents</span>
          </div>

          <h1 className="text-5xl md:text-7xl font-black tracking-tight text-white mb-8 leading-tight">
            Build WordPress Plugins <br className="hidden sm:inline" />
            <span className="bg-gradient-to-r from-indigo-400 via-cyan-400 to-emerald-400 bg-clip-text text-transparent">
              With Autonomous AI Agent Teams
            </span>
          </h1>

          <p className="text-lg md:text-xl text-slate-400 max-w-3xl mx-auto mb-10 leading-relaxed font-normal">
            Tersuite AI Studio deploys 7 specialized CrewAI workers (Coordinator, UI/UX, Frontend, Backend, Security, Reviewer, Sandbox) to write, test, and package production-ready WordPress plugins in real-time.
          </p>

          <div className="flex flex-wrap items-center justify-center gap-4">
            <Link
              href="/register"
              className="flex items-center gap-2 bg-gradient-to-r from-indigo-500 via-indigo-600 to-cyan-500 hover:from-indigo-600 hover:to-cyan-600 text-white font-extrabold px-8 py-4 rounded-xl shadow-xl shadow-indigo-500/25 transition text-base glow-btn"
            >
              Get Started Free <ArrowRight className="w-5 h-5" />
            </Link>
            <Link
              href="/api-keys"
              className="flex items-center gap-2 bg-slate-900/80 hover:bg-slate-900 text-slate-200 font-bold border border-slate-800 px-8 py-4 rounded-xl backdrop-blur-md transition text-base"
            >
              <Key className="w-5 h-5 text-cyan-400" /> Get API Connection Key
            </Link>
          </div>
        </div>
      </section>

      {/* 7 AI Agents Pipeline Grid */}
      <section className="py-24 max-w-7xl mx-auto px-6">
        <div className="text-center max-w-3xl mx-auto mb-16">
          <h2 className="text-3xl md:text-4xl font-extrabold text-white mb-4">
            7 Autonomous Specialists in One Pipeline
          </h2>
          <p className="text-slate-400 text-base">
            No single prompt can build a complete plugin. Our multi-agent orchestrator divides requirements across specialized sub-agents.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {[
            {
              title: "Coordinator Agent",
              role: "Interactive Planning",
              desc: "Analyzes user specifications, creates task breakdown trees, and manages agent handoffs.",
              icon: Bot,
              color: "text-indigo-400 border-indigo-500/30 bg-indigo-500/10"
            },
            {
              title: "UI / UX Sub-Agent",
              role: "WP Admin Screens",
              desc: "Designs clean WordPress administrative interfaces, settings pages, and tabbed dashboards.",
              icon: Layers,
              color: "text-cyan-400 border-cyan-500/30 bg-cyan-500/10"
            },
            {
              title: "Backend Specialist",
              role: "PHP & WP Hooks",
              desc: "Writes modern, OOP-compliant PHP modules, custom post types, and REST API controllers.",
              icon: Code2,
              color: "text-emerald-400 border-emerald-500/30 bg-emerald-500/10"
            },
            {
              title: "Security Auditor",
              role: "Zero-Trust Enforcement",
              desc: "Hardens code with ABSPATH guards, WP nonces, capability checks, and prepared SQL statements.",
              icon: ShieldCheck,
              color: "text-amber-400 border-amber-500/30 bg-amber-500/10"
            },
            {
              title: "Frontend Developer",
              role: "JS & AJAX Logic",
              desc: "Builds dynamic JavaScript UI components, AJAX form submitters, and responsive styling.",
              icon: Zap,
              color: "text-blue-400 border-blue-500/30 bg-blue-500/10"
            },
            {
              title: "Code Reviewer",
              role: "AST Verification",
              desc: "Parses generated code structures, checks for syntax errors, and validates coding conventions.",
              icon: Terminal,
              color: "text-purple-400 border-purple-500/30 bg-purple-500/10"
            },
            {
              title: "Sandbox Tester",
              role: "MicroVM Isolation",
              desc: "Executes unit tests in isolated sandboxes, verifies compatibility, and zips the final plugin.",
              icon: Cpu,
              color: "text-rose-400 border-rose-500/30 bg-rose-500/10"
            }
          ].map((agent, i) => {
            const IconComponent = agent.icon;
            return (
              <div key={i} className="glass-card glass-card-hover rounded-2xl p-6 relative">
                <div className={`w-12 h-12 rounded-xl border ${agent.color} flex items-center justify-center mb-5`}>
                  <IconComponent className="w-6 h-6" />
                </div>
                <h3 className="text-lg font-bold text-white mb-1">{agent.title}</h3>
                <span className="inline-block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">{agent.role}</span>
                <p className="text-sm text-slate-400 leading-relaxed">{agent.desc}</p>
              </div>
            );
          })}
        </div>
      </section>

      {/* WordPress Integration Highlight */}
      <section className="py-20 bg-slate-900/50 border-y border-slate-800">
        <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-bold text-emerald-400 mb-6">
              <CheckCircle2 className="w-3.5 h-3.5" /> Direct WP Admin Integration
            </div>
            <h2 className="text-3xl md:text-4xl font-extrabold text-white mb-6 leading-tight">
              Build & Install Plugins Without Leaving WordPress
            </h2>
            <p className="text-slate-400 text-base mb-8 leading-relaxed">
              Install the native Tersuite WordPress plugin (`agentforge-plugin-generator.php`) into your WP Admin dashboard. Connect via your API Token to create, chat, test, and install plugins directly on your website.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <Link href="/api-keys" className="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-emerald-600/20 transition">
                <Download className="w-4 h-4" /> Download WP Plugin
              </Link>
              <Link href="/subscription" className="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold px-6 py-3.5 rounded-xl border border-slate-700 transition">
                View Pricing Plans
              </Link>
            </div>
          </div>

          <div className="glass-card rounded-2xl p-6 border border-slate-800 shadow-2xl">
            <div className="flex items-center justify-between border-b border-slate-800 pb-4 mb-4">
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 rounded-full bg-rose-500" />
                <span className="w-3 h-3 rounded-full bg-amber-500" />
                <span className="w-3 h-3 rounded-full bg-emerald-500" />
              </div>
              <span className="text-xs font-mono text-slate-400">WP Admin — Tersuite Studio IDE</span>
            </div>
            <div className="font-mono text-xs text-slate-300 space-y-3 bg-slate-950 p-4 rounded-xl border border-slate-900">
              <div className="text-indigo-400">[02:01:45] Agent Coordinator: Initializing plugin project spec...</div>
              <div className="text-cyan-400">[02:01:47] UI Agent: Generated tabbed admin menu layout</div>
              <div className="text-emerald-400">[02:01:50] Backend Agent: Applied ABSPATH & Nonces security</div>
              <div className="text-amber-400">[02:01:53] Reviewer Agent: Passed AST verification (0 errors)</div>
              <div className="text-rose-400">[02:01:56] Sandbox Agent: Zipped plugin. Ready to install!</div>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12 border-t border-slate-900 text-center text-sm text-slate-500">
        <p>Tersuite AI Studio © 2026 — Multi-Agent Autonomous Plugin Platform</p>
      </footer>
    </div>
  );
}
