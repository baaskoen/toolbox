<?php

namespace Modules\CompanySearch\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Google
{
    public static function search(string $query): Response
    {
        $client = new Client([
            'base_uri' => 'https://customsearch.googleapis.com'
        ]);

        $key = config('company.google.api_key');
        $cx = config('company.google.search_engine_id');
        $cr = 'countryNL';
        $hl = 'nl';

        return $client->get('/customsearch/v1', [
            'query' => [
                'key' => $key,
                'q' => $query,
                'cx' => $cx,
                'c2coff' => 1,
                'cr' => $cr,
                'hl' => $hl
            ]
        ]);
    }

    public static function searchPlace(string $query): Response
    {
        $client = new Client([
            'base_uri' => 'https://maps.googleapis.com'
        ]);

        $key = config('company.google.api_key');

        return $client->get("/maps/api/place/findplacefromtext/json", [
            'query' => [
                'key' => $key,
                'input' => $query,
                'inputtype' => 'textquery',
                'language' => 'nl',
            ]
        ]);
    }

    public static function getPlace(string $id): Response
    {
        $client = new Client([
            'base_uri' => 'https://maps.googleapis.com'
        ]);

        $key = config('company.google.api_key');

        return $client->get("/maps/api/place/details/json", [
            'query' => [
                'key' => $key,
                'place_id' => $id,
                'language' => 'nl',
            ]
        ]);
    }
}
