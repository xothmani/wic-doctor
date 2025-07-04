{{-- Messenger Embed Widget --}}
<div id="messenger-embed-widget" class="messenger-embed-container">
    <div class="messenger-toggle-btn" onclick="toggleMessenger()">
        <i class="fas fa-comments"></i>
        <span class="messenger-unread-badge" id="messengerUnreadBadge" style="display: none;">0</span>
    </div>

    <div class="messenger-embed-popup" id="messengerPopup" style="display: none;">
        <div class="messenger-embed-header">
            <h5>{{ __('Messenger') }}</h5>
            <button type="button" class="btn-close" onclick="closeMessenger()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="messenger-embed-body">
            <messenger-app :embed-mode="true"></messenger-app>
        </div>
    </div>
</div>

<style>
    .messenger-embed-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }

    .messenger-toggle-btn {
        width: 60px;
        height: 60px;
        background: #0084ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 132, 255, 0.3);
        transition: all 0.3s ease;
        position: relative;
    }

    .messenger-toggle-btn:hover {
        background: #0073e6;
        transform: scale(1.1);
    }

    .messenger-unread-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }

    .messenger-embed-popup {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 380px;
        height: 500px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .messenger-embed-header {
        background: #0084ff;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .messenger-embed-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .btn-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .messenger-embed-body {
        height: calc(100% - 60px);
        overflow: hidden;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .messenger-embed-popup {
            width: calc(100vw - 40px);
            height: calc(100vh - 140px);
            bottom: 80px;
            right: -10px;
        }
    }
</style>

<script>
    let messengerOpen = false;

    function toggleMessenger() {
        const popup = document.getElementById('messengerPopup');
        const badge = document.getElementById('messengerUnreadBadge');

        if (messengerOpen) {
            closeMessenger();
        } else {
            openMessenger();
        }
    }

    function openMessenger() {
        const popup = document.getElementById('messengerPopup');
        popup.style.display = 'block';
        messengerOpen = true;

        // Hide unread badge when messenger is opened
        hideUnreadBadge();

        // Mark notifications as read
        markNotificationsAsRead();
    }

    function closeMessenger() {
        const popup = document.getElementById('messengerPopup');
        popup.style.display = 'none';
        messengerOpen = false;
    }

    function showUnreadBadge(count) {
        const badge = document.getElementById('messengerUnreadBadge');
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function hideUnreadBadge() {
        const badge = document.getElementById('messengerUnreadBadge');
        badge.style.display = 'none';
    }

    function markNotificationsAsRead() {
        fetch('{{ route("messenger.notifications.mark-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).catch(error => {
            console.error('Error marking notifications as read:', error);
        });
    }

    // Check for unread messages periodically
    function checkUnreadMessages() {
        fetch('{{ route("messenger.notifications.count") }}')
            .then(response => response.json())
            .then(data => {
                if (!messengerOpen) {
                    showUnreadBadge(data.count);
                }
            })
            .catch(error => {
                console.error('Error checking unread messages:', error);
            });
    }

    // Check every 30 seconds
    //setInterval(checkUnreadMessages, 30000);

    // Initial check
    document.addEventListener('DOMContentLoaded', function () {
        checkUnreadMessages();
    });

    // Close messenger when clicking outside
    document.addEventListener('click', function (event) {
        const container = document.getElementById('messenger-embed-widget');
        const popup = document.getElementById('messengerPopup');

        if (messengerOpen && !container.contains(event.target)) {
            closeMessenger();
        }
    });

    // Handle escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && messengerOpen) {
            closeMessenger();
        }
    });
</script>