<?php

use App\Jobs\DownloadProductImage;
use App\Jobs\ImportSiteMap;
use App\Models\SiteMap;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});

Route::get('crawl', function () {
    $sitemapUrl = 'https://mych.vn/sitemap.xml';
    $client = new Client();
    $response = $client->get($sitemapUrl);

    if ($response->getStatusCode() === 200) {
        $localFilePath = storage_path('app/sitemap.xml');
        file_put_contents($localFilePath, $response->getBody());

        return "Sitemap downloaded successfully. File saved at: $localFilePath";
    } else {
        return "Failed to download sitemap. HTTP status code: " . $response->getStatusCode();
    }
});

Route::get('insert', function () {
    dispatch(new ImportSiteMap());
    return "Import job has been dispatched.";
});

Route::get('download', function () {
    $chunkSize = 50;
    SiteMap::chunk($chunkSize, function ($data) {
        foreach ($data as $key => $sitemap) {
            dispatch(new DownloadProductImage($sitemap->url));
        }
    });

    return "Download Product Image job has been dispatched.";
});

use Symfony\Component\DomCrawler\Crawler;

Route::get('get-demo', function () {
    $url = 'https://mych.vn/san-pham/dung-cu-bom-bong-bong-bong-yoga-bang-chan.html';

    $client = new Client();
    $response = $client->get($url);

    $html = $response->getBody()->getContents();

    $crawler = new Crawler($html);

    $imageElement = $crawler->filter('#Zoom-1 img')->first();

    if ($imageElement->count() > 0) {
        $imageUrl = $imageElement->attr('src');

        $imageContent = $client->get('https://mych.vn/' . $imageUrl)->getBody()->getContents();

        file_put_contents(storage_path('app/public/product/image_' . time() . '.jpg'), $imageContent);

        dd('Image has been successfully downloaded!');
    } else {
        dd('Image not found.');
    }
});
