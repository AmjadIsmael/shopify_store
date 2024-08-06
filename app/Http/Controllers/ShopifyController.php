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
}
