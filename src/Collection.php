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

use ArrayObject;
use CallbackFilterIterator;
use DomainException;
use OutOfBoundsException;

/**
 * A Collection Of Currency Object.
 */
class Collection extends ArrayObject
{
    use ValidatorTrait;

    /**
     * New instance
     *
     * @param mixed $currencies a enumerable list of Currency objects
     */
    public function __construct($currencies = [])
    {
        $currencies = $this->validateCurrencyList($currencies);

        parent::__construct($currencies);
    }

    /**
     * Returns the Currency object associated with a Currency Numeric Code
     *
     * @param int|string $numCode
     *
     * @throws OutOfBoundsException
     *
     * @return Currency
     */
    public function getByNumCode($numCode)
    {
        $currency = $this->fetchNumCode($numCode);
        if ($currency instanceof Currency) {
            return $currency;
        }

        throw new OutOfBoundsException(sprintf('Unknown currency numeric code: %s', $numCode));
    }

    /**
     * Retrieve a Currency according to its ISO 4217 numeric code
     *
     * @param int|string $numCode
     *
     * @return Currency|null
     */
    protected function fetchNumCode($numCode)
    {
        $numCode = $this->validateNumCode($numCode);
        foreach ($this->getIterator() as $currency) {
            if ($numCode === $currency->getNumCode()) {
                return $currency;
            }
        }

        return null;
    }

    /**
     * Tell whether the Currency Numeric Code is present in the Collection
     *
     * @param int|string $numCode
     *
     * @return bool
     */
    public function hasNumCode($numCode)
    {
        $currency = $this->fetchNumCode($numCode);

        return $currency instanceof Currency;
    }

    /**
     * Returns a new Collection containing only the Currency for which a
     * predicate returns true.
     *
     * @param callable $callable
     *
     * @return static
     */
    public function filter(callable $callable)
    {
        return new static(new CallbackFilterIterator($this->getIterator(), $callable));
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($alphaCode, $currency)
    {
        $currency = $this->validateCurrency($currency);
        $alphaCode = $this->validateAlphaCode($alphaCode);
        if ($alphaCode !== $currency->getAlphaCode()) {
            throw new DomainException(
                'The offset must be equals to the `'.Currency::class.'` object alphabetic code'
            );
        }

        $numCode = $currency->getNumCode();
        $predicate = function (Currency $cur) use ($alphaCode, $numCode) {
            return $cur->getAlphaCode() !== $alphaCode && $cur->getNumCode() === $numCode;
        };

        if (0 !== count($this->filter($predicate))) {
            throw new DomainException('Currency numeric code must be unique within the collection');
        }

        return parent::offsetSet($alphaCode, $currency);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($alphaCode)
    {
        $alphaCode = $this->validateAlphaCode($alphaCode);

        return parent::offsetUnset($alphaCode);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($alphaCode)
    {
        $alphaCode = $this->validateAlphaCode($alphaCode);

        return parent::offsetExists($alphaCode);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($alphaCode)
    {
        if (!$this->offsetExists($alphaCode)) {
            throw new OutOfBoundsException(sprintf('Unknown currency alphabetic code: %s', $alphaCode));
        }

        $alphaCode = $this->validateAlphaCode($alphaCode);

        return parent::offsetGet($alphaCode);
    }

    /**
     * @inheritdoc
     */
    public function append($currency)
    {
        $currency = $this->validateCurrency($currency);

        return $this->offsetSet($currency->getAlphaCode(), $currency);
    }

    /**
     * @inheritdoc
     */
    public function exchangeArray($input)
    {
        $input = $this->validateCurrencyList($input);

        return parent::exchangeArray($input);
    }
}
