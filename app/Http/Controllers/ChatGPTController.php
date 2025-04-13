<?php

namespace App\Http\Controllers;

use App\Models\ChatItem;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use OpenAI;

class ChatGPTController extends Controller
{
    public function getSimpleAnswer(Request $request)
    {

        $text = $request->input('text');
        $user_id = $request->input('user_id');
        $chat_id = $request->input('chat_id');

        $chatItem = ChatItem::create([
            'chat_id' => $chat_id,
            'text' => $text,
            'created_by' => $user_id,
        ]);

        $apiKey = env('OPENAI_API_KEY');
        
logger($apiKey);

        // Send the user's message to OpenAI and get the response
        try {
            $client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'OpenAI-Beta' => 'assistants=v2',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4o', // Or your preferred model
                    'messages' => [
                        ['role' => 'user', 'content' => $request->input('text')],
                    ],
                ],
            ]);

            $body = json_decode($response->getBody());
            $openaiAnswer = $body->choices[0]->message->content ?? null;

            if ($openaiAnswer) {
                $aiMessage = ChatItem::create([
                    'chat_id' => $chat_id,
                    'text' => $openaiAnswer,
                    'created_by' => null, // Or a specific user ID for the AI
                ]);

                return response()->json([$chatItem, $aiMessage], 201);
            } else {
                // Handle the case where no AI response is received
                return response()->json([$chatItem], 201);
            }

        } catch (\Exception $e) {
            // Handle OpenAI API errors (e.g., rate limits, connection issues)
            \Illuminate\Support\Facades\Log::error('OpenAI API Error: ' . $e->getMessage());

            // Optionally, you can still return the user's message
            return response()->json([$chatItem], 201);
            // Or, you might want to return an error message to the frontend
            // return response()->json(['error' => 'Failed to get response from AI.'], 500);
        }

    }
}
