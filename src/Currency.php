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

use JsonSerializable;

class Currency implements JsonSerializable
{
    use ValidatorTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $alphaCode;

    /**
     * @var int
     */
    protected $numCode;

    /**
     * @var int
     */
    protected $minorUnitExponent;

    /**
     * @var string[]
     */
    protected $countries;

    /**
     * @var bool
     */
    protected $isFund;

    /**
     * @param string   $name
     * @param string   $alphaCode
     * @param int      $numCode
     * @param int|null $minorUnitExponent
     * @param bool     $isFund
     * @param string[] $countries
     */
    public function __construct($name, $alphaCode, $numCode, $minorUnitExponent, $isFund, array $countries)
    {
        $this->name = $this->validateName($name);
        $this->alphaCode = $this->validateAlphaCode($alphaCode);
        $this->numCode = $this->validateNumCode($numCode);
        $this->minorUnitExponent = $this->validateMinorUnitExponent($minorUnitExponent);
        $this->isFund = (bool) $isFund;
        $this->countries = $this->validateCountries($countries);
    }

    /**
     * The currency full name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The currency string representation
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getAlphaCode();
    }

    /**
     * The currency alphabetic code
     *
     * @return string
     */
    public function getAlphaCode()
    {
        return $this->alphaCode;
    }

    /**
     * The currency numeric code
     *
     * @return int
     */
    public function getNumCode()
    {
        return $this->numCode;
    }

    /**
     * The currency minor unit exponent
     *
     * @return int
     */
    public function getMinorUnitExponent()
    {
        return $this->minorUnitExponent;
    }

    /**
     * Tells whether this is a fund currency
     *
     * @return bool
     */
    public function isFund()
    {
        return $this->isFund;
    }

    /**
     * An associative array where
     *
     * <ul>
     * <li>the index is the ISO 3166-alpha 2 country code
     * <li>the value is the country full name
     * </ul>
     *
     * @return string[]
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Tells whether the currency is legal in
     * a given country. The method expect a valid ISO 3166 code name
     *
     * @param string $tld ISO ALPHA-2 country code
     *
     * @return bool
     */
    public function isLegalIn($tld)
    {
        $tld = $this->validateTld($tld);

        return isset($this->countries[$tld]);
    }

    /**
     * @inheritdoc
     */
    public static function __set_state(array $currency)
    {
        return new Currency(
            $currency['name'],
            $currency['alphaCode'],
            $currency['numCode'],
            $currency['minorUnitExponent'],
            $currency['isFund'],
            $currency['countries']
        );
    }

    /**
     * An Array representation of a Currency
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'alphaCode' => $this->alphaCode,
            'numCode' => $this->numCode,
            'minorUnitExponent' => $this->minorUnitExponent,
            'isFund' => $this->isFund,
            'countries' => $this->countries,
        ];
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'alphaCode' => $this->alphaCode,
            'numCode' => sprintf('%03d', $this->numCode),
            'minorUnitExponent' => $this->minorUnitExponent,
            'isFund' => $this->isFund,
            'countries' => $this->countries,
        ];
    }
}
