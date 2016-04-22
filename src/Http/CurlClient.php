<?php

/**
* This file is part of the php-currency-parser library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/php-currency-parser
* @version 0.1.0
* @package php-currency-parser
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Pcp\Http;

use RuntimeException;

class CurlClient implements Client
{
    public function getBody($uri)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $uri,
        ]);

        $body = curl_exec($curl);
        $errno = curl_errno($curl);
        if ($errno) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException(' Curl error: '.$error, $errno);
        }
        curl_close($curl);

        return $body;
    }
}
