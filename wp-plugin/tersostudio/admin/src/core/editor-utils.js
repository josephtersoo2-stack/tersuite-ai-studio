export function getEditorModeForFile(filePath) {
    var ext = '';
    if (filePath && filePath.indexOf('.') !== -1) {
        ext = filePath.split('.').pop().toLowerCase();
    }

    var map = {
        php: 'text/x-php',
        phtml: 'text/x-php',
        inc: 'text/x-php',
        js: 'text/javascript',
        mjs: 'text/javascript',
        cjs: 'text/javascript',
        ts: 'text/typescript',
        jsx: 'text/javascript',
        css: 'text/css',
        scss: 'text/x-scss',
        json: 'application/json',
        html: 'text/html',
        htm: 'text/html',
        md: 'text/x-markdown',
        txt: 'text/plain'
    };

    return map[ext] || 'text/x-php';
}

export function tokenizeCodeLine(codeLine) {
    const tokenRegex = /("(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|([$][a-zA-Z0-9_]+)|(\b(?:function|class|public|private|protected|return|if|else|foreach|while|new|extends|global|exit|defined|const|static|use|try|catch|throw|switch|case|break|continue|default|namespace|trait|interface|implements)\b)|(\b\d+(?:\.\d+)?\b)|([a-zA-Z0-9_]+)|([^\s\w]))/g;
    const parts = [];
    let currentMatch;
    let trackingIndexPointer = 0;

    while ((currentMatch = tokenRegex.exec(codeLine)) !== null) {
        if (currentMatch.index > trackingIndexPointer) {
            parts.push(codeLine.substring(trackingIndexPointer, currentMatch.index));
        }

        const slice = currentMatch[0];
        if (currentMatch[1]) {
            parts.push({ type: 'string', text: slice });
        } else if (currentMatch[2]) {
            parts.push({ type: 'variable', text: slice });
        } else if (currentMatch[3]) {
            parts.push({ type: 'keyword', text: slice });
        } else if (currentMatch[4]) {
            parts.push({ type: 'number', text: slice });
        } else if (currentMatch[5]) {
            parts.push(slice);
        } else if (currentMatch[6]) {
            parts.push({ type: 'symbol', text: slice });
        } else {
            parts.push(slice);
        }

        trackingIndexPointer = tokenRegex.lastIndex;
    }

    if (trackingIndexPointer < codeLine.length) {
        parts.push(codeLine.substring(trackingIndexPointer));
    }

    return parts.length ? parts : ['\u00a0'];
}
