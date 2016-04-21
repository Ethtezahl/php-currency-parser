<?php

namespace Pcp\Tests;

use Pcp\Collection;
use Pcp\Currency;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group collection
 */
class CollectionTest extends TestCase
{
    protected $collection;

    public function setUp()
    {
        $this->collection = new Collection([
            new Currency('Foo', 'FoO', '042', 2, false, [
                'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                'BE' => 'BELGIUM',
            ]),
            new Currency('BAR', 'BAR', 69, 2, true, [
                'YO' => 'FOO',
                'LO' => 'BAR',
            ]),
        ]);
    }

    public function tearDown()
    {
        $this->collection = null;
    }

    public function testArrayAccess()
    {
        $this->assertInstanceOf(Currency::class, $this->collection['BAR']);
        $this->assertTrue(isset($this->collection['FOO']));
        unset($this->collection['BAR']);
        $this->assertCount(1, $this->collection);
        $currency = new Currency('Foo bar', 'ZZZ', '125', 2, true, ['YO' => 'YOLO COUNTRY']);
        $this->collection['ZZZ'] = $currency;
        $this->assertSame($currency, $this->collection['ZZZ']);
        $this->assertCount(2, $this->collection);
    }

    /**
     * @expectedException DomainException
     */
    public function testOffsetSetThrowDomainExceptionIfOffsetDiffersFromAlphaCode()
    {
        $this->collection['FOO'] = new Currency('Foo bar', 'BAR', '042', 2, false, [
            'YO' => 'YOLO COUNTRY',
        ]);
    }

    /**
     * @expectedException DomainException
     */
    public function testNewInstanceThrowsExceptionIfCurrenciesSharesTheSameNumericCode()
    {
        $collection = new Collection([
            new Currency('Foo', 'FoO', '069', 2, false, [
                'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                'BE' => 'BELGIUM',
            ]),
            new Currency('BAR', 'BAR', 69, 2, true, [
                'YO' => 'FOO',
                'LO' => 'BAR',
            ]),
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOffsetSetThrowInvalidArgumentExceptionOnSet()
    {
        $this->collection['FOO'] = 'BAR';
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testOffsetGetThrowExceptionOnUnknownCurrency()
    {
        $this->collection['YOL'];
    }

    public function testFilter()
    {
        $predicate = function (Currency $currency) {
            return $currency->isFund();
        };

        $res = $this->collection->filter($predicate);
        $this->assertInstanceOf(Collection::class, $res);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNewInstanceThrowInvalidArgumentException()
    {
        new Collection(['foo']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAppendThrowsInvalidArgumentException()
    {
        $this->collection->append('foo');
    }

    /**
     * @expectedException DomainException
     */
    public function testAppendThrowsDomainException()
    {
        $currency = new Currency('Foo bar', 'BAZ', '042', 2, false, [
            'YO' => 'YOLO COUNTRY',
        ]);

        $this->collection->append($currency);
        $this->assertSame($currency, $this->collection['BAZ']);
    }

    public function testGetByNumCode()
    {
        $this->assertInstanceOf(Currency::class, $this->collection->getByNumCode(42));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetByNumCodeThrowsOutOfBoundsException()
    {
        $this->collection->getByNumCode(999);
    }

    public function testHasNumCode()
    {
        $this->assertTrue($this->collection->hasNumCode(42));
        $this->assertFalse($this->collection->hasNumCode(31));
    }

    public function testExchangeArray()
    {
        $data = $this->collection->getArrayCopy();
        $altData = $this->collection->exchangeArray([]);
        $this->assertSame($data, $altData);
        $this->assertCount(0, $this->collection);
    }
}
