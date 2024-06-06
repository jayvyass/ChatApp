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
            <div class="w-1/3 bg-grey shadow-sm sm:rounded-lg h-full overflow-y-auto border-custom" style="height: 100%;">
                <div class="p-6 text-gray-900">
                    @foreach ($users as $index => $user)
                        <div class="mt-10 flex items-center"> 
                            <img src="{{ asset('storage/profile-images/' . $user->profile_image) }}" alt="{{ $user->name }} profile image" class="w-10 h-10 rounded-full mr-2">
                            <a href="javascript:void(0);" onclick="openChat({{ $user->id }})">
                                <span class="ml-2 text-lg bold">{{ $user->name }}</span>
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
            </div>
        </div>
    </div>

    <script>
        function openChat(userId) {
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
        border-top: 4px solid lightskyblue;
        border-right: 4px solid skyblue;
        border-bottom: 4px solid skyblue;
        border-left: 4px solid skyblue;
    }
</style>
