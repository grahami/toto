<?php
/**
 * Simple controller for all web access for Toto sample code which then uses the REST api
 * to actually provide the core functionality
 */

namespace App\Http\Controllers;

use App\Helpers\EventHelper;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Config;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Default method with some simple instructions on what the web app can do, using the REST api
     * @return View
     */
    public function index()
    {
        $data = '';
        $instructions = [];
        $instructions['<a href="ip/77.44.8.10">ip/77.44.8.10</a>'] = 'Find information about an IP Address (eg. 77.44.8.10)';
        $instructions['<a href="find">find</a>'] = 'Retrieve an IP address record by its unique ID';
        foreach ($instructions as $key => $value) {
            $data .= $key . ' : ' . $value . '</br>';
        }
        return view('page', compact('data'));
    }

    /**
     * Get info about a particular IP address using a sample from the MaxMind public data
     * @param string $ip The IP Address to lookup. Can be IPV4 or IPV6
     * @return View
     */
    public function getIP($ip = '')
    {
        $data = '';
        $client = new Client();
        $restHost = Config::get('app.rest_host');
        $body = $client->request('GET', 'http://' . $restHost . '/api/ip/' . $ip, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->getBody();

        $contents = (string)$body;
        $dataArray = json_decode($contents, true);
        foreach ($dataArray['data'] as $key => $values) {
            foreach ($values as $valueKey => $value) {
                $data .= $valueKey . '=' . $value . '</br>';
            }
            $data .= '</br></br>';
        }
        $data .= "IP addresses can be in ranges :</br>";
        $data .= "77.44.2.1 - 77.44.2.255</br>";
        $data .= "77.44.8.1 - 77.44.8.255</br>";
        $data .= "77.44.83.1 - 77.44.83.255</br>";
        $data .= "86.24.110.1 - 86.24.110.255</br>";
        $data .= "86.24.113.128 - 86.24.113.255</br>";
        return view('page', compact('data'));
    }

    /**
     * Display the data entry form for post verbs as opposed to get verbs
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function findIP()
    {
        $data = "Valid ID's are int he range 1 - 45</br>";
        return view('page-entry', compact('data'));
    }

    /**
     * Process the entered data and display the results of the IP address that matches the ID
     * @param Request $request Incoming request with form post data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function postFindIP(Request $request)
    {
        $data = '';
        try {
            $input = $request->all();
            if (!isset($input['id']) || strlen($input['id']) == 0) {
                throw new \Exception('ID is missing');
            }
            $client = new Client();
            $restHost = Config::get('app.rest_host');
            $body = $client->request('GET', 'http://' . $restHost . '/api/find/' . $input['id'], [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->getBody();
            $contents = (string)$body;
            $dataArray = json_decode($contents, true);
            foreach ($dataArray['data'] as $key => $values) {
                foreach ($values as $valueKey => $value) {
                    $data .= $valueKey . '=' . $value . '</br>';
                }
                $data .= '</br></br>';
            }
            $data .= "Valid ID's are int he range 1 - 45</br>";
        } catch (\Exception $exception) {
            $errorDetail = $exception->getMessage();
            EventHelper::log('Post', 'ERROR', ['input' => $input], ['errorMessage' => $errorDetail]);
        }
        return view('page-entry', compact('data'));
    }

}