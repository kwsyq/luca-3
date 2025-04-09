<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ChatGPT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:chat-g-p-t';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

//        $filePath = storage_path('app/Unipol.pdf'); // or .txt, .docx, etc.

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'OpenAI-Beta' => 'assistants=v2',
                'Content-Type' => 'application/json',
            ],
        ]);

        // $uploadResponse = $client->request('POST', 'files', [
        //     'multipart' => [
        //         [
        //             'name'     => 'file',
        //             'contents' => fopen($filePath, 'r'),
        //         ],
        //         [
        //             'name'     => 'purpose',
        //             'contents' => 'assistants',
        //         ],
        //     ],
        // ]);

        // $fileId = json_decode($uploadResponse->getBody(), true)['id'];
        //'instructions' => 'Questo GPT è uno strumento specializzato per assicuratori, progettato per aiutare nella gestione e nell\'analisi delle polizze assicurative. Può elaborare documenti caricati dall\'utente e offrire supporto nelle seguenti attività:\n\n- **Descrizione per il cliente**: Quando l\'utente carica una polizza, il GPT genera una descrizione chiara e professionale in formato email da inviare al cliente, adattando il tono in base a quello fornito.\n- **Estratto informazioni**: Dopo il caricamento di una polizza, estrae tutte le informazioni chiave in maniera ordinata per facilitarne la valutazione.\n- **Riscrittura email**: Migliora bozze di email fornite dall\'utente, mantenendo il significato originale ma rendendole più professionali e scorrevoli, adattandosi al tono fornito dall\'utente.\n- **Riassunto documentazione**: Quando l\'utente carica documentazione relativa a una nuova polizza, direttiva o normativa, il GPT la riassume e la spiega in modo chiaro, come farebbe un insegnante esperto nel settore assicurativo, strutturando i riassunti con punti chiave, impatti pratici e azioni consigliate.\n- **Comparazione polizze**: Se l\'utente carica due polizze, il GPT le confronta e fornisce una tabella con tutte le informazioni comparate, includendo sempre parametri fondamentali come massimali, franchigia ed esclusioni, oltre ad altri dettagli rilevanti per una valutazione approfondita.\n\nIl tono del GPT è sempre professionale e naturale, evitando eccessi di formalità o rigidità. Il tono delle email si adatta a quello fornito dall\'utente, mantenendo chiarezza e leggibilità.',

        // 2. Create assistant (or reuse existing assistant ID)
        $assistantResponse = $client->post('assistants', [
            'json' => [
                'name' => 'Assistente Assicurativo',
                'instructions' => '',
                'model' => 'gpt-4o',
            ]
        ]);
        $assistantId = json_decode($assistantResponse->getBody(), true)['id'];

        // 3. Create thread
        $threadResponse = $client->post('threads');
        $threadId = json_decode($threadResponse->getBody(), true)['id'];

        // 4. Attach message with file
        $response = $client->post("threads/{$threadId}/messages", [
            'json' => [
                'role' => 'user',
                'content' => 'in base alla normativa ivass cosa deve fare un assicuratore se il cliente chiede una polizza vita?
insieme alla risposta mi dai anche la fonte e il link ad un eventuale normativa online?',
                // 'attachments' => [
                //     [
                //         'file_id' => $fileId,
                //         'tools' => [
                //             ['type' => 'file_search']
                //         ]
                //     ]
                // ],
            ],
        ]);


        // 5. Run the assistant
        $runResponse = $client->post("threads/{$threadId}/runs", [
            'json' => [
                'assistant_id' => $assistantId,
            ],
        ]);
        $runData = json_decode($runResponse->getBody(), true);
        $runId = $runData['id'];

        // 6. Poll until completed
        do {
            sleep(1);
            $statusResponse = $client->get("threads/{$threadId}/runs/{$runId}");
            $statusData = json_decode($statusResponse->getBody(), true);
            $status = $statusData['status'];
        } while (!in_array($status, ['completed', 'failed']));

        if ($status !== 'completed') {
            $this->error('Assistant failed.');
            return;
        }

        // 7. Get the message response
        $messagesResponse = $client->get("threads/{$threadId}/messages");
        $messages = json_decode($messagesResponse->getBody(), true);

        $lastMessage = $messages['data'][0]['content'][0]['text']['value'];
        echo "\nSummary:\n" . $lastMessage . "\n";



    }
}
