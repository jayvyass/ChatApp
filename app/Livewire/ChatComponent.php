<?php

namespace App\Livewire;
use App\Events\MessageSendEvent;
use App\Models\User;
use Livewire\Component;
use App\Models\Message;
use Livewire\Attributes\On;
class ChatComponent extends Component
{
    public $user;
    public $sender_id;
    public $receiver_id;
    public $message = '';
    public $messages=[];
    public $activeUserId;
    public function render()
    {
        return view('livewire.chat-component');
        
    }

    public function mount($user_id){
        $this->activeUserId = $user_id;
        $this->sender_id = auth()->user()->id;
        $this->receiver_id = $user_id;
        $messages = Message::where(function($query) {
            $query->where('sender_id', $this->sender_id)
                  ->where('receiver_id', $this->receiver_id);
        })->orWhere(function($query) {
            $query->where('sender_id', $this->receiver_id)
                  ->where('receiver_id', $this->sender_id);
        })->with('sender:id,name', 'receiver:id,name')->get();

        foreach ($messages as $message) {
            $this->appendChatMessage($message);
        }
        $this->markMessagesAsSeen();
        $this->user = User::findOrFail($user_id);
    }
    public function sendMessage(){
        // Trim the message to remove leading and trailing whitespace
        $trimmedMessage = trim($this->message);
        if (empty($trimmedMessage)) {
            return;
        }
        $chatMessage = new Message();
        $chatMessage->sender_id = $this->sender_id;
        $chatMessage->receiver_id = $this->receiver_id;
        $chatMessage->message = $this->message;
        $chatMessage->save();

        $this->appendChatMessage($chatMessage);

        broadcast(new MessageSendEvent($chatMessage))->toOthers();
        $this->message = '';
    }


    #[on('echo-private:chat-channel.{sender_id},MessageSendEvent')]
    public function listenForMessage($event){
        $chatMessage = Message::whereId($event['message']['id'])
            ->with('sender:id,name','receiver:id,name')
            ->first();
        $this->appendChatMessage($chatMessage);
    }
    

    public function appendChatMessage($message) {
        $formattedTime = $message->created_at->setTimezone('Asia/Kolkata')->format('g:i A');
    
        $this->messages[] = [
            'id' => $message->id,
            'sender_id' => $message->sender_id, 
            'sender' => $message->sender->name,
            'receiver' => $message->receiver->name,
            'message' => $message->message,
            'created_at' => $message->created_at, 
            'formatted_time' => $formattedTime,
            'status'=> $message->status,
        ];
    }
    public function markMessagesAsSeen()
    {
        Message::where('receiver_id', $this->sender_id)
            ->where('sender_id', $this->receiver_id)
            ->where('status', '!=', 'seen')
            ->update(['status' => 'seen']);
    }

}
