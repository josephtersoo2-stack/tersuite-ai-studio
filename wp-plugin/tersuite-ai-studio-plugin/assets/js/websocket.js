/**
 * Tersuite AI Studio — WebSocket Live Telemetry Client & Event Router
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
        connect: function(url, projectId, apiKey, onEvent) {
            if (!url || !window.WebSocket) {
                console.warn('[Tersuite WS] WebSocket not supported or URL missing.');
                return null;
            }

            var self = this;
            if (onEvent) {
                self.listeners.push(onEvent);
            }

            var fullUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'project_id=' + encodeURIComponent(projectId);
            if (apiKey) {
                fullUrl += '&token=' + encodeURIComponent(apiKey);
            }

            try {
                self.socket = new WebSocket(fullUrl);

                self.socket.onopen = function() {
                    console.log('[Tersuite WS] Live Telemetry connected for project #' + projectId);
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
                    console.warn('[Tersuite WS] Connection error:', err);
                    self.emit('connection.error', { error: err });
                };

                self.socket.onclose = function() {
                    console.log('[Tersuite WS] Telemetry socket closed.');
                    self.emit('connection.closed', {});
                    self.scheduleReconnect(url, projectId, apiKey);
                };

            } catch (err) {
                console.error('[Tersuite WS] Initialization failed:', err);
            }

            return self.socket;
        },

        /**
         * Reconnection logic with backoff.
         */
        scheduleReconnect: function(url, projectId, apiKey) {
            var self = this;
            if (self.reconnectAttempts < self.maxReconnectAttempts) {
                self.reconnectAttempts++;
                setTimeout(function() {
                    console.log('[Tersuite WS] Reconnecting (Attempt ' + self.reconnectAttempts + ')...');
                    self.connect(url, projectId, apiKey);
                }, self.reconnectDelay * self.reconnectAttempts);
            }
        },

        /**
         * Register event listener callback.
         */
        on: function(callback) {
            if (typeof callback === 'function') {
                this.listeners.push(callback);
            }
        },

        /**
         * Dispatch event payload to listeners.
         */
        handleEvent: function(data) {
            if (!data || !data.event) return;

            console.log('[Tersuite WS Event]:', data.event, data);

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