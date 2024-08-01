<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\ShopifyController;
use Maltekuhr\LaravelGpt\Facades\Gpt;
use App\GPT\Actions\TranslateText\TranslateTextGPTAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class TranslateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $shopifyController = new ShopifyController();

        $response = $shopifyController->getProducts();
        $products = $response->json()['data'] ?? [];

        foreach ($products as $product) {
            try {
                $translator = new TranslateTextGPTAction($product['title']);
                $translatedTitle = $translator->function()($product['title']);

                $translator = new TranslateTextGPTAction($product['body_html']);
                $translatedDescription = $translator->function()($product['body_html']);

                $updateResponse = $shopifyController->updateProduct(new Request([
                    'title' => $translatedTitle,
                    'description' => $translatedDescription,
                ]), $product['id']);

                if (!$updateResponse->json()['success']) {
                    throw new Exception("Failed to update product ID: {$product['id']}");
                }
            } catch (Exception $e) {
                Log::error('Error processing product', ['error' => $e->getMessage()]);
            }
        }
    }
}
