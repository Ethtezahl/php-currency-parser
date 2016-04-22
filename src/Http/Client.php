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

interface Client
{
    /**
     * returns the body of an HTTP GET request
     *
     * @param string $uri the HTTP URI
     *
     * @throws RuntimeException If an error occurs while retrieve the HTTP Response Body
     *
     * @return string the HTTP Response body
     */
    public function getBody($uri);
}
