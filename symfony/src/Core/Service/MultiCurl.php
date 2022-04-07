<?php

declare(strict_types=1);

namespace App\Core\Service;

use CurlHandle;
use CurlMultiHandle;

use function array_key_exists;
use function count;
use function curl_getinfo;
use function curl_multi_add_handle;
use function curl_multi_close;
use function curl_multi_exec;
use function curl_multi_getcontent;
use function curl_multi_init;
use function curl_multi_remove_handle;

use const CURLINFO_EFFECTIVE_URL;
use const CURLINFO_HTTP_CODE;
use const PHP_EOL;

final class MultiCurl
{
    private CurlMultiHandle $curl;

    /** @var array<int|string, CurlHandle> */
    private array $handles = [];

    public function __construct()
    {
        $this->curl = curl_multi_init();
    }

    public function addHandle(CurlHandle $curlHandle, ?string $key = null) : void
    {
        if ($key === null) {
            $key = count($this->handles);
        }

        if (array_key_exists($key, $this->handles)) {
            return;
        }

        curl_multi_add_handle($this->curl, $curlHandle);
        $this->handles[$key] = $curlHandle;
    }

    /** @return array<int|string, string> */
    public function execute() : array
    {
        $running = null;
        do {
            curl_multi_exec($this->curl, $running);
        } while ($running);

        foreach ($this->handles as $key => $handle) {
            $returnCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
            if ($returnCode !== 200) {
                echo PHP_EOL . 'WRONG RETURN CODE: ' . $returnCode . PHP_EOL;
                echo 'URL: ' . $url . PHP_EOL;
                echo curl_multi_getcontent($handle) . PHP_EOL;

                continue;
            }

            $results[$key] = curl_multi_getcontent($handle);
            curl_multi_remove_handle($this->curl, $handle);
        }

        curl_multi_close($this->curl);

        $this->handles = [];

        return $results ?? [];
    }
}
