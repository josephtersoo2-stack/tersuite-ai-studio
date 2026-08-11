/**
 * Tersuite AI Studio — Authoritative Code Editor Component
 */
(function($) {
    'use strict';

    window.TSAEditor = {
        projectId: null,
        currentPath: '',
        originalContent: '',
        currentRevision: '',
        isDirty: false,

        init: function(projectId) {
            this.projectId = projectId || (window.TersuiteAI ? window.TersuiteAI.projectId : null);
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            $(document).off('input', '.tsa-editor-textarea').on('input', '.tsa-editor-textarea', function() {
                var newContent = $(this).val();
                self.isDirty = (newContent !== self.originalContent);
                self.updateSaveIndicator();
            });

            $(document).off('keydown.tsaEditor').on('keydown.tsaEditor', function(e){ if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='s'){ e.preventDefault(); self.saveCurrentFile(); } });

            $(document).off('click', '.tsa-btn-save-file').on('click', '.tsa-btn-save-file', function(e) {
                e.preventDefault();
                self.saveCurrentFile();
            });
        },

        loadFile: function(projectId, path) {
            var self = this;
            self.projectId = projectId || self.projectId;
            self.currentPath = path;

            if (!self.projectId || !path) return;

            self.updateBreadcrumb(path);

            var $wrap = $('.tsa-code-wrap');
            if ($wrap.length) {
                $wrap.html('<div style="padding:20px; color:#94a3b8; font-family:monospace; font-size:13px;">Loading file ' + $('<div/>').text(path).html() + '...</div>');
            }

            TSAAPI.file(self.projectId, path)
                .done(function(res) {
                    if (res && res.success && res.data) {
                        var content = typeof res.data === 'string' ? res.data : (res.data.content || JSON.stringify(res.data, null, 2));
                        self.originalContent = content;
                        self.currentRevision = (typeof res.data === 'object' && res.data.revision_id) ? res.data.revision_id : (typeof res.data === 'object' && res.data.revision ? res.data.revision : '');
                        self.isDirty = false;
                        self.renderCode(path, content);
                    } else {
                        self.renderError('Unable to load file contents.');
                    }
                })
                .fail(function() {
                    self.renderError('Failed to connect to backend to fetch file.');
                });
        },

        saveCurrentFile: function() {
            var self = this;
            if (!self.projectId || !self.currentPath) return;

            var $textarea = $('.tsa-editor-textarea');
            var content = $textarea.length ? $textarea.val() : self.originalContent;

            if (window.TSA) window.TSA.toast('Saving file...', 'info');

            TSAAPI.saveFile(self.projectId, self.currentPath, content, self.currentRevision)
                .done(function(res) {
                    if (res && res.success) {
                        self.originalContent = content;
                        self.currentRevision = (typeof res.data === 'object' && res.data.revision_id) ? res.data.revision_id : (typeof res.data === 'object' && res.data.revision ? res.data.revision : '');
                        self.isDirty = false;
                        self.updateSaveIndicator();
                        if (window.TSA) window.TSA.toast('File saved successfully!', 'success');
                    } else {
                        if (window.TSA) window.TSA.toast('Failed to save file.', 'error');
                    }
                })
                .fail(function() {
                    if (window.TSA) window.TSA.toast('Connection error while saving file.', 'error');
                });
        },

        updateBreadcrumb: function(path) {
            var parts = path.split('/');
            var fileName = parts.pop();
            var dirPath = parts.join(' › ');

            var $bc = $('.tsa-breadcrumb');
            if ($bc.length) {
                $bc.html((dirPath ? dirPath + ' › ' : '') + '<b>' + $('<div/>').text(fileName).html() + '</b>');
            }

            var $tabs = $('.tsa-editor-tabs');
            if ($tabs.length) {
                $tabs.find('.editor-tab').first().html($('<div/>').text(fileName).html() + ' <span class="tsa-unsaved-badge" style="display:none; color:#f59e0b;">*</span>');
            }
        },

        updateSaveIndicator: function() {
            var $badge = $('.tsa-unsaved-badge');
            if (this.isDirty) {
                $badge.show();
            } else {
                $badge.hide();
            }
        },

        renderCode: function(path, content) {
            var $wrap = $('.tsa-code-wrap');
            if (!$wrap.length) return;

            var lines = content.split('\n');
            var codeHtml = '<textarea class="tsa-editor-textarea" style="width:100%; height:450px; background:#0f172a; color:#f8fafc; font-family:monospace; font-size:13px; border:none; padding:16px; outline:none; resize:none; line-height:1.6;">' +
                $('<div/>').text(content).html() +
                '</textarea>';

            $wrap.html(codeHtml);
        },

        renderError: function(msg) {
            var $wrap = $('.tsa-code-wrap');
            if ($wrap.length) {
                $wrap.html('<div style="padding:20px; color:#f87171; font-family:monospace; font-size:13px;">Error: ' + $('<div/>').text(msg).html() + '</div>');
            }
        }
    };

})(jQuery);
