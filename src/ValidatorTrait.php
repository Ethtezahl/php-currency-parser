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

use DomainException;
use InvalidArgumentException;

/**
 * A Trait to validate Currency Properties
 */
trait ValidatorTrait
{
    /**
     * Validate a currency object
     *
     * @param mixed $currency
     *
     * @throws InvalidArgumentException if the $currency is not a Currency object
     *
     * @return Currency
     */
    protected function validateCurrency($currency)
    {
        if (!$currency instanceof Currency) {
            throw new InvalidArgumentException('The submitted value must be a `'.Currency::class.'` object');
        }

        return $currency;
    }

    /**
     * Validate a list of currencies
     *
     * @param mixed $currencies
     *
     * @throws DomainException
     *
     * @return array
     */
    protected function validateCurrencyList($currencies)
    {
        $list = [];
        $numCodeList = [];
        foreach ($currencies as $currency) {
            $currency = $this->validateCurrency($currency);
            $numCode = $currency->getNumCode();
            if (isset($numCodeList[$numCode])) {
                throw new DomainException('Currency numeric code must be unique within the collection');
            }
            $numCodeList[$numCode] = 1;
            $list[$currency->getAlphaCode()] = $currency;
        }

        return $list;
    }

    /**
     * Validate the currency alphaCode code
     *
     * @param string $alphaCode
     *
     * @throws DomainException if the code is not a valid currency alphaCode code
     *
     * @return string
     */
    protected function validateAlphaCode($alphaCode)
    {
        $alphaCode = $this->validateString($alphaCode, 'alphaCode');
        if (!preg_match('/^[a-zA-Z]{3}$/', $alphaCode)) {
            throw new DomainException(sprintf('Not a valid alphaCode: %s', $alphaCode));
        }

        return strtoupper($alphaCode);
    }

    /**
     * Validate the currency name
     *
     * @param mixed  $value
     * @param string $type
     *
     * @throws InvalidArgumentException if the $value can not be converted to a string
     *
     * @return string
     */
    protected function validateString($value, $type)
    {
        if (!is_string($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            throw new InvalidArgumentException(sprintf(
                '%s must be a string; received "%s"',
                $type, (is_object($value) ? get_class($value) : gettype($value))
            ));
        }

        $value = (string) $value;
        $value = trim($value);

        return $value;
    }

    /**
     * Validate the currency numeric code
     *
     * @param string $numeric
     *
     * @throws DomainException if the code is not a valid currency numeric code
     *
     * @return string
     */
    protected function validateNumCode($numeric)
    {
        if (!is_numeric($numeric)) {
            throw new InvalidArgumentException(sprintf(
                'Currency Numeric Code must be a numeric; received "%s"',
                (is_object($numeric) ? get_class($numeric) : gettype($numeric))
            ));
        }

        $numeric = filter_var((int) $numeric, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$numeric) {
            throw new DomainException(sprintf('Not a valid numeric: %s', $numeric));
        }

        return $numeric;
    }

    /**
     * Validate the currency exponent
     *
     * @param int|nul $exponent
     *
     * @throws DomainException if the value is not a positive integer or 0
     *
     * @return int|null
     */
    protected function validateMinorUnitExponent($exponent)
    {
        if (null === $exponent) {
            return $exponent;
        }

        if (false !== filter_var($exponent, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])) {
            return $exponent;
        }

        throw new DomainException(sprintf('Not a valid exponent: %s', $exponent));
    }

    /**
     * Validate a country list
     * @param  array $countries an associative array of countries list
     *                          - the key is the ISO-ALPHA-2 country code
     *                          - the value is the country full name
     * @return array
     */
    protected function validateCountries(array $countries)
    {
        $data = [];
        foreach ($countries as $tld => $countryName) {
            $data[$this->validateTld($tld)] = $this->validateName($countryName);
        }

        return $data;
    }

    /**
     * Validate the currency name
     *
     * @param string $name
     *
     * @throws DomainException if the name is not a valid
     *
     * @return string
     */
    protected function validateName($name)
    {
        $name = $this->validateString($name, 'currency name');
        if ('' === $name) {
            throw new DomainException('The name can not be an empty string');
        }

        return $name;
    }

    /**
     * Validate the currency name
     *
     * @param string $tld
     *
     * @throws DomainException if the name is not a valid
     *
     * @return string
     */
    protected function validateTld($tld)
    {
        $tld = $this->validateString($tld, 'TLD');
        if (!preg_match('/^[a-zA-Z]{2}$/', $tld)) {
            throw new DomainException(sprintf('Not a valid TLD: %s', $tld));
        }

        return strtoupper($tld);
    }
}
