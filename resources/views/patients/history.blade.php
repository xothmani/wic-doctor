<div class="timeline">
    @forelse ($messages as $message)
        <div class="timeline-entry">
            <div class="timeline-time">
            <small>{{ \Carbon\Carbon::parse($message->created_at)->format('d/m/Y H:i') }}</small>
            </div>
            <div class="timeline-card">
                {{ $message->message }}
            </div>
        </div>
    @empty
        <div class="timeline-entry">
            <div class="timeline-time">Aucune heure</div>
            <div class="timeline-card">Aucun message trouvé.</div>
        </div>
    @endforelse
</div>
