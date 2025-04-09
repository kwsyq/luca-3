<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create()->each(function ($user) {
            // Create 2-3 chats for each user
            Chat::factory(rand(2, 3))->create(['user_id' => $user->id])->each(function ($chat) use ($user) {
                // Create 3-4 chat items for each chat
                ChatItem::factory(rand(3, 4))->create([
                    'chat_id' => $chat->id,
                    'created_by' => $user->id,
                ]);
            });
        });
    }
}
