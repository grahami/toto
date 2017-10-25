<?php
/**
 * APIController to handle all calls to the REST API.
 * True core functionality is implemented here and is accessible to clients via the REST API.
 */

namespace app\Http\Controllers;

use App\Models\IPAddress\IPAddress;

class APIController
{
    /**
     * Retrieve information about the location of an IP address
     * @param string $ip The IPAddress to find. Supports IPV4 and IPV6 formats
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIPInfo($ip)
    {
        $ipAddresses = IPAddress::findCustom($ip);
        $data = [];
        foreach ($ipAddresses as $key => $ipAddress) {
            // get the Location model from the IPAddress model. It must exist as there is a foreign key constraint
            $location = $ipAddress->getLocation();
            $data[] = ['id' => $ipAddress->id, 'postCode' => $ipAddress->postcode, 'continent' => $location->continent,
                'country' => $location->country, 'region' => $location->region, 'city' => $location->city];
        }
        return response()->json(['data' => $data], 200);
    }

    /**
     * Retrieve an IPAddress model record by the unique ID
     * @param integer $id The ID of the IPAddress model to retrieve
     * @return \Illuminate\Http\JsonResponse
     */
    public function findIPInfo($id = 0)
    {
        $ipAddress = IPAddress::find($id);
        $data = [];
        if (isset($ipAddress)) {
            // get the Location model from the IPAddress model. It must exist as there is a foreign key constraint
            $location = $ipAddress->getLocation();
            $data[] = ['id' => $ipAddress->id, 'postCode' => $ipAddress->postcode, 'continent' => $location->continent,
                'country' => $location->country, 'region' => $location->region, 'city' => $location->city];
        } else {
            $data[] = ['Error' => 'ID not found'];
        }
        return response()->json(['data' => $data], 200);
    }

}