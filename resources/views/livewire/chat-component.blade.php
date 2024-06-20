<div class="h-full flex flex-col">
    <div class="flex-none w-full bg-blue-400 h-20 pt-2 text-white flex justify-between shadow-md items-center">
        <div class="flex items-center">
            <img src="{{ asset('storage/profile-images/' . $user->profile_image) }}" alt="{{ $user->name }} profile image" class="w-12 h-12 rounded-full ml-3 object-cover">
            <div class="ml-4 font-bold text-lg tracking-wide text-gray-900">{{ $user->name }}</div>
        </div>
        <div class="relative" x-data="{ open: false }">
            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="icon-dots-vertical w-8 h-8 mr-2 cursor-pointer">
                <path class="text-green-100 fill-current" fill-rule="evenodd" d="M12 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
            </svg>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-blue-100 rounded-md shadow-lg py-2 z-50">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="document.getElementById('deleteModal').classList.remove('hidden')">Delete Chat</a>
            </div>
        </div>
    </div>
    <div class="flex-grow mt-5 mb-20 ml-4 overflow-y-auto" id="chat-messages">
    @php
        $currentDate = null;
        $lastTimestamp = null;
        $lastSender = null;
        $lastMessageSentByUser = null;
    @endphp
<div wire:poll>
    @foreach ($messages as $key => $message)
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
                $lastMessageSentByUser = null;
            @endphp
        @endif

        @if ($message['sender_id'] != auth()->user()->id)
            <div class="clearfix w-4/4">
                @if ($lastTimestamp != $messageTimestamp || $lastSender != $message['sender_id'])
                    <div class="text-xs text-gray-600">{{ $message['sender'] }}, {{ \Carbon\Carbon::parse($message['formatted_time'])->format('H:i') }}</div>
                @endif
                <div class="bg-gray-300 mx-1 my-2 p-4 rounded-lg inline-block" style="max-width: 75%; width: auto;">
                    {!! nl2br(e($message['message'])) !!}
                    @if ($message['image'])
                        <img src="{{ asset('storage/profile-images/' . $message['image']) }}" alt="Chat Image" class="max-w-xs rounded-lg">
                    @endif
                </div>
            </div>
            @else
                <div class="clearfix w-4/4">
                    <div class="text-right relative" x-data="{ open: false }">
                        @if ($lastTimestamp != $messageTimestamp || $lastSender != $message['sender_id'])
                            <div class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($message['formatted_time'])->format('H:i') }}</div>
                        @endif
                        <div class="bg-blue-300 mx-1 my-2 p-4 rounded-lg clearfix inline-block relative" style="max-width: 75%; width: auto;" @click="open = !open">
                            {!! nl2br(e($message['message'])) !!}
                            @if ($message['image'])
                                <img src="{{ asset('storage/profile-images/' . $message['image']) }}" alt="Chat Image" class="max-w-xs rounded-lg">
                            @endif
                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-32 bg-blue-100 rounded-md shadow-lg py-2 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" wire:click="editMessage({{ $message['id'] }})">Edit</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" wire:click="unsendMessage({{ $message['id'] }})">Unsend</a>
                            </div>
                        </div>
                        @if ($key == count($messages) - 1)
                           <div class="text-xs text-gray-500 my-2 mt-1">{{ $message['status'] }}</div>
                        @endif
                    </div>
                </div>
            @php
                $lastMessageSentByUser = $message;
            @endphp
        @endif

        @php
            $lastTimestamp = $messageTimestamp;
            $lastSender = $message['sender_id'];
        @endphp
        
    @endforeach
    </div>
 </div>
    <!-- Imagwe preview -->
    @if ($photo)
        <div class="mb-10">
            <img src="{{ $photo->temporaryUrl() }}" alt="Image Preview" class="w-20 h-20 object-cover rounded-lg">
        </div>
    @endif

    <form id="messageForm" wire:submit.prevent="sendMessage" class="absolute bottom-0 rounded-full left-0 w-full bg-blue-100">
        <div class="flex justify-between items-center">
            <textarea id="messageInput" class="flex-grow m-2 py-2 px-4 mr-1 rounded-full border border-gray-300 bg-gray-200 resize-none" rows="1" wire:model.defer="message" placeholder="Message..." style="outline: none; white-space: pre-wrap;">{{ $messageToEdit }}</textarea>
            <!-- Gallery Upload Button -->
            <div class="m-2 relative">
                <input type="file" id="galleryUpload" wire:model="photo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                <svg class="svg-inline--fa text-blue-400 fa-image fa-w-16 w-12 h-12 py-2 mr-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="image" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="currentColor" d="M464 448H48c-26.51 0-48-21.49-48-48V112c0-26.51 21.49-48 48-48h416c26.51 0 48 21.49 48 48v288c0 26.51-21.49 48-48 48zM48 80c-17.67 0-32 14.33-32 32v288c0 17.67 14.33 32 32 32h416c17.67 0 32-14.33 32-32V112c0-17.67-14.33-32-32-32H48zm128 96c26.51 0 48 21.49 48 48s-21.49 48-48 48-48-21.49-48-48 21.49-48 48-48zm220.71 221.14l-101.35-136c-4.68-6.27-14.7-6.27-19.38 0l-101.35 136C168.71 405.9 175.19 416 184 416h144c8.81 0 15.29-10.1 12.71-18.86z"></path>
                </svg>
            </div>
            <button class="m-2" type="submit" style="outline: none;">
                <svg class="svg-inline--fa text-blue-400 fa-paper-plane fa-w-16 w-12 h-12 py-2 mr-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="paper-plane" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="currentColor" d="M476 3.2L12.5 270.6c-18.1 0-15.8 35.6 2.2 43.2L121 358.4l287.3-253.2c5.5-4.9 13.3 2.6 8.6 8.3L176 407v80.5c0 23.6 28.5 32.9 42.5 15.8L282 426l124.6 52.2c14.2 6 30.4-2.9 33-18.2l72-432C515 7.8 493.3-6.8 476 3.2z"></path>
                </svg>
            </button>
        </div>
    </form>
   
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden absolute inset-0 flex justify-center items-center">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <!-- Modal content -->
            <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <button type="button" class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="deleteModal" @click="document.getElementById('deleteModal').classList.add('hidden')">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <svg class="text-gray-400 dark:text-gray-500 w-11 h-11 mb-3.5 mx-auto" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 000-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 01-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 002 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this chat?</p>
                <div class="flex justify-center items-center space-x-4">
                    <button data-modal-toggle="deleteModal" type="button" class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600" @click="document.getElementById('deleteModal').classList.add('hidden')">
                        No, cancel
                    </button>
                    <button type="submit" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900" wire:click="deleteChat" @click="document.getElementById('deleteModal').classList.add('hidden')">
                        Yes, I'm sure
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
    const messageInput = document.getElementById('messageInput');
    const messageForm = document.getElementById('messageForm');
    const chatMessages = document.getElementById('chat-messages');

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    messageInput.addEventListener('keydown', function(event) {
        if (event.keyCode === 13 && !event.shiftKey) {
            // Prevent the default action of Enter key (adding a newline)
            event.preventDefault();
            // Trigger the form submission
            messageForm.dispatchEvent(new Event('submit'));
            scrollToBottom();
        }
    });
    scrollToBottom();

    messageForm.addEventListener('submit', function(event) {
        event.preventDefault();
        @this.sendMessage().then(() => {
            messageInput.value = '';
            scrollToBottom();
        });
    });

    // Listen for the broadcast event
    Echo.private('chat-channel.{{ auth()->user()->id }}')
        .listen('MessageSendEvent', (e) => {
            addMessageToChatWindow(e.message);
            scrollToBottom(); 
        });

    function addMessageToChatWindow(message) {
        // Create a message element
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.textContent = message.content; // Add the message content
        chatMessages.appendChild(messageElement);
        scrollToBottom();
    }   

    // Scroll to the bottom on page load
    document.addEventListener('DOMContentLoaded', function() {
        scrollToBottom();
    });
</script>


