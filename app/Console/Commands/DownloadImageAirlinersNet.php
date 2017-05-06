<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DownloadImageAirlinersNet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bizjets:downloadImageAirlinersNet {reg?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download an image from airliners.net for a plane registration';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $reg = $this->argument('reg');

        echo "command called to download :" . $reg . PHP_EOL;

        $url = "http://www.airliners.net/search?registrationActual=" . $reg;

        $client = new Client([
            'timeout'  => 200.0,
            ]);
        $response = $client->request('GET', $url);
        $html = $response->getBody()->getContents();

        $doc = new \DOMDocument();
        $tidy = tidy_parse_string($html);
        $html = $tidy->html();

        $doc->loadHTML(mb_convert_encoding($html, "UTF-8"));
        $xpath = new \DOMXpath($doc);
        
        $src = $xpath->evaluate("string(//img[@class='lazy-load']/@src)");
        if ($src) {
            $imgSrc =  $src;
            $contents=file_get_contents($imgSrc);
            $save_path="/var/www/laravel54/public/planeImages/airlinersNet/".$reg . ".jpg";
            file_put_contents($save_path,$contents);
            
            echo PHP_EOL;
        }

    }
}