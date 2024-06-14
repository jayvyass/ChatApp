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
    public $messageToEdit;
    public $editedMessageId;

   
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
                })->where('mstatus', 'active')  // Only fetch active messages
                ->with('sender:id,name', 'receiver:id,name')->get();

        foreach ($messages as $message) {
            $this->appendChatMessage($message);
        }
        $this->markMessagesAsSeen();
        $this->user = User::findOrFail($user_id);
        
    }

    public function sendMessage() {
        // Trim the message to remove leading and trailing whitespace
        $trimmedMessage = trim($this->message);
        if (empty($trimmedMessage)) {
            return;
        }
        
        if ($this->editedMessageId) {
            // Update the existing message
            $message = Message::find($this->editedMessageId);
            if ($message) {
                $message->message = $this->message;
                $message->save();
            }
            
            // Clear the editing state
            $this->editedMessageId = null;
        } else {
            // Create a new message
            $chatMessage = new Message();
            $chatMessage->sender_id = $this->sender_id;
            $chatMessage->receiver_id = $this->receiver_id;
            $chatMessage->message = $this->message;
            $chatMessage->mstatus = 'active';
            $chatMessage->save();

            $this->appendChatMessage($chatMessage);
        }

        // Clear the message input
        $this->message = '';
        
    }

    public function editMessage($messageId){
        $this->editedMessageId = $messageId;
        $message = Message::find($messageId);
        if ($message) {
            $this->message = $message->message;
        }
    }


    #[on('echo-private:chat-channel.{sender_id},MessageSendEvent')]
    public function listenForMessage($event){
        $chatMessage = Message::whereId($event['message']['id'])
            ->with('sender:id,name','receiver:id,name')
            ->first();
        $this->appendChatMessage($chatMessage);
    }
    public function appendChatMessage($message) {
        if ($message->mstatus !== 'active') {
            return;  // Skip messages that are not active
        }
        
        $formattedTime = $message->created_at->setTimezone('Asia/Kolkata')->format('g:i A');
    
        $this->messages[] = [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender' => $message->sender->name,
            'receiver' => $message->receiver->name,
            'message' => $message->message,
            'created_at' => $message->created_at,
            'formatted_time' => $formattedTime,
            'status' => $message->status,
        ];
    }
    

    public function markMessagesAsSeen()
    {
        Message::where('receiver_id', $this->sender_id)
            ->where('sender_id', $this->receiver_id)
            ->where('status', '!=', 'seen')
            ->update(['status' => 'seen']);
    }

    public function clearChat() {
        $this->messages = [];
    }

    public function deleteChat() {
        Message::where(function($query) {
            $query->where('sender_id', $this->sender_id)
                  ->where('receiver_id', $this->receiver_id);
        })->orWhere(function($query) {
            $query->where('sender_id', $this->receiver_id)
                  ->where('receiver_id', $this->sender_id);
        })->update(['mstatus' => 'away']);
    
        $this->clearChat();
    }
    
    public function unsendMessage($messageId){
        $message = Message::where('id', $messageId)
            ->where('sender_id', $this->sender_id)
            ->first();

        if ($message) {
            $message->update(['mstatus' => 'away']);
            $this->messages = array_filter($this->messages, function ($msg) use ($messageId) {
                return $msg['id'] !== $messageId;
            });
        }
    }   

}
