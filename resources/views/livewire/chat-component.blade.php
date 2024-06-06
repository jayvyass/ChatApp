<div class="h-full flex flex-col">
    <div class="flex-none w-full bg-blue-400 h-20 pt-2 text-white flex justify-between shadow-md items-center">
        <div class="flex items-center">
            
            <img src="{{ asset('storage/profile-images/' . $user->profile_image) }}" alt="{{ $user->name }} profile image" class="w-12 h-12 rounded-full ml-3">
            <div class="ml-4 font-bold text-lg tracking-wide text-gray-900">{{ $user->name }}</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="icon-dots-vertical w-8 h-8 mr-2">
            <path class="text-green-100 fill-current" fill-rule="evenodd" d="M12 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
        </svg>
    </div>
    <div class="flex-grow mt-5 mb-20 ml-4 overflow-y-auto">
        @php
            $currentDate = null;
            $lastTimestamp = null;
            $lastSender = null;
        @endphp
        @foreach ($messages as $message)
            @php
                $messageDate = \Carbon\Carbon::parse($message['created_at'])->toDateString();
                $messageTimestamp = \Carbon\Carbon::parse($message['created_at'])->format('Y-m-d H:i');
                $today = \Carbon\Carbon::today()->toDateString();
                $yesterday = \Carbon\Carbon::yesterday()->toDateString();
            @endphp
            @if ($currentDate != $messageDate)
                <div class="text-center my-2 text-black-600">
                    @if ($messageDate == $today)
                        Today
                    @elseif ($messageDate == $yesterday)
                        Yesterday
                    @else
                        {{ \Carbon\Carbon::parse($message['created_at'])->format('F d, Y') }}
                    @endif
                </div>
                @php
                    $currentDate = $messageDate;
                    $lastTimestamp = null;
                    $lastSender = null;
                @endphp
            @endif
            @if ($message['sender_id'] != auth()->user()->id)
                <div class="clearfix w-4/4">
                    @if ($lastTimestamp != $messageTimestamp || $lastSender != $message['sender_id'])
                        <div class="text-xs text-gray-600">{{ $message['sender'] }}, {{ \Carbon\Carbon::parse($message['formatted_time'])->format('H:i') }}</div>
                    @endif
                    <div class="bg-gray-300 mx-1 my-2 p-4 rounded-lg inline-block"style="max-width: 75%; width: auto;">
                    {!! nl2br(e($message['message'])) !!}
                    </div>
                </div>
            @else
                <div class="clearfix w-4/4">
                    <div class="text-right">
                        @if ($lastTimestamp != $messageTimestamp || $lastSender != $message['sender_id'])
                            <div class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($message['formatted_time'])->format('H:i') }}</div>
                        @endif
                        <div class="bg-blue-300 mx-1 my-2 p-4 rounded-lg clearfix inline-block" style="max-width: 75%; width: auto;">
                        {!! nl2br(e($message['message'])) !!}
                        </div>
                    </div>
                </div>
            @endif
            @php
                $lastTimestamp = $messageTimestamp;
                $lastSender = $message['sender_id'];
            @endphp
        @endforeach
    </div>
    <form wire:submit.prevent="sendMessage" class="absolute bottom-0  rounded-full left-0 w-full bg-blue-100">
        <div class="flex justify-between">
        <textarea id="messageInput" class="flex-grow m-2 py-2 px-4 mr-1 rounded-full border border-gray-300 bg-gray-200 resize-none" rows="1" wire:model.defer="message" placeholder="Message..." style="outline: none; white-space: pre-wrap;"></textarea>
                <button class="m-2" type="submit" style="outline: none;">
                    <svg class="svg-inline--fa text-blue-400 fa-paper-plane fa-w-16 w-12 h-12 py-2 mr-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="paper-plane" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor" d="M476 3.2L12.5 270.6c-18.1 10.4-15.8 35.6 2.2 43.2L121 358.4l287.3-253.2c5.5-4.9 13.3 2.6 8.6 8.3L176 407v80.5c0 23.6 28.5 32.9 42.5 15.8L282 426l124.6 52.2c14.2 6 30.4-2.9 33-18.2l72-432C515 7.8 493.3-6.8 476 3.2z"/>
                    </svg>
                </button>
        </div>
    </form>
</div>
