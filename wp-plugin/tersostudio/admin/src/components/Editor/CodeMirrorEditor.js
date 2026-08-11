import { getEditorModeForFile } from '../../core/editor-utils.js';

export function createCodeMirrorEditor(textarea, filePath, settings = {}) {
    if (!window.wp || !window.wp.codeEditor || !textarea) {
        return null;
    }

    const editorSettings = {
        ...settings,
        codemirror: {
            ...(settings.codemirror || {}),
            mode: getEditorModeForFile(filePath),
            lineNumbers: true,
            lineWrapping: false,
            styleActiveLine: true,
            matchBrackets: true,
            indentUnit: 4,
            tabSize: 4,
        },
    };

    const editor = window.wp.codeEditor.initialize(textarea, editorSettings);
    if (editor && editor.codemirror) {
        editor.codemirror.setSize('100%', '100%');
        editor.codemirror.refresh();
    }

    return editor;
}
