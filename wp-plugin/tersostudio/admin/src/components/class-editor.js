const el = wp.element.createElement;
const useState = wp.element.useState;
const useEffect = wp.element.useEffect;
const useRef = wp.element.useRef;

export function WorkspaceMainLayoutShell() {
    const S = window.TERSOSTUDIO_State || { projectId: 0, currentFiles: {}, chatHistory: [], terminalLogs: [], rest_url: '', nonce: '' };
    const [chatHistory, setChatHistory] = useState(S.chatHistory || []);
    const [currentFiles, setCurrentFiles] = useState(S.currentFiles || {});
    const [terminalLogs, setTerminalLogs] = useState(S.terminalLogs || []);
    const [inputMessage, setInputMessage] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);
    const [activeBufferFile, setActiveBufferFile] = useState('none_selected.php');
    const [fileEditorBuffer, setFileEditorBuffer] = useState('');
    const [isEditingFile, setIsEditingFile] = useState(false);

    function appendTerminalLog(text) {
        setTerminalLogs(prev => [...prev, `[${new Date().toLocaleTimeString()}] ${text}`]);
    }

    return el('div', { style: { display: 'flex', flexDirection: 'column', height: '100vh', background: '#121214', color: '#e1e1e6', fontFamily: 'monospace' } },
        el('div', { style: { height: '40px', background: '#1a1a1e', borderBottom: '1px solid #29292e', display: 'flex', alignItems: 'center', padding: '0 16px', justifyContent: 'space-between' } },
            el('span', null, '⚙️ TERSOSTUDIO v2 WORKBENCH // MODULAR ARCHITECTURE BUNDLE'),
            el('span', { style: { color: '#10b981' } }, '🟢 SYSTEM STANDBY')
        ),
        el('div', { style: { display: 'flex', flex: 1, overflow: 'hidden' } },
            el('div', { style: { width: '45%', background: '#16161a', borderRight: '1px solid #29292e', padding: '12px' } },
                el('h3', null, '🤖 Architect Chat Subsystem Input'),
                el('div', { style: { height: '70%', overflowY: 'auto', background: '#111827', borderRadius: '6px', padding: '10px', marginBottom: '10px' } },
                    chatHistory.map((msg, i) => el('div', { key: i, style: { marginBottom: '10px' } }, el('strong', null, msg.sender_role + ': '), msg.message_body))
                )
            ),
            el('div', { style: { width: '55%', background: '#121214', display: 'flex', flexDirection: 'column' } },
                el('div', { style: { height: '38px', background: '#1a1a1e', borderBottom: '1px solid #29292e', display: 'flex', alignItems: 'center', padding: '0 12px', justifyContent: 'space-between' } },
                    el('span', null, '📄 ' + activeBufferFile),
                    activeBufferFile !== 'none_selected.php' ? el('button', { onClick: () => setIsEditingFile(!isEditingFile), style: { background: '#2563eb', color: '#fff', border: 'none', padding: '4px 10px', borderRadius: '4px', cursor: 'pointer' } }, isEditingFile ? 'Lock View' : 'Edit Source') : null
                ),
                el('div', { style: { flex: 1, background: '#18181b', padding: '12px' } },
                    isEditingFile 
                        ? el('textarea', { value: fileEditorBuffer, onChange: (e) => setFileEditorBuffer(e.target.value), style: { width: '100%', height: '100%', background: '#151518', color: '#38bdf8', border: 'none', fontFamily: 'monospace', fontSize: '13px', outline: 'none', resize: 'none' } })
                        : el('pre', null, fileEditorBuffer || '[Select an operational workspace ledger script tracking node from registry arrays]')
                )
            )
        ),
        el('div', { style: { height: '120px', background: '#09090b', borderTop: '1px solid #29292e', padding: '12px', overflowY: 'auto' } },
            el('div', { style: { color: '#10b981', fontWeight: 'bold', marginBottom: '6px' } }, '>_ Core Telemetry Operations Matrix Ledger Terminal:'),
            terminalLogs.map((log, i) => el('div', { key: i }, log))
        )
    );
}