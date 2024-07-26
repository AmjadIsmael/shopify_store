<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PHPShopify\ShopifySDK;
use Illuminate\Support\Facades\Http;




class ShopifyController extends Controller
{


    private   $shopUrl = config('shopify-app.shop_url');

    private $apiKey = config('shopify-app.api_key');
    private   $apiSecret = config('shopify-app.api_secret');

    /*    private $shopUrl = 'https://f89c7c-c8.myshopify.com';
    private $apiKey = '22508ee2625bb2deb4c182efbc6ad4d0';
    private $apiSecret = 'b562362038f516eb7132ef63f99adb54';
*/
    private function buildUrl($endpoint)
    {
        return "{$this->shopUrl}{$endpoint}";
    }

    public function getProducts()
    {
        $endpoint = "/admin/api/2023-04/products.json";
        $url = $this->buildUrl($endpoint);

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)->get($url);

        if ($response->successful()) {
            $products = $response->json()['products'];
            return $products;
        } else {
            return response()->json(['error' => 'Failed to fetch products', 'details' => $response->json()], $response->status());
        }
    }

    public function updateProducts(Request $request, $productId)
    {
        $endpoint = "/admin/api/2023-04/products/{$productId}.json";
        $url = $this->buildUrl($endpoint);

        $productData = [
            'product' => [
                'title' => $request->input('title'),
                'body_html' => $request->input('description'),
            ],
        ];

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)->put($url, $productData);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to update product', 'details' => $response->json()], $response->status());
        }
        $updatedProduct = $response->json()['product'];
        return $updatedProduct;
    }
}
