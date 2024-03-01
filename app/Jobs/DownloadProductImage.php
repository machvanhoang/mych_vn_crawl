<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class DownloadProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $url;
    /**
     * Create a new job instance.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client();
        $response = $client->get($this->url);
        $html = $response->getBody()->getContents();
        $crawler = new Crawler($html);
        $imageElement = $crawler->filter('#Zoom-1 img')->first();

        if ($imageElement->count() > 0) {
            $imageUrl = $imageElement->attr('src');

            $imageContent = $client->get('https://mych.vn/' . $imageUrl)->getBody()->getContents();

            $path = storage_path('app/public/product/image_' . time() . '.jpg');
            file_put_contents($path, $imageContent);

            info("Success: {$path}");
        } else {
            info("Error");
        }
    }
}
