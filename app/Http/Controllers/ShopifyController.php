<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShopifyController extends Controller
{
    private $shopDomain;
    // private $apiKey;
    // private $apiSecret;
    private $accessToken;

    public function __construct()
    {
        $this->shopDomain = config('shopify-app.myshopify_domain');
        // $this->apiKey = config('shopify-app.api_key');
        // $this->apiSecret = config('shopify-app.api_secret');
        $this->accessToken = config('shopify-app.access_token');
    }
    
    private function buildUrl($endpoint)
    {
        return "{$this->shopDomain}{$endpoint}";
    }

    public function getProducts()
    {
        $endpoint = "/admin/api/2023-04/products.json";
        $url = $this->buildUrl($endpoint);

        $response = Http::withOptions(['verify' => false])
            ->withHeaders(['X-Shopify-Access-Token' => $this->accessToken])
            ->get($url);

        if ($response->failed()) {
            return response(['success' => false, 'message' => $response->json()], $response->status());
        }

        $products = $response->json()['products'];
        return response(['success' => true, 'data' => $products]);
    }

    public function updateProduct(Request $request, $productId)
    {
        $endpoint = "/admin/api/2023-04/products/{$productId}.json";
        $url = $this->buildUrl($endpoint);

        $payload = [
            'product' => [
                'title' => $request->title,
                'body_html' => $request->description,
            ],
        ];
        $response = Http::withOptions(['verify' => false])
            ->withHeaders(['X-Shopify-Access-Token' => $this->accessToken])
            ->put($url, $payload);

        if ($response->failed()) {
            return response(['success' => false, 'message' => $response->json()], $response->status());
        }

        $product = $response->json()['product'];
        return response(['success' => true, 'data' => $product]);
    }
}
