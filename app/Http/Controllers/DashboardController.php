<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $users = User::where('id', '!=', $currentUser->id)->get();

        // Fetch last messages and count of delivered messages for each user
        $lastMessages = [];
        $deliveredCounts = [];
        $sortedUsers = [];

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

            $lastMessages[$user->id] = [
                'message' => $lastMessage,
                'formatted_time' => $formattedTime,
            ];

            $deliveredCount = Message::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('status', 'delivered')
                ->where('mstatus', 'active')
                ->count();

            $deliveredCounts[$user->id] = $deliveredCount;

            $sortedUsers[] = [
                'user' => $user,
                'delivered_count' => $deliveredCount,
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
            ];
        }

        // Sort users by notification count and latest message time
        usort($sortedUsers, fn($a, $b) => $b['last_message_time'] <=> $a['last_message_time']);

        return view('dashboard', compact('sortedUsers', 'lastMessages', 'deliveredCounts'));
    }

    public function chat($id)
    {
        return view('chat', compact('id'));
    }
}
