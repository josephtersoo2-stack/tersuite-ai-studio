jQuery(document).ready(function($) {
    // Create project handler
    $('#tersuite-create-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#create-project-btn');
        btn.prop('disabled', true).text('Creating...');
        $.post(tersuiteData.apiUrl, {
            action: 'tersuite_create_project',
            nonce: tersuiteData.nonce,
            project_name: $('#project_name').val(),
            plugin_description: $('#plugin_description').val()
        }, function(response) {
            if (response.success) {
                $('#create-result').html('<div class="notice notice-success"><p>Project created! <a href="#">Open IDE</a></p></div>');
            } else {
                $('#create-result').html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? response.data.message : 'Failed') + '</p></div>');
            }
            btn.prop('disabled', false).text('Create Project');
        });
    });

    // Start agent pipeline
    $('.start-agents').on('click', function(e) {
        e.preventDefault();
        var projectId = $(this).data('project-id');
        $.post(tersuiteData.apiUrl, {
            action: 'tersuite_start_agents',
            nonce: tersuiteData.nonce,
            project_id: projectId,
            task: 'Generate plugin'
        }, function(response) {
            if (response.success) {
                alert('Agent pipeline started! Monitor progress in the IDE page.');
            } else {
                alert('Failed to start agents.');
            }
        });
    });

    // Load subscription status on dashboard
    $(document).ready(function() {
        $.post(tersuiteData.apiUrl, {
            action: 'tersuite_fetch_subscription',
            nonce: tersuiteData.nonce,
        }, function(response) {
            if (response.success && response.data) {
                var sub = response.data;
                var html = '<h3>Credits: ' + (sub.credits_remaining || 0) + '</h3>';
                html += '<p>Plan: ' + (sub.plan || 'None') + ' | Status: ' + (sub.status || 'N/A') + '</p>';
                if (sub.next_billing) html += '<p>Next billing: ' + sub.next_billing + '</p>';
                $('#subscription-status').html(html);
            } else {
                $('#subscription-status').html('<p>No active subscription. <a href="?page=tersuite-subscription">Subscribe</a></p>');
            }
        });
    });

    // Open IDE link with WebSocket streaming
    $('.open-ide').on('click', function(e) {
        e.preventDefault();
        var projectId = $(this).data('project-id');
        // Connect WebSocket to Django Channels for real-time agent streaming
        var wsUrl = (tersuiteData.streamUrl || 'ws://localhost:8000') + '/ws/progress/' + projectId + '/';
        if (tersuiteData.apiKey) wsUrl += '?token=' + encodeURIComponent(tersuiteData.apiKey);
        try {
            var socket = new WebSocket(wsUrl);
            socket.onopen = function() {
                console.log('Agent stream connected:', projectId);
                $('#agent-status').text('Streaming').addClass('live');
            };
            socket.onmessage = function(event) {
                var data = JSON.parse(event.data);
                if (data.type === 'agent_update') {
                    $('#agent-progress-text').text(data.agent + ': ' + data.status);
                    $('#agent-chat-box').append('<div class="chat-message agent"><strong>' + data.agent + ':</strong> ' + (data.message || data.status) + '</div>');
                }
            };
            socket.onclose = function() {
                console.log('Agent stream disconnected');
                $('#agent-status').text('Disconnected').removeClass('live');
            };
        } catch (err) {
            console.error('WebSocket error:', err);
        }
        // Open IDE page in new tab
        window.open('?page=tersuite-ide&project_id=' + projectId, '_blank');
    });
});

jQuery(document).on('click', '.download-plugin', function(e) {
    e.preventDefault();
    var projectId = jQuery(this).data('project-id');
    var form = jQuery('<form>', {method: 'POST', action: tersuiteData.apiUrl}).appendTo('body');
    form.append(jQuery('<input>', {type: 'hidden', name: 'action', value: 'tersuite_download_plugin'}));
    form.append(jQuery('<input>', {type: 'hidden', name: 'nonce', value: tersuiteData.nonce}));
    form.append(jQuery('<input>', {type: 'hidden', name: 'project_id', value: projectId}));
    form.trigger('submit');
});
