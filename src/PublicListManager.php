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
namespace Pcp;

use Pcp\Http\Client;
use Pcp\Http\CurlClient;
use RuntimeException;
use SimpleXMLElement;

/**
 * Public Currency List Manager.
 *
 * This class obtains, writes, caches, and returns text and PHP representations
 * of the Currency List as maintained by the CURRENCY-ISO.org Website
 */
class PublicListManager
{
    const FILE_COUNTRY_CODE_JSON = 'public-country-code.json';
    const FILE_CURRENCY_XML = 'public-currency-list.xml';
    const FILE_CURRENCY_PHP = 'public-currency-list.php';
    const ISO3166_UNKNOWN_COUNTRY = 'ZZ';

    /**
     * @var string Currency List URI
     */
    protected $xmlUri = 'http://www.currency-iso.org/dam/downloads/lists/list_one.xml';

    /**
     * @var string ISO 3166 Code URI
     */
    protected $countryCodeUri = 'http://data.okfn.org/data/core/country-codes/r/country-codes.json';

    /**
     * @var string Directory where text and php versions of list will be cached
     */
    protected $cacheDir;

    /**
     * Public constructor.
     *
     * @param string $cacheDir   Optional cache directory
     * @param Client $httpClient Optional Http Client
     */
    public function __construct($cacheDir = null, Client $httpClient = null)
    {
        if (is_null($cacheDir)) {
            $cacheDir = dirname(__DIR__).'/data';
        }

        if (is_null($httpClient)) {
            $httpClient = new CurlClient();
        }

        $this->cacheDir = $cacheDir;
        $this->httpClient = $httpClient;
    }

    /**
     * Gets Currency Collection.
     *
     * @return Collection
     */
    public function getList()
    {
        $path = $this->cacheDir.'/'.self::FILE_CURRENCY_PHP;
        if (!file_exists($path)) {
            $this->refreshList();
        }

        $createCurrency = function (array $currency) {
            return Currency::__set_state($currency);
        };

        $arr = include $path;

        return new Collection(array_map($createCurrency, $arr));
    }

    /**
     * Downloads Currency List and writes text cache and PHP cache.
     * Overwrite existing files.
     */
    public function refreshList()
    {
        $this->fetch($this->xmlUri, self::FILE_CURRENCY_XML);
        $this->fetch($this->countryCodeUri, self::FILE_COUNTRY_CODE_JSON);
        $currencies = $this->parseXML(self::FILE_CURRENCY_XML);
        $data = '<?php'.PHP_EOL.'return '.var_export($currencies, true).';';

        return $this->write(self::FILE_CURRENCY_PHP, $data);
    }

    /**
     * Fetch and Save URI content to a specific path
     *
     * @param string $uri
     * @param string $filename Filename in cache dir where data will be written
     *
     * @return int Number of bytes that were written to the file
     */
    protected function fetch($uri, $filename)
    {
        $body = $this->httpClient->getBody($uri);

        return $this->write($filename, $body);
    }

    /**
     * Parses retrieved XML to converted the data into PHP array.
     *
     * @param string $filename Currency List XML filename
     *
     * @return array Associative, multidimensional array representation of the
     *               currency list
     */
    protected function parseXML($filename)
    {
        $data = simplexml_load_file($this->cacheDir.'/'.$filename)->xpath('/ISO_4217/CcyTbl/CcyNtry');
        $arr = [];
        foreach ($data as $item) {
            $this->extractCurrencyData($item, $arr);
        }
        ksort($arr, SORT_STRING);

        return array_values($arr);
    }

    /**
     * Extract Currency Data From The XML node
     *
     * @param SimpleXMLElement $item
     * @param array            &$arr
     */
    protected function extractCurrencyData(SimpleXMLElement $item, array &$arr)
    {
        if ('No universal currency' === (string) $item->CcyNm) {
            return;
        }

        $alphaCode = (string) $item->Ccy;
        $country = (string) $item->CtryNm;
        $tld = $this->fetchTld($country);
        if (isset($arr[$alphaCode])) {
            $arr[$alphaCode]['countries'][$tld] = $country;

            return;
        }

        $minorUnitExponent = (string) $item->CcyMnrUnts;
        if ('N.A.' == $minorUnitExponent) {
            $minorUnitExponent = null;
        }

        $arr[$alphaCode] = [
            'name' => (string) $item->CcyNm,
            'alphaCode' => $alphaCode,
            'numCode' => (string) $item->CcyNbr,
            'minorUnitExponent' => $minorUnitExponent,
            'isFund' => (bool) $item->CcyNm->attributes()->IsFund,
            'countries' => [$tld => $country],
        ];
    }

    /**
     * Fetch the corresponding ISO 3166 Alpha 2 code for a given country name
     *
     * @param string $country The full country name
     *
     * @return string
     */
    protected function fetchTld($country)
    {
        $countryCodeList = $this->getCountryCodeConverter();
        $fCountry = strtoupper(str_replace(['(', ')', ',', ' '], '', $country));
        foreach ($countryCodeList as $key => $value) {
            if (false !== strpos($fCountry, $key)) {
                return $value;
            }
        }

        return self::ISO3166_UNKNOWN_COUNTRY;
    }

    /**
     * Generate the Country Code Converter
     *
     * @return array
     */
    protected function getCountryCodeConverter()
    {
        static $converter;
        if (is_null($converter)) {
            $data = file_get_contents($this->cacheDir.'/'.self::FILE_COUNTRY_CODE_JSON);
            $list = json_decode($data, true);
            $converter = [
                'CONGOTHEDEMOCRATICREPUBLICOFTHE' => 'CD',
                'MOLDOVATHEREPUBLICOF' => 'MD',
                'KOREATHEREPUBLICOF' => 'KR',
                'KOREATHEDEMOCRATICPEOPLE’SREPUBLICOF' => 'KP',
                'EUROPEANUNION' => 'EU',
                'FALKLANDISLANDSTHE[MALVINAS]' => 'FK',
                'HOLYSEETHE' => 'VA',
            ];
            $pattern = ['(', ')', ',', ' '];
            foreach ($list as $value) {
                $key = strtoupper(str_replace($pattern, '', $value['currency_country_name']));
                if ('' === $key || isset($converter[$key])) {
                    continue;
                }
                $converter[$key] = $value['ISO3166-1-Alpha-2'];
            }
        }

        return $converter;
    }

    /**
     * Writes to file.
     *
     * @param string $filename Filename in cache dir where data will be written
     * @param mixed  $data     Data to write
     *
     * @throws RuntimeException if unable to write file
     *
     * @return int Number of bytes that were written to the file
     */
    protected function write($filename, $data)
    {
        $result = @file_put_contents($this->cacheDir.'/'.$filename, $data);
        if ($result === false) {
            throw new RuntimeException("Cannot write '".$this->cacheDir.'/'."$filename'");
        }

        return $result;
    }
}
