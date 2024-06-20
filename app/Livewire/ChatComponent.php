<?php

namespace App\Livewire;

use App\Events\MessageSendEvent;
use App\Models\User;
use Livewire\Component;
use App\Models\Message;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
class ChatComponent extends Component
{
    use WithFileUploads;

    public $user;
    public $sender_id;
    public $receiver_id;
    public $message = '';
    public $messages = [];
    public $activeUserId;
    public $messageToEdit;
    public $editedMessageId;
    public $photo;

    public function render()
    {
        return view('livewire.chat-component');
    }

    public function mount($user_id)
    {
        $this->activeUserId = $user_id;
        $this->sender_id = auth()->user()->id;
        $this->receiver_id = $user_id;
        $messages = Message::where(function($query) {
            $query->where('sender_id', $this->sender_id)
                  ->where('receiver_id', $this->receiver_id);
        })->orWhere(function($query) {
            $query->where('sender_id', $this->receiver_id)
                  ->where('receiver_id', $this->sender_id);
        })->where('mstatus', 'active')
          ->with('sender:id,name', 'receiver:id,name')->get();

        foreach ($messages as $message) {
            $this->appendChatMessage($message);
        }
        $this->markMessagesAsSeen();
        $this->user = User::findOrFail($user_id);
    }

    public function sendMessage()
    {
        $trimmedMessage = trim($this->message);
        if (empty($trimmedMessage) && !$this->photo) {
            return;
        }

        if ($this->editedMessageId) {
            $message = Message::find($this->editedMessageId);
            if ($message) {
                $message->message = $this->message;
                if ($this->photo) {
                    $message->image = $this->photo->store('chat-images', 'public');
                }
                $message->save();
            }
            $this->editedMessageId = null;
        } else {
            $chatMessage = new Message();
            $chatMessage->sender_id = $this->sender_id;
            $chatMessage->receiver_id = $this->receiver_id;
            $chatMessage->message = $this->message;
            $chatMessage->mstatus = 'active';
            if ($this->photo) {
                $chatMessage->image = $this->photo->store('chat-images', 'public');
            }
            $chatMessage->save();
            $this->appendChatMessage($chatMessage);
            broadcast(new MessageSendEvent($chatMessage))->toOthers();
        }
        
        $this->message = '';
        $this->photo = null;
    }

    public function editMessage($messageId)
    {
        $this->editedMessageId = $messageId;
        $message = Message::find($messageId);
        if ($message) {
            $this->message = $message->message;
        }
    }

    #[on('echo-private:chat-channel.{sender_id},MessageSendEvent')]
    public function listenForMessage($event)
    {
        $chatMessage = Message::whereId($event['message']['id'])
        ->with('sender:id,name', 'receiver:id,name')
        ->first();
    if ($chatMessage && $chatMessage->sender_id == $this->receiver_id && $chatMessage->receiver_id == $this->sender_id) {
        $this->appendChatMessage($chatMessage);
    }
}

    public function appendChatMessage($message)
    {
        if ($message->mstatus !== 'active') {
            return;
        }

        $formattedTime = $message->created_at->setTimezone('Asia/Kolkata')->format('g:i A');

        $this->messages[] = [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender' => $message->sender->name,
            'receiver' => $message->receiver->name,
            'message' => $message->message,
            'image' => $message->image,
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

    public function clearChat()
    {
        $this->messages = [];
    }

    public function deleteChat()
    {
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
