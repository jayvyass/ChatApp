<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class UserList extends Component
{
    public $sortedUsers = [];
    public $lastMessages = [];
    public $deliveredCounts = [];

    public function mount()
    {
        $this->loadUserData();
    }

    public function loadUserData()
    {
        $currentUser = Auth::user();
        $users = User::where('id', '!=', $currentUser->id)->get();

        $this->sortedUsers = [];
        $this->lastMessages = [];
        $this->deliveredCounts = [];

        foreach ($users as $user) {
            $lastMessage = Message::where(function ($query) use ($user, $currentUser) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id)
                      ->where('mstatus', 'active');
            })->orWhere(function ($query) use ($user, $currentUser) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id)
                      ->where('mstatus', 'active');
            })->orderBy('created_at', 'desc')->first();

            $formattedTime = $lastMessage ? $lastMessage->created_at->setTimezone('Asia/Kolkata') : null;
            $currentDate = now()->setTimezone('Asia/Kolkata');

            $formattedTime = $formattedTime ? (
                $formattedTime->isSameDay($currentDate) ? $formattedTime->format('H:i') : $formattedTime->format('D')
            ) : 'No messages yet';

            $this->lastMessages[$user->id] = [
                'message' => $lastMessage,
                'formatted_time' => $formattedTime,
            ];

            $deliveredCount = Message::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('status', 'delivered')
                ->where('mstatus', 'active')
                ->count();

            $this->deliveredCounts[$user->id] = $deliveredCount;

            $this->sortedUsers[] = [
                'user' => $user,
                'delivered_count' => $deliveredCount,
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
            ];
        }

        // Sort users by notification count and latest message time
        usort($this->sortedUsers, fn($a, $b) => $b['delivered_count'] <=> $a['delivered_count'] ?: $b['last_message_time'] <=> $a['last_message_time']);
    }

    public function render()
    {
        return view('livewire.user-list');
    }
}
