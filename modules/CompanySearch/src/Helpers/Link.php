<?php

namespace Modules\CompanySearch\Helpers;

class Link
{
    public static function getHost(string $url): string
    {
        $host = str_replace(['http://', 'https://', 'www.'], '', $url);
        $parts = explode('/', $host);

        return $parts[0];
    }
}
