<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


use App\GPT\Actions\TranslateText\TranslateTextGPTAction;

class TestController extends Controller
{
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

    /**
     * Test the translation action.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testTranslate(Request $request)
    {
        $response = $this->getProducts();

        if ($response->json()['success'] === false) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch products'], 500);
        }

        $products = $response->json()['data'];

        if (empty($products)) {
            return response()->json(['success' => false, 'message' => 'No products found'], 404);
        }

        $product = $products[0];

        $result = TranslateTextGPTAction::make($product)->send($product);

        $translatedTitle = $result['title'] ?? 'Translation failed'; // Fallback if title is not present
        $translatedDescription = $result['description'] ?? 'Translation failed'; // Fallback if description is not present

        return response()->json([
            'title' => $translatedTitle,
            'description' => $translatedDescription
        ]);
    }
}
