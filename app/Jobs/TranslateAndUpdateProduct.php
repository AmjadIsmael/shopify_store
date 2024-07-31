<?php

namespace App\Jobs;

use App\Http\Controllers\ShopifyController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use MalteKuhr\LaravelGPT\Facades\OpenAI;
use Illuminate\Support\Facades\Log;


class TranslateAndUpdateProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;
    protected $title;
    protected $description;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productId, $title, $description)
    {
        $this->productId = $productId;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Translate the title and description using OpenAI
        try {
            $translatedTitle = OpenAI::completions()->create([
                'model' => 'text-davinci-003',
                'prompt' => "Translate the following text to French: {$this->title}",
                'max_tokens' => 60,
            ])->choices[0]->text;

            $translatedDescription = OpenAI::completions()->create([
                'model' => 'text-davinci-003',
                'prompt' => "Translate the following text to French: {$this->description}",
                'max_tokens' => 200,
            ])->choices[0]->text;
        } catch (\Exception $e) {
            Log::error('Translation failed: ' . $e->getMessage());
            return;
        }

        $shopifyController = app(ShopifyController::class);
        $request = new \Illuminate\Http\Request([
            'title' => $translatedTitle,
            'description' => $translatedDescription,
        ]);

        $shopifyController->updateProduct($request, $this->productId);
    }
}
