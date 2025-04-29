<?php

namespace App\Http\Controllers;

use App\Models\Chat;
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

        $chat=Chat::find($chat_id);

        if($chat->chatItems()->count()==0){
            $chat->update([
                'title' => substr($text, 0, 50),
            ]);
        }

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
            $instructions = "You are a helpful assistant specialized in providing information about insurance policies. Respond in Italian.";

            $messages = [
                ['role' => 'system', 'content' => $instructions],
                ['role' => 'user', 'content' => $request->input('text')],
            ];

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4.1', // Or your preferred model
                    'messages' => $messages,
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

    public function uploadAndProcess(Request $request)
    {
        $text = $request->input('text');
        $user_id = $request->input('user_id');
        $chat_id = $request->input('chat_id');
        $file = $request->file('file');

        $chat = Chat::find($chat_id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found.'], 404);
        }

        if ($chat->chatItems()->count() == 0) {
            $chat->update([
                'title' => substr($text ?? ($file ? $file->getClientOriginalName() : 'Nuova Chat'), 0, 50),
            ]);
        }

        $chatItem = ChatItem::create([
            'chat_id' => $chat_id,
            'text' => $text ?: 'File uploaded: ' . ($file ? $file->getClientOriginalName() : ''),
            'created_by' => $user_id,
        ]);

        $apiKey = env('OPENAI_API_KEY');

        try {
            $client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'OpenAI-Beta' => 'assistants=v2', // <-- QUESTO È OBBLIGATORIO!
                    'Content-Type' => 'application/json', // Consigliato sempre
                ],
            ]);

            $fileId = null;
            if ($file) {
                $fileUploadResponse = $client->request('POST', 'files', [
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => fopen($file->getRealPath(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                        ],
                        [
                            'name'     => 'purpose',
                            'contents' => 'assistants',
                        ],
                    ],
                ]);

                $uploadedFile = json_decode($fileUploadResponse->getBody(), true);
                $fileId = $uploadedFile['id'] ?? null;
            }

            // 1. Crea Assistant collegato al file
            $assistantResponse = $client->post('assistants', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'OpenAI-Beta' => 'assistants=v2',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => 'Analizzatore Polizze',
                    'instructions' => 'Sei un assistente esperto di polizze assicurative. Rispondi SEMPRE in italiano analizzando eventuali documenti allegati.',
                    'model' => 'gpt-4-1106-preview',
                    'tools' => [['type' => 'file_search']], // <-- solo strumenti qui
                ],
            ]);

            $assistant = json_decode($assistantResponse->getBody(), true);
            $assistantId = $assistant['id'] ?? null;

            if (!$assistantId) {
                throw new \Exception('Assistant creation failed.');
            }

            // 2. Crea nuova Thread con il messaggio dell'utente
            $threadResponse = $client->post('threads', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'OpenAI-Beta' => 'assistants=v2',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $text ?: 'Analizza il file caricato e forniscimi un riassunto.',
                            'attachments' => [
                                [
                                    'file_id' => $fileId, // <-- Il file caricato prima
                                    'tools' => [
                                        ['type' => 'file_search']
                                    ]
                                ]
                            ],
                        ],
                    ],
                ],
            ]);

            $thread = json_decode($threadResponse->getBody(), true);
            $threadId = $thread['id'] ?? null;

            if (!$threadId) {
                throw new \Exception('Thread creation failed.');
            }

            // 3. Avvia una Run (Assistant risponde nella Thread)
            $runResponse = $client->post("threads/{$threadId}/runs", [
                'json' => [
                    'assistant_id' => $assistantId,
                ],
            ]);

            $run = json_decode($runResponse->getBody(), true);
            $runId = $run['id'] ?? null;

            if (!$runId) {
                throw new \Exception('Run creation failed.');
            }

            // 4. Attendere la risposta: polling fino a completion
            $openaiAnswer = null;
            $tries = 0;
            while ($tries < 10) { // massimo 10 tentativi
                sleep(2); // aspetta 2 secondi

                $statusResponse = $client->get("threads/{$threadId}/runs/{$runId}");
                $status = json_decode($statusResponse->getBody(), true);

                if (isset($status['status']) && $status['status'] === 'completed') {
                    // Recupera i messaggi della thread
                    $messagesResponse = $client->get("threads/{$threadId}/messages");
                    $messagesBody = json_decode($messagesResponse->getBody(), true);

                    $messagesList = $messagesBody['data'] ?? [];

                    foreach ($messagesList as $msg) {
                        if ($msg['role'] === 'assistant') {
                            $openaiAnswer = $msg['content'][0]['text']['value'] ?? null;
                            break;
                        }
                    }
                    break;
                }

                $tries++;
            }

            if ($openaiAnswer) {
                $aiMessage = ChatItem::create([
                    'chat_id' => $chat_id,
                    'text' => $openaiAnswer,
                    'created_by' => null, // OpenAI bot
                ]);

                return response()->json([$chatItem, $aiMessage], 201);
            } else {
                return response()->json([$chatItem], 201);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OpenAI API Error: ' . $e->getMessage());
            return response()->json([$chatItem], 201);
        }
    }
}
