<?php

namespace App\Helper;

use GuzzleHttp\Client;

class curl
{
    public function webhookPost($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ["data"=>$data]);
        return curl_exec($ch);

        // $client = new Client();
        // $client->request('POST', $url, ['form_params' => $data])->getBody()->getContents();

    }
}
