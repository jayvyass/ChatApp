<x-app-layout>
    <!-- Page Heading -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Page Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex h-screen">
            <!-- User List Section (30% width) -->
            <div id="user-list" class="w-1/3 bg-grey shadow-sm sm:rounded-lg h-full overflow-y-auto border-custom" style="height: 100%;">
                <div class="p-6 text-gray-900">
                    @foreach ($users as $index => $user)
                        <div id="user-{{ $user->id }}" class="user-item mt-10 flex items-center p-3" data-user-id="{{ $user->id }}"> 
                            <img src="{{ asset('storage/profile-images/' . $user->profile_image) }}" alt="{{ $user->name }} profile image" class="w-16 h-16 rounded-full mr-4">
                            <a href="javascript:void(0);" onclick="openChat({{ $user->id }})">
                                <span class="ml-4 text-xl font-bold">{{ $user->name }}</span>
                            </a>
                        </div>
                        @if ($index == 0)
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    openChat({{ $user->id }});
                                });
                            </script>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Chat Section (70% width) -->
            <div class="w-2/3 bg-white shadow-sm sm:rounded-lg h-full overflow-hidden relative" id="chat-container" style="height: 100%;">
                <div class="text-gray-900 h-full overflow-y-auto" id="chat-content">
                    @livewire('chat-component', ['user_id' => $id])
                </div>
                <!-- Send Message Form -->
                <form wire:submit.prevent="sendMessage" class="absolute bottom-0 rounded-full left-0 w-full bg-blue-100 p-4">
                    <!-- Your form elements here -->
                </form>
            </div>
        </div>
    </div>

    <script>
        let activeUser = null;

        function openChat(userId) {
            if (activeUser) {
                document.getElementById('user-' + activeUser).classList.remove('active-user');
            }
            document.getElementById('user-' + userId).classList.add('active-user');
            activeUser = userId;

            fetch(`/chat/${userId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('chat-content').innerHTML = html;
                });
        }
    </script>
</x-app-layout>

<style>
    .border-custom {
        border-top: 4px solid skyblue;
        border-right: 4px solid skyblue;
        border-bottom: 4px solid skyblue;
        border-left: 4px solid skyblue;
    }
    /* .user-item:hover {
        background-color: lightgray;
        border-radius: 30px;
    } */
    .active-user {
        background-color:skyblue ;
        border-radius: 30px;
        color: black;
    }
    .user-item img {
        width: 48px; 
        height: 48px; 
    }
    .user-item span {
        font-size: 1.5rem; 
        font-weight: bold;
    }
</style>
