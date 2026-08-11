/**
 * Tersuite AI Studio — Authoritative File Tree Component
 */
(function($) {
    'use strict';

    window.TSAFileTree = {
        projectId: null,
        onFileSelectCallback: null,

        init: function(projectId, onSelect) {
            this.projectId = projectId || (window.TersuiteAI ? window.TersuiteAI.projectId : null);
            this.onFileSelectCallback = onSelect;
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;
            $(document).off('click', '.tsa-tree .file-item, .tsa-tree .tree-indent.deeper').on('click', '.tsa-tree .file-item, .tsa-tree .tree-indent.deeper', function(e) {
                e.stopPropagation();
                var path = $(this).attr('data-path');
                $('.tsa-tree .selected').removeClass('selected');
                $(this).addClass('selected');

                if (path && typeof self.onFileSelectCallback === 'function') {
                    self.onFileSelectCallback(path);
                }
            });
        },

        loadTree: function(projectId) {
            var self = this;
            var id = projectId || self.projectId;
            if (!id) return;

            var $container = $('#tsa-file-tree');
            $container.html('<div class="tsa-tree-loading" style="padding:10px; color:#94a3b8; font-size:12px;">Loading workspace manifest...</div>');

            TSAAPI.files(id)
                .done(function(res) {
                    if (res && res.success && res.data) {
                        self.render(res.data);
                    } else {
                        $container.html('<div class="tsa-tree-empty" style="padding:10px; color:#64748b; font-size:12px;">No generated files in workspace yet.</div>');
                    }
                })
                .fail(function() {
                    $container.html('<div class="tsa-tree-error" style="padding:10px; color:#f87171; font-size:12px;">Failed to load workspace files.</div>');
                });
        },

        render: function(treeData) {
            var $container = $('#tsa-file-tree');
            if (!$container.length) return;

            if (Array.isArray(treeData) && treeData.length === 0) {
                $container.html('<div class="tsa-tree-empty" style="padding:10px; color:#64748b; font-size:12px;">Workspace is empty.</div>');
                return;
            }

            var html = '';

            // Handle flat list or nested tree structure
            if (Array.isArray(treeData)) {
                for (var i = 0; i < treeData.length; i++) {
                    var item = treeData[i];
                    var path = typeof item === 'string' ? item : item.path;
                    var name = path.split('/').pop();
                    var ext = name.indexOf('.') !== -1 ? name.split('.').pop().toUpperCase() : 'FILE';

                    html += '<div class="tree-item file-item" data-path="' + $('<div/>').text(path).html() + '">' +
                        '<span class="file">' + ext + '</span> ' + $('<div/>').text(name).html() +
                        '</div>';
                }
            } else if (typeof treeData === 'object') {
                html = this.buildNestedHtml(treeData);
            }

            $container.html(html);
        },

        buildNestedHtml: function(obj, currentPath) {
            currentPath = currentPath || '';
            var html = '';

            for (var key in obj) {
                if (!obj.hasOwnProperty(key)) continue;
                var itemPath = currentPath ? currentPath + '/' + key : key;

                if (typeof obj[key] === 'object' && obj[key] !== null) {
                    html += '<div class="tree-indent">⌄ <span class="folder">' + $('<div/>').text(key).html() + '</span>' +
                        this.buildNestedHtml(obj[key], itemPath) +
                        '</div>';
                } else {
                    var ext = key.indexOf('.') !== -1 ? key.split('.').pop().toUpperCase() : 'FILE';
                    html += '<div class="tree-indent deeper file-item" data-path="' + $('<div/>').text(itemPath).html() + '">' +
                        '<span class="file">' + ext + '</span> ' + $('<div/>').text(key).html() +
                        '</div>';
                }
            }

            return html;
        }
    };

})(jQuery);
