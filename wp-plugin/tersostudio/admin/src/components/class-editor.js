const el = wp.element.createElement;
const useState = wp.element.useState;
const useEffect = wp.element.useEffect;
const useRef = wp.element.useRef;

export function WorkspaceMainLayoutShell() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '/wp-json/tersostudio/v2';
    const nonce = S.nonce || '';

    const urlParams = new URLSearchParams(window.location.search);
    const initialProjectId = S.projectId || urlParams.get('project_id') || '';

    const [projects, setProjects] = useState([]);
    const [selectedProjectId, setSelectedProjectId] = useState(initialProjectId);
    const [activeProject, setActiveProject] = useState(null);

    const [chatHistory, setChatHistory] = useState([
        { role: 'ai', text: '👋 Welcome to TersoStudio AI Architect IDE! Select a project and type a prompt below to generate WordPress plugin code, shortcodes, admin pages, or hooks.', time: new Date().toLocaleTimeString() }
    ]);
    const [inputPrompt, setInputPrompt] = useState('');
    const [isGenerating, setIsGenerating] = useState(false);
    const [statusMessage, setStatusMessage] = useState('');

    const [files, setFiles] = useState({});
    const [activeFileName, setActiveFileName] = useState('');
    const [editorCode, setEditorCode] = useState('');
    const [terminalLogs, setTerminalLogs] = useState([]);

    // LLM Test Modal State
    const [showLlmModal, setShowLlmModal] = useState(false);
    const [llmTestData, setLlmTestData] = useState(null);
    const [isTestingLlm, setIsTestingLlm] = useState(false);

    const chatEndRef = useRef(null);
    const terminalEndRef = useRef(null);

    function addLog(msg) {
        const time = new Date().toLocaleTimeString();
        setTerminalLogs(prev => [...prev, `[${time}] ${msg}`]);
    }

    // Scroll helpers
    useEffect(() => {
        if (chatEndRef.current) chatEndRef.current.scrollIntoView({ behavior: 'smooth' });
    }, [chatHistory]);

    useEffect(() => {
        if (terminalEndRef.current) terminalEndRef.current.scrollIntoView({ behavior: 'smooth' });
    }, [terminalLogs]);

    // 1. Fetch available projects
    useEffect(() => {
        addLog('Workspace IDE initialized. Loading project registry...');
        fetch(restUrl + '/projects', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json())
            .then(data => {
                const list = data.results || data.projects || data || [];
                setProjects(list);
                if (list.length > 0) {
                    if (!selectedProjectId) {
                        setSelectedProjectId(list[0].id);
                        setActiveProject(list[0]);
                    } else {
                        const found = list.find(p => p.id === selectedProjectId);
                        if (found) setActiveProject(found);
                    }
                }
            })
            .catch(err => addLog(`Error loading projects: ${err.message}`));
    }, []);

    // 2. Load project details & generated files
    useEffect(() => {
        if (!selectedProjectId) return;
        addLog(`Syncing workspace context for project #${selectedProjectId.substring(0, 8)}...`);

        fetch(restUrl + '/chat/deliver/' + selectedProjectId, { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json())
            .then(data => {
                if (data.files && Object.keys(data.files).length > 0) {
                    setFiles(data.files);
                    const firstFile = Object.keys(data.files)[0];
                    setActiveFileName(firstFile);
                    setEditorCode(data.files[firstFile]);
                    addLog(`Loaded ${Object.keys(data.files).length} generated plugin file(s) into workspace.`);
                } else {
                    setFiles({});
                    setActiveFileName('');
                    setEditorCode('// Workspace ready. Instruct AI Architect to generate plugin files.');
                }
            })
            .catch(() => {
                setFiles({});
                setActiveFileName('');
                setEditorCode('// Workspace ready for code generation.');
            });
    }, [selectedProjectId]);

    // Switch active project
    function handleProjectChange(e) {
        const newId = e.target.value;
        setSelectedProjectId(newId);
        const found = projects.find(p => p.id === newId);
        if (found) setActiveProject(found);
        window.history.replaceState({}, '', `?page=tersostudio-workbench&project_id=${newId}`);
    }

    // Select file in explorer
    function handleSelectFile(fileName) {
        setActiveFileName(fileName);
        setEditorCode(files[fileName] || '');
        addLog(`Opened file in editor: ${fileName}`);
    }

    // Submit prompt to start AI generation pipeline
    function handleSendPrompt(e) {
        if (e) e.preventDefault();
        const promptText = inputPrompt.trim();
        if (!promptText) return;
        if (!selectedProjectId) {
            alert('Please select or create a project first.');
            return;
        }

        setIsGenerating(true);
        setStatusMessage('Transmitting request to AI Architect multi-agent pipeline...');
        addLog(`[USER PROMPT]: "${promptText}"`);

        const userMsg = { role: 'user', text: promptText, time: new Date().toLocaleTimeString() };
        setChatHistory(prev => [...prev, userMsg]);
        setInputPrompt('');

        fetch(restUrl + '/chat', {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify({ project_id: selectedProjectId, message: promptText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'started' || data.status === 'running' || data.success) {
                addLog('AI Agent pipeline engaged! Generating plugin architecture and code...');
                const aiMsg = { role: 'ai', text: '🤖 I am analyzing your request and generating the plugin architecture & code. Watch the telemetry log below for step-by-step progress!', time: new Date().toLocaleTimeString() };
                setChatHistory(prev => [...prev, aiMsg]);
                pollProgress(selectedProjectId);
            } else {
                setIsGenerating(false);
                setStatusMessage('');
                const aiMsg = { role: 'ai', text: `Response: ${data.message || JSON.stringify(data)}`, time: new Date().toLocaleTimeString() };
                setChatHistory(prev => [...prev, aiMsg]);
            }
        })
        .catch(err => {
            setIsGenerating(false);
            setStatusMessage('');
            addLog(`Error triggering AI pipeline: ${err.message}`);
        });
    }

    // Poll pipeline status
    function pollProgress(pId) {
        const interval = setInterval(() => {
            fetch(restUrl + '/chat/stream/' + pId, { headers: { 'X-WP-Nonce': nonce } })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(interval);
                        setIsGenerating(false);
                        setStatusMessage('');
                        addLog('✅ AI Agent pipeline completed plugin generation successfully!');

                        // Fetch generated files
                        fetch(restUrl + '/chat/deliver/' + pId, { headers: { 'X-WP-Nonce': nonce } })
                            .then(r => r.json())
                            .then(deliv => {
                                if (deliv.files && Object.keys(deliv.files).length > 0) {
                                    setFiles(deliv.files);
                                    const firstFile = Object.keys(deliv.files)[0];
                                    setActiveFileName(firstFile);
                                    setEditorCode(deliv.files[firstFile]);
                                    addLog(`Workspace updated with ${Object.keys(deliv.files).length} file(s).`);
                                }
                            });

                        const aiMsg = { role: 'ai', text: '🎉 Plugin code generated and loaded into Workspace Editor!', time: new Date().toLocaleTimeString() };
                        setChatHistory(prev => [...prev, aiMsg]);
                    } else if (data.status === 'failed') {
                        clearInterval(interval);
                        setIsGenerating(false);
                        setStatusMessage('');
                        addLog(`❌ Pipeline Error: ${data.error || 'Failed to complete.'}`);
                        const aiMsg = { role: 'ai', text: `Error: ${data.error || 'Agent generation failed.'}`, time: new Date().toLocaleTimeString() };
                        setChatHistory(prev => [...prev, aiMsg]);
                    } else {
                        addLog(`[PIPELINE STATUS]: ${data.status || 'building'}...`);
                    }
                })
                .catch(() => clearInterval(interval));
        }, 3000);
    }

    // Test LLM Connection
    function runLlmTest() {
        setIsTestingLlm(true);
        setShowLlmModal(true);
        setLlmTestData(null);

        fetch(restUrl + '/settings/test-llm', {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce }
        })
        .then(res => res.json())
        .then(data => {
            setIsTestingLlm(false);
            setLlmTestData(data);
            addLog(`LLM Test Result: ${data.status || 'Done'} (${data.latency_ms} ms)`);
        })
        .catch(err => {
            setIsTestingLlm(false);
            setLlmTestData({ status: 'error', message: err.message });
            addLog(`LLM Test Error: ${err.message}`);
        });
    }

    const promptPills = [
        '➕ Add WooCommerce Custom Checkout Fields',
        '🔒 Add 2FA & Hardened Security Nonces',
        '⚙️ Build Admin Settings Page & Tab Bar',
        '⚡ Add Custom REST API Endpoint'
    ];

    return el('div', { style: { display: 'flex', flexDirection: 'column', height: 'calc(100vh - 40px)', background: '#0b0f19', color: '#f1f5f9', fontFamily: 'Inter, system-ui, sans-serif' } },
        
        // 1. TOP HEADER TOOLBAR
        el('div', { style: { height: '52px', background: '#0f172a', borderBottom: '1px solid #1e293b', display: 'flex', alignItems: 'center', padding: '0 18px', justifyContent: 'space-between', boxSizing: 'border-box' } },
            el('div', { style: { display: 'flex', alignItems: 'center', gap: '16px' } },
                el('span', { style: { fontWeight: '800', fontSize: '16px', background: 'linear-gradient(135deg, #818cf8, #c084fc)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', display: 'flex', alignItems: 'center', gap: '8px' } }, '⚡ TersoStudio AI Architect IDE'),
                el('select', { value: selectedProjectId, onChange: handleProjectChange, style: { background: '#020617', color: '#38bdf8', border: '1px solid #334155', borderRadius: '8px', padding: '6px 14px', fontSize: '13px', fontWeight: '600', outline: 'none', cursor: 'pointer' } },
                    projects.length === 0 ? el('option', { value: '' }, 'Loading projects...') : null,
                    projects.map(p => el('option', { key: p.id, value: p.id }, `📁 ${p.name} (${p.category?.name || 'General'})`))
                )
            ),
            el('div', { style: { display: 'flex', alignItems: 'center', gap: '12px' } },
                el('button', { onClick: runLlmTest, style: { background: '#6366f1', color: '#fff', border: 'none', padding: '6px 14px', borderRadius: '8px', fontSize: '12px', fontWeight: 'bold', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '6px' } }, '🧪 Test LLM Connection'),
                el('span', { style: { background: isGenerating ? 'rgba(245,158,11,0.15)' : 'rgba(16,185,129,0.15)', color: isGenerating ? '#f59e0b' : '#10b981', border: `1px solid ${isGenerating ? '#f59e0b' : '#10b981'}`, padding: '4px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: 'bold' } }, isGenerating ? '⚡ AI Swarm Building...' : '🟢 System Ready')
            )
        ),

        // 2. MAIN WORKSPACE VIEWPORT
        el('div', { style: { display: 'flex', flex: 1, overflow: 'hidden' } },
            
            // LEFT PANEL: AI ARCHITECT CHAT
            el('div', { style: { width: '450px', background: '#0f172a', borderRight: '1px solid #1e293b', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { padding: '12px 16px', background: '#1e293b', borderBottom: '1px solid #334155', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                    el('span', { style: { fontWeight: '700', fontSize: '13px', color: '#f8fafc', display: 'flex', alignItems: 'center', gap: '8px' } }, '🤖 AI Architect Assistant'),
                    el('span', { style: { fontSize: '11px', color: '#818cf8', background: 'rgba(99,102,241,0.15)', padding: '2px 8px', borderRadius: '4px', fontWeight: '600' } }, 'Claude / Swarm')
                ),
                
                // Chat message timeline
                el('div', { style: { flex: 1, padding: '16px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '14px', background: '#020617' } },
                    chatHistory.map((m, i) => el('div', { key: i, style: { background: m.role === 'user' ? '#1e1b4b' : '#0f172a', border: `1px solid ${m.role === 'user' ? '#4338ca' : '#1e293b'}`, padding: '14px', borderRadius: '10px', fontSize: '13px', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' } },
                        el('div', { style: { display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '11px', fontWeight: 'bold' } },
                            el('span', { style: { color: m.role === 'user' ? '#a5b4fc' : '#38bdf8' } }, m.role === 'user' ? '👤 USER' : '🤖 AI ARCHITECT'),
                            el('span', { style: { color: '#64748b' } }, m.time)
                        ),
                        el('div', { style: { color: '#f8fafc', whiteSpace: 'pre-wrap', lineHeight: '1.6' } }, m.text)
                    )),
                    el('div', { ref: chatEndRef })
                ),

                // Prompt suggestion pills
                el('div', { style: { padding: '8px 12px', background: '#090d16', borderTop: '1px solid #1e293b', display: 'flex', gap: '6px', overflowX: 'auto' } },
                    promptPills.map((pill, idx) => el('button', {
                        key: idx,
                        onClick: () => setInputPrompt(pill),
                        style: { background: '#1e293b', color: '#cbd5e1', border: '1px solid #334155', borderRadius: '14px', padding: '4px 10px', fontSize: '11px', cursor: 'pointer', whiteSpace: 'nowrap' }
                    }, pill))
                ),

                // Prompt Input Form
                el('form', { onSubmit: handleSendPrompt, style: { padding: '14px', background: '#0f172a', borderTop: '1px solid #1e293b', display: 'flex', flexDirection: 'column', gap: '10px' } },
                    statusMessage ? el('div', { style: { color: '#f59e0b', fontSize: '12px', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '6px' } }, `⏳ ${statusMessage}`) : null,
                    el('textarea', {
                        value: inputPrompt,
                        onChange: e => setInputPrompt(e.target.value),
                        placeholder: 'Ask AI Architect to generate features, hooks, custom shortcodes, or admin tables...',
                        rows: 3,
                        onKeyDown: e => { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) handleSendPrompt(e); },
                        style: { width: '100%', background: '#020617', color: '#fff', border: '1px solid #334155', borderRadius: '8px', padding: '10px', fontSize: '13px', outline: 'none', resize: 'none', boxSizing: 'border-box', lineHeight: '1.4' }
                    }),
                    el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                        el('span', { style: { fontSize: '11px', color: '#64748b' } }, 'Ctrl + Enter to send'),
                        el('button', { type: 'submit', disabled: isGenerating || !inputPrompt.trim(), style: { background: isGenerating ? '#475569' : '#4f46e5', color: '#fff', border: 'none', padding: '8px 18px', borderRadius: '8px', fontWeight: 'bold', fontSize: '13px', cursor: isGenerating ? 'not-allowed' : 'pointer' } }, isGenerating ? '⚡ Building...' : '🚀 Send Prompt')
                    )
                )
            ),

            // MIDDLE PANEL: FILE EXPLORER
            el('div', { style: { width: '230px', background: '#0f172a', borderRight: '1px solid #1e293b', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { padding: '12px 16px', background: '#1e293b', borderBottom: '1px solid #334155', fontWeight: '700', fontSize: '13px', color: '#cbd5e1' } }, '📁 Generated Files'),
                el('div', { style: { flex: 1, padding: '8px', overflowY: 'auto' } },
                    Object.keys(files).length === 0 ? el('div', { style: { color: '#64748b', fontSize: '12px', padding: '12px', fontStyle: 'italic', textAlign: 'center' } }, 'No files generated yet.') : null,
                    Object.keys(files).map(fn => el('div', {
                        key: fn,
                        onClick: () => handleSelectFile(fn),
                        style: { padding: '8px 12px', borderRadius: '6px', fontSize: '12px', fontFamily: 'monospace', cursor: 'pointer', background: activeFileName === fn ? '#1e293b' : 'transparent', color: activeFileName === fn ? '#38bdf8' : '#cbd5e1', fontWeight: activeFileName === fn ? 'bold' : 'normal', marginBottom: '3px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }
                    }, `📄 ${fn}`))
                )
            ),

            // RIGHT PANEL: SOURCE CODE EDITOR
            el('div', { style: { flex: 1, background: '#020617', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { height: '42px', background: '#0f172a', borderBottom: '1px solid #1e293b', display: 'flex', alignItems: 'center', padding: '0 16px', justifyContent: 'space-between' } },
                    el('span', { style: { fontFamily: 'monospace', fontSize: '13px', color: '#38bdf8', fontWeight: 'bold' } }, activeFileName ? `📄 ${activeFileName}` : 'No File Selected'),
                    activeFileName ? el('div', { style: { display: 'flex', gap: '8px' } },
                        el('button', { onClick: () => { navigator.clipboard.writeText(editorCode); addLog('Copied source code to clipboard.'); }, style: { background: '#1e293b', color: '#cbd5e1', border: '1px solid #334155', padding: '4px 10px', borderRadius: '6px', fontSize: '11px', fontWeight: 'bold', cursor: 'pointer' } }, '📋 Copy Code'),
                        el('button', { onClick: () => { setFiles(prev => ({ ...prev, [activeFileName]: editorCode })); addLog(`Saved changes to ${activeFileName}`); alert(`Saved ${activeFileName} successfully!`); }, style: { background: '#10b981', color: '#fff', border: 'none', padding: '4px 14px', borderRadius: '6px', fontSize: '12px', fontWeight: 'bold', cursor: 'pointer' } }, '💾 Save File')
                    ) : null
                ),
                el('textarea', {
                    value: editorCode,
                    onChange: e => setEditorCode(e.target.value),
                    style: { flex: 1, background: '#020617', color: '#f8fafc', border: 'none', padding: '16px', fontFamily: 'monospace', fontSize: '13px', outline: 'none', resize: 'none', lineHeight: '1.6', tabSize: 4 }
                })
            )
        ),

        // 3. BOTTOM PANEL: SYSTEM TELEMETRY & LOGS TERMINAL
        el('div', { style: { height: '120px', background: '#020617', borderTop: '1px solid #1e293b', padding: '10px 16px', overflowY: 'auto', fontFamily: 'monospace', fontSize: '12px', boxSizing: 'border-box' } },
            el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '6px' } },
                el('div', { style: { color: '#10b981', fontWeight: 'bold' } }, '>_ System Telemetry & Event Logs Terminal:'),
                el('div', { style: { display: 'flex', gap: '8px' } },
                    el('button', { onClick: () => { navigator.clipboard.writeText(terminalLogs.join('\n')); addLog('Copied logs to clipboard.'); }, style: { background: '#1e293b', color: '#cbd5e1', border: '1px solid #334155', padding: '2px 8px', borderRadius: '4px', fontSize: '11px', cursor: 'pointer' } }, '📋 Copy Logs'),
                    el('button', { onClick: () => setTerminalLogs([]), style: { background: '#1e293b', color: '#cbd5e1', border: '1px solid #334155', padding: '2px 8px', borderRadius: '4px', fontSize: '11px', cursor: 'pointer' } }, '🧹 Clear Log')
                )
            ),
            terminalLogs.map((log, i) => el('div', { key: i, style: { color: '#94a3b8', lineHeight: '1.4' } }, log)),
            el('div', { ref: terminalEndRef })
        ),

        // 4. LLM TEST DIAGNOSTIC MODAL OVERLAY
        showLlmModal ? el('div', { style: { position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.8)', backdropFilter: 'blur(4px)', zIndex: 99999, display: 'flex', justifyContent: 'center', alignItems: 'center' } },
            el('div', { style: { background: '#0f172a', color: '#fff', width: '90%', maxWidth: '550px', borderRadius: '14px', border: '1px solid #334155', padding: '24px', boxShadow: '0 25px 50px -12px rgba(0,0,0,0.5)' } },
                el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px', borderBottom: '1px solid #1e293b', paddingBottom: '10px' } },
                    el('h3', { style: { margin: 0, fontSize: '18px', color: '#818cf8', fontWeight: '800' } }, '🤖 AI Model & LLM Diagnostic Test'),
                    el('button', { onClick: () => setShowLlmModal(false), style: { background: 'none', border: 'none', color: '#94a3b8', fontSize: '18px', cursor: 'pointer' } }, '✕')
                ),
                isTestingLlm ? el('div', { style: { padding: '20px', textAlign: 'center', color: '#f59e0b', fontWeight: 'bold' } }, '⏳ Testing connectivity to LLM Providers & Multi-Agent Swarm...')
                : llmTestData ? el('div', { style: { fontSize: '13px', background: '#020617', padding: '16px', borderRadius: '8px', border: '1px solid #1e293b', fontFamily: 'monospace' } },
                    el('div', { style: { color: '#10b981', fontWeight: 'bold', fontSize: '14px', marginBottom: '8px' } }, `✅ LLM Status: ${llmTestData.status || 'ONLINE'}`),
                    el('div', { style: { color: '#cbd5e1', marginBottom: '4px' } }, `Active Model: `, el('strong', { style: { color: '#38bdf8' } }, llmTestData.active_model || 'Claude 3.5 / Swarm')),
                    el('div', { style: { color: '#cbd5e1', marginBottom: '4px' } }, `Response Latency: `, el('strong', { style: { color: '#f59e0b' } }, `${llmTestData.latency_ms} ms`)),
                    el('div', { style: { color: '#cbd5e1', marginTop: '8px' } }, `API Keys configured:`),
                    el('div', { style: { color: '#94a3b8', fontSize: '11px', marginTop: '2px' } }, `• Gemini: ${llmTestData.api_keys?.google_gemini ? '✓ Enabled' : '✗ Not set'}`),
                    el('div', { style: { color: '#94a3b8', fontSize: '11px' } }, `• Claude: ${llmTestData.api_keys?.anthropic_claude ? '✓ Enabled' : '✗ Not set'}`),
                    el('div', { style: { color: '#94a3b8', fontSize: '11px' } }, `• OpenAI: ${llmTestData.api_keys?.openai ? '✓ Enabled' : '✗ Not set'}`),
                    el('div', { style: { color: '#64748b', fontSize: '11px', marginTop: '10px' } }, llmTestData.message)
                ) : null
            )
        ) : null
    );
}