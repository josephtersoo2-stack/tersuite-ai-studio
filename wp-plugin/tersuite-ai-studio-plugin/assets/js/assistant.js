/**
 * Tersuite AI Studio — Coordinator Assistant Component
 *
 * Responsibilities:
 * - Handle conversation with the single user-facing identity: Tersuite Coordinator.
 * - Enforce Coordinator-only interaction (NO specialist-agent selector).
 * - Attach UI context payload (screen, selected file, version, session) to every request.
 * - Render formatted Coordinator messages, action triggers, and plan approval prompts.
 */
(function($) {
    'use strict';

    window.TSAAssistant = {
        projectId: null,
        selectedFile: '',
        selectedVersion: '',
        activeSession: '',

        init: function(projectId) {
            this.projectId = projectId || window.TersuiteAI ? window.TersuiteAI.projectId : null;
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            $('#tsa-send-chat').off('click').on('click', function(e) {
                e.preventDefault();
                self.sendMessage();
            });

            $('#tsa-chat-input').off('keydown').on('keydown', function(e) {
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });
        },

        setContext: function(file, version, session) {
            if (file !== undefined) this.selectedFile = file;
            if (version !== undefined) this.selectedVersion = version;
            if (session !== undefined) this.activeSession = session;
        },

        getUiContext: function() {
            return {
                project_id: this.projectId,
                screen: 'ai_studio',
                selected_file: this.selectedFile,
                selected_version: this.selectedVersion,
                active_session: this.activeSession,
                route: window.TersuiteAI ? window.TersuiteAI.page : 'tersuite-ai-studio'
            };
        },

        sendMessage: function() {
            var self = this;
            var $input = $('#tsa-chat-input');
            var text = $input.val().trim();

            if (!text) return;
            if (!self.projectId) {
                if (window.TSA) window.TSA.toast('Please select or open a project first.', 'info');
                return;
            }

            var safeText = $('<div/>').text(text).html();
            var timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Append User Message
            var userHtml = '<div class="chat-user"><strong>You</strong><small>' + timeStr + '</small><p>' + safeText + '</p></div>';
            $('#tsa-chat').append(userHtml);
            $input.val('');
            self.scrollToBottom();

            // Append Typing Indicator
            var $typing = $('<div class="chat-agent coordinator typing-indicator"><div class="agent-head"><span class="agent-avatar">✦</span><strong>Tersuite Coordinator</strong><span class="tsa-status-chip">Thinking...</span></div><p><i>Analyzing project context & production plan...</i></p></div>');
            $('#tsa-chat').append($typing);
            self.scrollToBottom();

            // Send to Coordinator API via API Client
            TSAAPI.coordinatorMessage(self.projectId, text, self.getUiContext())
                .done(function(res) {
                    $typing.remove();
                    if (res && res.success && res.data) {
                        self.renderResponse(res.data);
                    } else {
                        var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Coordinator could not process your message.';
                        self.renderError(errMsg);
                    }
                })
                .fail(function(xhr) {
                    $typing.remove();
                    self.renderError('Connection to Coordinator backend failed.');
                });
        },

        renderResponse: function(data) {
            var text = data.message || data.text || 'Coordinator received your message.';
            var safeText = $('<div/>').text(text).html();
            var timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            var actionsHtml = '';
            if (data.plan_ready || data.plan_id) {
                var planId = data.plan_id || 'latest';
                actionsHtml = '<div class="coordinator-actions" style="margin-top:10px;">' +
                    '<button class="tsa-secondary tsa-btn-view-plan" data-plan-id="' + planId + '">View Production Plan</button>' +
                    '<button class="tsa-primary tsa-btn-approve-plan" data-plan-id="' + planId + '">Approve Production ⚡</button>' +
                    '</div>';
            }

            var agentHtml = '<div class="chat-agent coordinator"><div class="agent-head"><span class="agent-avatar">✦</span><strong>Tersuite Coordinator</strong><span class="tsa-status-chip live">' + timeStr + '</span></div><p>' + safeText + '</p>' + actionsHtml + '</div>';

            $('#tsa-chat').append(agentHtml);
            this.scrollToBottom();
        },

        renderError: function(errMsg) {
            var agentHtml = '<div class="chat-agent coordinator error-msg"><div class="agent-head"><span class="agent-avatar">✦</span><strong>Tersuite Coordinator</strong><span class="tsa-status-chip error">Error</span></div><p style="color:#f87171;">' + $('<div/>').text(errMsg).html() + '</p></div>';
            $('#tsa-chat').append(agentHtml);
            this.scrollToBottom();
        },

        scrollToBottom: function() {
            var $chat = $('#tsa-chat');
            if ($chat.length) {
                $chat.scrollTop($chat[0].scrollHeight);
            }
        }
    };

})(jQuery);
