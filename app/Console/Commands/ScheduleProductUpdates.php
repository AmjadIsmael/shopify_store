<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\TranslateAndUpdateProduct;
use Illuminate\Support\Facades\Http;


class ScheduleProductUpdates extends Command
{
    protected $signature = 'products:translate-update';
    protected $description = 'Translate and update Shopify products every 2 hours.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $response = Http::withHeaders(['X-Shopify-Access-Token' => config('shopify-app.access_token')])
            ->get(config('shopify-app.myshopify_domain') . '/admin/api/2023-04/products.json');

        if ($response->failed()) {
            $this->error('Failed to fetch products.');
            return;
        }

        $products = $response->json()['products'];

        foreach ($products as $product) {
            TranslateAndUpdateProduct::dispatch($product['id'], $product['title'], $product['body_html']);
        }

        $this->info('Product translation and updates have been dispatched.');
    }
}
