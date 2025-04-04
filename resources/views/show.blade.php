<div id="messagesContainer">
    @if($messages && count($messages) > 0)
        @foreach($messages as $message)
            <div class="message">
                <strong>{{ $message['sender_name'] }}:</strong> {{ $message['content'] }}
            </div>
        @endforeach
    @else
        <p>No messages available</p>
    @endif
</div>
