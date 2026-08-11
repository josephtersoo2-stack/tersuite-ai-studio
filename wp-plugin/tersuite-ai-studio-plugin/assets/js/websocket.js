/**
 * Tersuite AI Studio — Secure WebSocket Live Telemetry Client & Event Router
 *
 * Security & Reliability:
 * - Uses short-lived ticket or token authentication.
 * - Preserves listener registry map across automatic reconnection.
 * - Dispatches backend events to registered UI components (Studio, Task Graph, File Tree).
 */
(function($) {
    'use strict';

    window.TSAWebSocket = {
        socket: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 5,
        reconnectDelay: 3000,
        listeners: [],

        /**
         * Initialize WebSocket connection for a project.
         */
        connect: function(url, projectId, ticket, onEvent) {
            if (!url || !window.WebSocket) {
                console.warn('[Tersuite WS] WebSocket not supported or URL missing.');
                return null;
            }

            var self = this;

            // Preserve listener callback across reconnects
            if (onEvent && typeof onEvent === 'function' && self.listeners.indexOf(onEvent) === -1) {
                self.listeners.push(onEvent);
            }

            // Build secure WebSocket URL using short-lived ticket
            var fullUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'project_id=' + encodeURIComponent(projectId);
            if (ticket) {
                fullUrl += '&ticket=' + encodeURIComponent(ticket);
            }

            try {
                if (self.socket && (self.socket.readyState === WebSocket.OPEN || self.socket.readyState === WebSocket.CONNECTING)) {
                    return self.socket;
                }

                self.socket = new WebSocket(fullUrl);

                self.socket.onopen = function() {
                    console.log('[Tersuite WS] Live Telemetry stream established for project #' + projectId);
                    self.reconnectAttempts = 0;
                    self.emit('connection.established', { status: 'connected', projectId: projectId });
                };

                self.socket.onmessage = function(e) {
                    try {
                        var eventData = JSON.parse(e.data);
                        self.handleEvent(eventData);
                    } catch (err) {
                        console.error('[Tersuite WS] Failed to parse event payload:', err);
                    }
                };

                self.socket.onerror = function(err) {
                    console.warn('[Tersuite WS] Live stream error:', err);
                    self.emit('connection.error', { error: err });
                };

                self.socket.onclose = function(e) {
                    console.log('[Tersuite WS] Telemetry socket closed (code ' + e.code + ').');
                    self.emit('connection.closed', { code: e.code });
                    self.scheduleReconnect(url, projectId, ticket);
                };

            } catch (err) {
                console.error('[Tersuite WS] Initialization error:', err);
            }

            return self.socket;
        },

        /**
         * Reconnection logic with backoff, preserving all registered listeners.
         */
        scheduleReconnect: function(url, projectId, ticket) {
            var self = this;
            if (self.reconnectAttempts < self.maxReconnectAttempts) {
                self.reconnectAttempts++;
                setTimeout(function() {
                    console.log('[Tersuite WS] Reconnecting (Attempt ' + self.reconnectAttempts + ')...');
                    self.connect(url, projectId, ticket, null);
                }, self.reconnectDelay * self.reconnectAttempts);
            }
        },

        /**
         * Register event listener callback.
         */
        on: function(callback) {
            if (typeof callback === 'function' && this.listeners.indexOf(callback) === -1) {
                this.listeners.push(callback);
            }
        },

        /**
         * Dispatch event payload to all registered listeners.
         */
        handleEvent: function(data) {
            if (!data || !data.event) return;

            for (var i = 0; i < this.listeners.length; i++) {
                try {
                    this.listeners[i](data);
                } catch (e) {
                    console.error('[Tersuite WS Handler Error]:', e);
                }
            }
        },

        emit: function(eventName, payload) {
            this.handleEvent({ event: eventName, payload: payload });
        },

        disconnect: function() {
            if (this.socket) {
                this.socket.close();
                this.socket = null;
            }
        }
    };

})(jQuery);