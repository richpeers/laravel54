<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Londinium;
use App\Subcategories;
use Debugbar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class LondiniumController extends Controller
{
    public function index()
    {
        $data['site'] = Londinium::orderByRaw('RAND()')->take(1)->get();
        return view('londinium', $data);
    }

    public function sites()
    {
        $data['sites'] = Londinium::orderBy('id')->where('active', '=', 1)->paginate(15);
        $data['countSaved'] = Londinium::where('saved', '=', 'saved')->count();
        $data['highestSavedId'] = Londinium::select('id')->where('saved', '=', 'saved')->orderBy('id', 'DESC')->take(1)->get();
        return view('londiniumSites', $data);
    }

    public function saved()
    {
        $data['sites'] = Londinium::orderBy('url')->where('saved', '=', 'saved')->paginate(2099);
        $data['countSaved'] = Londinium::where('saved', '=', 'saved')->count();
        $data['highestSavedId'] = Londinium::select('id')->where('saved', '=', 'saved')->orderBy('id', 'DESC')->take(1)->get();
        return view('londiniumSites', $data);
    }

    public function search()
    {
        echo "hi";
    }

    public function subcategories()
    {
        $data['subcategories'] = Subcategories::orderBy('name')->paginate(5000);

        return view('londiniumSubcategories', $data);
    }

    public function subcategory($id)
    {
        $data['sites'] = Londinium::where('subcategory_id', '=', $id)->orderBy('url')->paginate(500);
        $data['countSaved'] = Londinium::where('saved', '=', 'saved')->count();
        $data['highestSavedId'] = Londinium::select('id')->where('saved', '=', 'saved')->orderBy('id', 'DESC')->take(1)->get();

        return view('londiniumSites', $data);
    }

    public function save($id)
    {
        $Londinium = Londinium::where('id', '=', $id)->first();
        $Londinium->saved = 'saved';
        $Londinium->save();
        return back()->with('message', 'Operation Successful !');
    }

    public function unsave($id)
    {
        $Londinium = Londinium::where('id', '=', $id)->first();
        $Londinium->saved = '';
        $Londinium->save();
        return back()->with('message', 'Operation Successful !');
    }


    public function sitesBySubcategory()
    {
        $subcategories = [];

        $result = Subcategories::orderBy('name')->get();
        foreach ($result as $row) {
            $subcategories[$row['id'] ]= $row['name'];
        }


        $data['subcategories'] = $subcategories;
        $data['sites'] = Londinium::where('saved', '=', 'saved')->orderBy('subcategory_id')->paginate(1000);

        return view('londiniumSitesOutputBySubcategory', $data);
    }


    public function outputHtml()
    {
        \Debugbar::disable();
        return view('outputHtml');
    }

    public function screenshot()
    {
        // uses https://github.com/spatie/browsershot

        $browsershot = new \Spatie\Browsershot\Browsershot();
        $browsershot
        ->setURL('http://www.londinium.com')
        ->setWidth(400)
        ->setHeightToRenderWholePage()
        ->setTimeout(5000)
        ->save('/var/www/laravel54/public/screenshots/londinium.jpg');
    }

    public function spider()
    {
        // get random url from sites table
        $data['url'] = Londinium::where('active', '=', 1)->orderByRaw('RAND()')->take(1)->first();

        $url = $data['url']->url;
        $id = $data['url']->id;

        echo $id . " -> " . $url;

        $client = new \GuzzleHttp\Client([
            'timeout'  => 200.0,
            'http_errors' => false,
            'base_uri' => $url
            ]);
        


        try {
            $response = $client->request('GET', $url);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // If there are network errors, we need to ensure the application doesn't crash.
            // if $e->hasResponse is not null we can attempt to get the message
            // Otherwise, we'll just pass a network unavailable message.
            if ($e->hasResponse()) {
                $exception = (string) $e->getResponse()->getBody();
                $exception = json_decode($exception);
                echo $e->getCode();
                echo "request exception" . $response->getStatusCode();
                exit;
            } else {
                echo "request exception else";
                exit;
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            echo "connection exception";
            // var_dump($e);
            exit();
        }




        $html = $response->getBody()->getContents();

        $doc = new \DOMDocument();
        $tidy_config = array(
                     'clean' => true,
                     'output-xhtml' => true,
                     'show-body-only' => false,
                     'wrap' => 0,
                     );
        $tidy = tidy_parse_string($html, $tidy_config, 'UTF8');
        $tidy->cleanRepair();

        $html = $tidy->html();

        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, "UTF-8"));
        libxml_clear_errors();

        $xpath = new \DOMXpath($doc);
        $title = $xpath->evaluate("string(//html/head/title[1])");
        echo "<h1>".$title . "</h1>";


        $desc = $xpath->query('/html/head/meta[@name="description"]/@content');
        foreach ($desc as $content) {
            echo $content->value . PHP_EOL;
        }


        $hrefs = $xpath->evaluate("/html/body//a");

        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            $title = @$href->firstChild->nodeValue;
            echo "<br /> $url $title";
        }
    }
}
