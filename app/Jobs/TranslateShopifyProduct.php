<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Maltekuhr\LaravelGpt\Facades\Gpt;
use App\GPT\Actions\TranslateText\TranslateTextGPTAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class TranslateShopifyProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $shopDomain;
    private $accessToken;

    public function __construct()
    {
        $this->shopDomain = config('shopify-app.myshopify_domain');
        $this->accessToken = config('shopify-app.access_token');
    }

    private function buildUrl($endpoint)
    {
        return "{$this->shopDomain}{$endpoint}";
    }

    private function getProducts()
    {
        $endpoint = "/admin/api/2023-04/products.json";
        $url = $this->buildUrl($endpoint);

        $response = Http::withHeaders(['X-Shopify-Access-Token' => $this->accessToken])
            ->get($url);

        if ($response->failed()) {
            return response(['success' => false, 'message' => $response->json()], $response->status());
        }

        $products = $response->json()['products'];
        return response(['success' => true, 'data' => $products]);
    }

    private function updateProduct(Request $request, $productId)
    {
        $endpoint = "/admin/api/2023-04/products/{$productId}.json";
        $url = $this->buildUrl($endpoint);

        $payload = [
            'product' => [
                'title' => $request->title,
                'body_html' => $request->description,
            ],
        ];
        $response = Http::withHeaders(['X-Shopify-Access-Token' => $this->accessToken])
            ->put($url, $payload);

        if ($response->failed()) {
            return response(['success' => false, 'message' => $response->json()], $response->status());
        }

        $product = $response->json()['product'];
        return response(['success' => true, 'data' => $product]);
    }

    public function handle()
    {
        $response = $this->getProducts();
        $products = $response->json()['data'] ?? [];

        foreach ($products as $product) {
            try {
                $translationResponse = TranslateTextGPTAction::make($product)->send($product);

                $translatedTitle = $translationResponse['text_parts'][0];
                $translatedDescription = $translationResponse['text_parts'][1];

                $updateResponse = $this->updateProduct(new Request([
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
