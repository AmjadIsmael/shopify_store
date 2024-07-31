<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\ShopifyController;
use Maltekuhr\LaravelGpt\Facades\Gpt;
use Illuminate\Http\Request;

class TranslateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $shopifyController = new ShopifyController();

        $response = $shopifyController->getProducts();
        if (!$response->original['success']) {
            return;
        }

        $products = $response->original['data'];

        foreach ($products as $product) {
            $translatedTitle = $this->translateText($product['title']);
            $translatedDescription = $this->translateText($product['body_html']);

            $shopifyController->updateProduct(new Request([
                'title' => $translatedTitle,
                'description' => $translatedDescription,
            ]), $product['id']);
        }
    }

    private function translateText($text)
    {
        $response = Gpt::translate($text, 'en', 'fr');
        return $response['choices'][0]['text'] ?? $text;
    }
}
