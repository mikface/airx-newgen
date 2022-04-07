<?php

declare(strict_types=1);

namespace App\Core\Service;

use CurlHandle;

use function bin2hex;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function json_decode;
use function random_bytes;
use function str_starts_with;
use function substr;

use const CURLOPT_PROXY;
use const CURLOPT_PROXYUSERPWD;
use const CURLOPT_RETURNTRANSFER;

final class Curl
{
    private const BOT_PROXY = 'botproxy';

    public static function getProxyAuth() : string
    {
        $user = $_ENV['CURL_PROXY_USER'];
        if ($_ENV['CURL_PROXY_NAME'] === self::BOT_PROXY) {
            $user .= '+' . bin2hex(random_bytes(10));
        }

        return $user . ':' . $_ENV['CURL_PROXY_PASSWORD'];
    }

    public static function getFromUrl(string $url, bool $useProxy = false) : CurlHandle
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($useProxy === true) {
            curl_setopt($ch, CURLOPT_PROXY, $_ENV['CURL_PROXY']);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::getProxyAuth());
        }

        return $ch;
    }

    public static function performSingleGet(string $url, bool $useProxy = false) : string
    {
        $ch = self::getFromUrl($url, $useProxy);
        $result = curl_exec($ch);

        if (str_starts_with(bin2hex($result), 'efbbbf')) {
            return substr($result, 3);
        }

        return $result === false ? '' : $result;
    }

    /** @return array<int|string, mixed> */
    public static function performSingleGetAndDecode(string $url, bool $associative = true, bool $useProxy = false) : array
    {
        $result = json_decode(self::performSingleGet($url, $useProxy), $associative);

        return $result === false || $result === null ? [] : $result;
    }
}
