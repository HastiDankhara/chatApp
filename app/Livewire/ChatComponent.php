<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Message;
use Livewire\Component;
use App\Events\MessageSendEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ChatComponent extends Component
{
    public $user;
    public $sender_id;
    public $receiver_id;
    public $message = '';
    public $messages = [];
    public function render()
    {
        return view('livewire.chat-component');
    }
    public function mount($user_id)
    {
        // dd($user_id);
        $this->sender_id = Auth::user()->id;
        $this->receiver_id = $user_id;
        $this->user = User::where('id', $user_id)->first();
        $messages = Message::where(function ($query) {
            $query->where('sender_id', $this->sender_id)
                ->where('receiver_id', $this->receiver_id);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->receiver_id)
                ->where('receiver_id', $this->sender_id);
        })->with('sender:id,name', 'receiver:id,name')->get();
        // dd($messages);
        foreach ($messages as $message) {
            $this->appendMessage($message);
        }
    }
    public function sendMessage()
    {
        // dd($this->message);
        $this->validate([
            'message' => 'required|string|max:255',
        ]);

        $msg = new Message();
        $msg->sender_id = $this->sender_id;
        $msg->receiver_id = $this->receiver_id;
        $msg->message = $this->message;
        $msg->save();
        $this->appendMessage($msg);
        broadcast(new MessageSendEvent($msg))->toOthers();
        $this->message = '';
        // $this->dispatch('messageSent');
    }
    #[On('echo-private:chat-channel.{sender_id},MessageSendEvent')]
    public function listenForMessage($event)
    {
        $msg = Message::whereId($event['message']['id'])->with('sender:id,name', 'receiver:id,name')->first();
        $this->appendMessage($msg);
        // $this->dispatch('messageReceived');
    }
    public function appendMessage($message)
    {
        $this->messages[] = [
            'id' => $message->id,
            'message' => $message->message,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'receiver_id' => $message->receiver_id,
            'receiver_name' => $message->receiver->name,
        ];
    }
}
