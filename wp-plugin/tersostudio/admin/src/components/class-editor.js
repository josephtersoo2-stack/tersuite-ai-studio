const el = wp.element.createElement;
const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

export function WorkspaceMainLayoutShell() {
    const S = window.TERSOSTUDIO_State || {};
    const restUrl = S.rest_url || '/wp-json/tersostudio/v2';
    const nonce = S.nonce || '';

    const urlParams = new URLSearchParams(window.location.search);
    const initialProjectId = urlParams.get('project_id') || '';

    const [projects, setProjects] = useState([]);
    const [selectedProjectId, setSelectedProjectId] = useState(initialProjectId);
    const [activeProject, setActiveProject] = useState(null);

    const [chatHistory, setChatHistory] = useState([]);
    const [inputPrompt, setInputPrompt] = useState('');
    const [isGenerating, setIsGenerating] = useState(false);
    const [statusMessage, setStatusMessage] = useState('');

    const [files, setFiles] = useState({});
    const [activeFileName, setActiveFileName] = useState('');
    const [editorCode, setEditorCode] = useState('');
    const [terminalLogs, setTerminalLogs] = useState([]);

    function addLog(msg) {
        const time = new Date().toLocaleTimeString();
        setTerminalLogs(prev => [...prev, `[${time}] ${msg}`]);
    }

    // 1. Fetch available projects
    useEffect(() => {
        addLog('Initializing Workspace IDE... Loading projects registry.');
        fetch(restUrl + '/projects', { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json())
            .then(data => {
                const list = data.results || data.projects || data || [];
                setProjects(list);
                if (list.length > 0 && !selectedProjectId) {
                    setSelectedProjectId(list[0].id);
                }
            })
            .catch(err => addLog(`Error loading projects: ${err.message}`));
    }, []);

    // 2. Fetch selected project details & files
    useEffect(() => {
        if (!selectedProjectId) return;
        addLog(`Loading project context #${selectedProjectId.substring(0, 8)}...`);

        fetch(restUrl + '/chat/deliver/' + selectedProjectId, { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json())
            .then(data => {
                if (data.files && Object.keys(data.files).length > 0) {
                    setFiles(data.files);
                    const firstFile = Object.keys(data.files)[0];
                    setActiveFileName(firstFile);
                    setEditorCode(data.files[firstFile]);
                    addLog(`Loaded ${Object.keys(data.files).length} generated plugin file(s).`);
                } else {
                    setFiles({});
                    setActiveFileName('');
                    setEditorCode('// No generated files yet. Send a prompt in the Chat panel to build your plugin!');
                }
            })
            .catch(() => {
                setFiles({});
                setActiveFileName('');
                setEditorCode('// Ready to generate plugin files.');
            });
    }, [selectedProjectId]);

    // Handle project dropdown change
    function handleProjectChange(e) {
        const newId = e.target.value;
        setSelectedProjectId(newId);
        window.history.replaceState({}, '', `?page=tersostudio-workbench&project_id=${newId}`);
    }

    // Select file in explorer
    function handleSelectFile(fileName) {
        setActiveFileName(fileName);
        setEditorCode(files[fileName] || '');
        addLog(`Opened file: ${fileName}`);
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
        setStatusMessage('Transmitting prompt to AI Architect pipeline...');
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
            if (data.status === 'started' || data.status === 'running') {
                addLog('AI Agent pipeline engaged! Generating code files...');
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

    // Poll for progress updates
    function pollProgress(pId) {
        const interval = setInterval(() => {
            fetch(restUrl + '/chat/stream/' + pId, { headers: { 'X-WP-Nonce': nonce } })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(interval);
                        setIsGenerating(false);
                        setStatusMessage('');
                        addLog('✅ Agent pipeline completed generation successfully!');

                        // Fetch generated files
                        fetch(restUrl + '/chat/deliver/' + pId, { headers: { 'X-WP-Nonce': nonce } })
                            .then(r => r.json())
                            .then(deliv => {
                                if (deliv.files) {
                                    setFiles(deliv.files);
                                    const firstFile = Object.keys(deliv.files)[0];
                                    setActiveFileName(firstFile);
                                    setEditorCode(deliv.files[firstFile]);
                                    addLog(`Updated workspace with ${Object.keys(deliv.files).length} generated plugin file(s).`);
                                }
                            });

                        const aiMsg = { role: 'ai', text: '🎉 Plugin code generated and loaded into Workspace editor!', time: new Date().toLocaleTimeString() };
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

    return el('div', { style: { display: 'flex', flexDirection: 'column', height: 'calc(100vh - 40px)', background: '#0b0f19', color: '#f1f5f9', fontFamily: 'Inter, system-ui, sans-serif' } },
        
        // 1. TOP HEADER TOOLBAR
        el('div', { style: { height: '50px', background: '#0f172a', borderBottom: '1px solid #1e293b', display: 'flex', alignItems: 'center', padding: '0 16px', justifyContent: 'space-between' } },
            el('div', { style: { display: 'flex', alignItems: 'center', gap: '16px' } },
                el('span', { style: { fontWeight: '800', fontSize: '15px', color: '#6366f1', display: 'flex', alignItems: 'center', gap: '8px' } }, '⚡ TersoStudio IDE'),
                el('select', { value: selectedProjectId, onChange: handleProjectChange, style: { background: '#020617', color: '#38bdf8', border: '1px solid #334155', borderRadius: '6px', padding: '6px 12px', fontSize: '13px', outline: 'none' } },
                    projects.length === 0 ? el('option', { value: '' }, 'No projects found') : null,
                    projects.map(p => el('option', { key: p.id, value: p.id }, `${p.name} (${p.category?.name || 'General'})`))
                )
            ),
            el('div', { style: { display: 'flex', alignItems: 'center', gap: '12px' } },
                el('span', { style: { background: isGenerating ? 'rgba(245,158,11,0.2)' : 'rgba(16,185,129,0.2)', color: isGenerating ? '#f59e0b' : '#10b981', padding: '4px 10px', borderRadius: '12px', fontSize: '12px', fontWeight: 'bold' } }, isGenerating ? '⚡ AI Building...' : '🟢 Standby Ready')
            )
        ),

        // 2. MAIN WORKSPACE CONTENT AREA
        el('div', { style: { display: 'flex', flex: 1, overflow: 'hidden' } },
            
            // LEFT PANEL: AI ARCHITECT CHAT
            el('div', { style: { width: '420px', background: '#0f172a', borderRight: '1px solid #1e293b', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { padding: '12px 16px', background: '#1e293b', borderBottom: '1px solid #334155', fontWeight: '700', fontSize: '14px', color: '#cbd5e1' } }, '🤖 AI Architect Assistant'),
                
                // Chat history log
                el('div', { style: { flex: 1, padding: '16px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '12px', background: '#020617' } },
                    chatHistory.length === 0 ? el('div', { style: { color: '#64748b', fontStyle: 'italic', fontSize: '13px', textAlign: 'center', marginTop: '40px' } }, 'No prompts sent yet. Ask AI Architect to build features, hooks, shortcodes, or custom admin pages!') : null,
                    chatHistory.map((m, i) => el('div', { key: i, style: { background: m.role === 'user' ? '#1e1b4b' : '#0f172a', border: `1px solid ${m.role === 'user' ? '#4338ca' : '#1e293b'}`, padding: '12px', borderRadius: '8px', fontSize: '13px' } },
                        el('div', { style: { display: 'flex', justifyContent: 'space-between', marginBottom: '6px', fontSize: '11px', color: m.role === 'user' ? '#a5b4fc' : '#38bdf8', fontWeight: 'bold' } },
                            el('span', null, m.role === 'user' ? '👤 YOU' : '🤖 AI ARCHITECT'),
                            el('span', { style: { color: '#64748b' } }, m.time)
                        ),
                        el('div', { style: { color: '#f8fafc', whiteSpace: 'pre-wrap', lineHeight: '1.5' } }, m.text)
                    ))
                ),

                // Prompt Input Box
                el('form', { onSubmit: handleSendPrompt, style: { padding: '16px', background: '#0f172a', borderTop: '1px solid #1e293b', display: 'flex', flexDirection: 'column', gap: '10px' } },
                    statusMessage ? el('div', { style: { color: '#f59e0b', fontSize: '12px', fontWeight: 'bold' } }, statusMessage) : null,
                    el('textarea', {
                        value: inputPrompt,
                        onChange: e => setInputPrompt(e.target.value),
                        placeholder: 'Describe features to build (e.g. Add a WooCommerce checkout tax box & admin settings page)...',
                        rows: 3,
                        onKeyDown: e => { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) handleSendPrompt(e); },
                        style: { width: '100%', background: '#020617', color: '#fff', border: '1px solid #334155', borderRadius: '8px', padding: '10px', fontSize: '13px', outline: 'none', resize: 'none', boxSizing: 'border-box' }
                    }),
                    el('button', { type: 'submit', disabled: isGenerating, style: { background: isGenerating ? '#475569' : '#4f46e5', color: '#fff', border: 'none', padding: '10px', borderRadius: '8px', fontWeight: 'bold', fontSize: '13px', cursor: isGenerating ? 'not-allowed' : 'pointer' } }, isGenerating ? '⚡ Generating Code...' : '🚀 Send Prompt to AI Architect')
                )
            ),

            // MIDDLE PANEL: FILE EXPLORER
            el('div', { style: { width: '220px', background: '#0f172a', borderRight: '1px solid #1e293b', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { padding: '12px 16px', background: '#1e293b', borderBottom: '1px solid #334155', fontWeight: '700', fontSize: '14px', color: '#cbd5e1' } }, '📁 Project Files'),
                el('div', { style: { flex: 1, padding: '8px', overflowY: 'auto' } },
                    Object.keys(files).length === 0 ? el('div', { style: { color: '#64748b', fontSize: '12px', padding: '12px', fontStyle: 'italic' } }, 'No files generated') : null,
                    Object.keys(files).map(fn => el('div', {
                        key: fn,
                        onClick: () => handleSelectFile(fn),
                        style: { padding: '8px 12px', borderRadius: '6px', fontSize: '12px', fontFamily: 'monospace', cursor: 'pointer', background: activeFileName === fn ? '#1e293b' : 'transparent', color: activeFileName === fn ? '#38bdf8' : '#cbd5e1', fontWeight: activeFileName === fn ? 'bold' : 'normal', marginBottom: '2px' }
                    }, `📄 ${fn}`))
                )
            ),

            // RIGHT PANEL: SOURCE CODE EDITOR
            el('div', { style: { flex: 1, background: '#020617', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { height: '40px', background: '#0f172a', borderBottom: '1px solid #1e293b', display: 'flex', alignItems: 'center', padding: '0 16px', justifyContent: 'space-between' } },
                    el('span', { style: { fontFamily: 'monospace', fontSize: '13px', color: '#38bdf8', fontWeight: 'bold' } }, activeFileName ? `📄 ${activeFileName}` : 'No File Selected'),
                    activeFileName ? el('button', { onClick: () => { setFiles(prev => ({ ...prev, [activeFileName]: editorCode })); addLog(`Saved changes to ${activeFileName}`); alert(`Saved ${activeFileName} successfully!`); }, style: { background: '#10b981', color: '#fff', border: 'none', padding: '4px 12px', borderRadius: '6px', fontSize: '12px', fontWeight: 'bold', cursor: 'pointer' } }, '💾 Save File') : null
                ),
                el('textarea', {
                    value: editorCode,
                    onChange: e => setEditorCode(e.target.value),
                    style: { flex: 1, background: '#020617', color: '#f8fafc', border: 'none', padding: '16px', fontFamily: 'monospace', fontSize: '13px', outline: 'none', resize: 'none', lineHeight: '1.5', tabSize: 4 }
                })
            )
        ),

        // 3. BOTTOM PANEL: SYSTEM TELEMETRY & LOGS TERMINAL
        el('div', { style: { height: '110px', background: '#020617', borderTop: '1px solid #1e293b', padding: '10px 16px', overflowY: 'auto', fontFamily: 'monospace', fontSize: '12px' } },
            el('div', { style: { color: '#10b981', fontWeight: 'bold', marginBottom: '4px' } }, '>_ System Telemetry & Logs Terminal:'),
            terminalLogs.map((log, i) => el('div', { key: i, style: { color: '#94a3b8', lineHeight: '1.4' } }, log))
        )
    );
}