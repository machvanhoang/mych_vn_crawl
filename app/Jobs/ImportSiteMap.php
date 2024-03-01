<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportSiteMap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $localFilePath = storage_path('app/sitemap.xml');
        if (file_exists($localFilePath)) {
            $xmlContent = file_get_contents($localFilePath);
            $xml = new \SimpleXMLElement($xmlContent);
            foreach ($xml->url as $url) {
                $urlString = (string) $url->loc;
                DB::table('site_maps')->insert([
                    'url' => $urlString,
                ]);
            }
        }
    }
}
