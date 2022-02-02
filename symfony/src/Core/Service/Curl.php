<?php

declare(strict_types=1);

namespace App\Core\Service;

use CurlHandle;

use function curl_exec;
use function curl_init;
use function curl_setopt;
use function json_decode;

use const CURLOPT_RETURNTRANSFER;

final class Curl
{
    public static function getFromUrl(string $url) : CurlHandle
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }

    public static function performSingleGet(string $url) : string
    {
        $result = curl_exec(self::getFromUrl($url));

        return $result === false ? '' : $result;
    }

    /** @return array<int|string, mixed> */
    public static function performSingleGetAndDecode(string $url, bool $associative = true) : array
    {
        $result = json_decode(self::performSingleGet($url), $associative);

        return $result === false ? [] : $result;
    }
}
