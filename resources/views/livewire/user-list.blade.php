<div class="w-1/3 bg-grey shadow-sm sm:rounded-lg h-full overflow-y-auto border-custom" style="height: 100%;">
    <div class="p-6 text-gray-900">
        @foreach ($sortedUsers as $index => $sortedUser)
            @php
                $user = $sortedUser['user'];
            @endphp
            <div id="user-{{ $user->id }}" class="user-item mt-5 flex items-center p-2" data-user-id="{{ $user->id }}">
                <img src="{{ asset('storage/profile-images/' . $user->profile_image) }}" alt="{{ $user->name }} profile image" class="w-16 h-16 rounded-full mr-2 object-fit">
                <a href="javascript:void(0);" onclick="openChat({{ $user->id }})" class="flex-grow">
                    <div class="flex items-center justify-between">
                        <span class="text-m font-bold">{{ Str::limit($user->name, 18) }}</span>
                        @if (($deliveredCounts[$user->id] ?? 0) > 0)
                            <div class="notification-circle ml-2">
                                {{ $deliveredCounts[$user->id] }}
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        @if (isset($lastMessages[$user->id]) && isset($lastMessages[$user->id]['message']))
                            @if ($lastMessages[$user->id]['message']->image)
                                <span class="mt-1 text-xs text-gray-500">
                                    Image
                                </span>
                            @else
                                <span class="mt-1 text-xs text-gray-500">
                                    {{ Str::limit($lastMessages[$user->id]['message']->message, 14) }}
                                </span>
                            @endif
                            <div class="ml-auto mt-1 text-xxs text-black-400" style="font-size: 12px;">
                                {{ $lastMessages[$user->id]['formatted_time'] }}
                            </div>
                        @else
                            <span class="text-xs text-gray-500">No Messages</span>
                            <div class="ml-auto mt-7 text-xxs text-black-400" style="font-size: 12px;"></div>
                        @endif
                    </div>
                </a>
            </div>
            @if ($index == 0)
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        displayWelcomeMessage();
                    });
                </script>
            @endif
        @endforeach
    </div>
</div>
