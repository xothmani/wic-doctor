{{-- Messenger Navigation Link --}}
<li class="nav-item">
    <a href="{{ route('messenger.index') }}" class="nav-link {{ request()->routeIs('messenger.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-comments"></i>
        <p>
            {{ __('Messenger') }}
            <span class="right badge badge-danger" id="messenger-nav-badge" style="display: none;">0</span>
        </p>
    </a>
</li>

<script>
    // Update navigation badge with unread message count
    function updateMessengerNavBadge() {
        fetch('{{ route("messenger.notifications.count") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('messenger-nav-badge');
                if (data.count && data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching unread message count:', error);
            });
    }

    // Update badge every 30 seconds
    //setInterval(updateMessengerNavBadge, 30000);

    // Initial load
    document.addEventListener('DOMContentLoaded', function () {
        updateMessengerNavBadge();
    });
</script>