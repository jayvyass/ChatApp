<?php

use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

Route::get('/dashboard', function() {
    $users = User::where('id', '!=', auth()->user()->id)->get();

    // Fetch last messages and count of delivered messages for each user
    $lastMessages = [];
    $deliveredCounts = [];
    $sortedUsers = [];

    foreach ($users as $user) {
        $lastMessage = Message::where(function($query) use ($user) {
            $query->where('sender_id', auth()->user()->id)
                  ->where('receiver_id', $user->id)
                  ->where('mstatus', 'active');
        })->orWhere(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', auth()->user()->id);
        })->orderBy('created_at', 'desc')->first();

        if ($lastMessage) {
            $formattedTime = $lastMessage->created_at->setTimezone('Asia/Kolkata');
            $currentDate = now()->setTimezone('Asia/Kolkata');

            // Check if the message is from yesterday or earlier
            if ($formattedTime->isSameDay($currentDate)) {
                $formattedTime = $formattedTime->format('H:i ');
            } else {
                $formattedTime = $formattedTime->format('D');
            }

            $lastMessages[$user->id] = [
                'message' => $lastMessage,
                'formatted_time' => $formattedTime,
            ];
        } else {
            $lastMessages[$user->id] = [
                'message' => null,
                'formatted_time' => 'No messages yet',
            ];
        }

        // Count delivered messages for each user
        $deliveredCount = Message::where('sender_id', $user->id)
            ->where('receiver_id', auth()->user()->id)
            ->where('status', 'delivered')
            ->where('mstatus', 'active')
            ->count();

        $deliveredCounts[$user->id] = $deliveredCount;

        // Add user to sortedUsers array with notification count and latest message time
        $sortedUsers[] = [
            'user' => $user,
            'delivered_count' => $deliveredCount,
            'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
        ];
    }

    // Sort users by notification count and latest message time
    usort($sortedUsers, function($a, $b) {
        if ($a['delivered_count'] === $b['delivered_count']) {
            return $b['last_message_time'] <=> $a['last_message_time'];
        }
        return $b['delivered_count'] <=> $a['delivered_count'];
    });

    return view('dashboard', [
        'sortedUsers' => $sortedUsers,
        'lastMessages' => $lastMessages,
        'deliveredCounts' => $deliveredCounts,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/chat/{id}', function($id) {
    return view('chat', [
        'id' => $id
    ]);
})->middleware(['auth', 'verified'])->name('chat');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
Route::view('/', 'welcome');
require __DIR__.'/auth.php';
