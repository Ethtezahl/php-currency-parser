<?php

namespace Pcp\Tests;

use DomainException;
use InvalidArgumentException;
use Pcp\Currency;
use PHPUnit_Framework_TestCase as TestCase;
use StdClass;

/**
 * @group currency
 */
class CurrencyTest extends TestCase
{
    protected $currency;

    public function setUp()
    {
        $this->currency = new Currency('Foo', 'FoO', '042', 2, false, [
            'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
            'BE' => 'BELGIUM',
        ]);
    }

    public function tearDown()
    {
        $this->currency = null;
    }

    public function testNewInstanceIsWellCreated()
    {
        $this->assertSame('Foo', $this->currency->getName());
        $this->assertSame('FOO', $this->currency->getAlphaCode());
        $this->assertSame(42, $this->currency->getNumCode());
        $this->assertSame(2, $this->currency->getMinorUnitExponent());
        $this->assertSame([
            'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
            'BE' => 'BELGIUM',
        ], $this->currency->getCountries());
        $this->assertFalse($this->currency->isFund());
        $this->assertTrue($this->currency->isLegalIn('CD'));
        $this->assertFalse($this->currency->isLegalIn('FR'));
        $this->assertSame('FOO', (string) $this->currency);
    }

    public function testNewInstanceIsWellCreatedWithMinorExponentIsNull()
    {
        $currency = new Currency('Foo', 'FoO', '042', null, false, [
            'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
            'BE' => 'BELGIUM',
        ]);

        $this->assertNull($currency->getMinorUnitExponent());
    }

    public function testToArray()
    {
        $this->assertSame([
            'name' => 'Foo',
            'alphaCode' => 'FOO',
            'numCode' => 42,
            'minorUnitExponent' => 2,
            'isFund' => false,
            'countries' => [
                'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                'BE' => 'BELGIUM',
            ],
        ], $this->currency->toArray());
    }

    public function testJsonSerialize()
    {
        $this->assertSame([
            'name' => 'Foo',
            'alphaCode' => 'FOO',
            'numCode' => '042',
            'minorUnitExponent' => 2,
            'isFund' => false,
            'countries' => [
                'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                'BE' => 'BELGIUM',
            ],
        ], json_decode(json_encode($this->currency), true));
    }

    public function testSetState()
    {
        $newCurrency = eval('return '.var_export($this->currency, true).';');
        $this->assertEquals($this->currency, $newCurrency);
    }

    /**
     * @dataProvider invalidConstructor
     */
    public function testNewInstanceThrowsException(
        $name,
        $alphaCode,
        $numCode,
        $minorUnitExponent,
        $isFund,
        $countries,
        $expectedExceptionClass
    ) {
        $this->setExpectedException($expectedExceptionClass);

        new Currency($name, $alphaCode, $numCode, $minorUnitExponent, $isFund, $countries);
    }

    public function invalidConstructor()
    {
        return [
            'Invalid Name Argument' => [
                'name' => new StdClass(),
                'alphaCode' => 'Fo0',
                'numCode' => '042',
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => InvalidArgumentException::class,
            ],
            'Name argument can not be empty' => [
                'name' => '',
                'alphaCode' => 'Fo0',
                'numCode' => '042',
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
            'alphaCode must be a stringable' => [
                'name' => 'Foobar',
                'alphaCode' => new StdClass(),
                'numCode' => '042',
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => InvalidArgumentException::class,
            ],
            'alphaCode must only contains letters' => [
                'name' => 'Foobar',
                'alphaCode' => 'F00',
                'numCode' => '042',
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
            'alphaCode must only contains 3 letters' => [
                'name' => 'Foobar',
                'alphaCode' => 'FAFAFA',
                'numCode' => '042',
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
            'numCode must be a stringable' => [
                'name' => 'Foobar',
                'alphaCode' => 'FFF',
                'numCode' => new StdClass(),
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => InvalidArgumentException::class,
            ],
            'numCode must be a positive integer' => [
                'name' => 'Foobar',
                'alphaCode' => 'FFF',
                'numCode' => -3,
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
            'exp must a positive integer, 0 or null' => [
                'name' => 'Foobar',
                'alphaCode' => 'FFF',
                'numCode' => '424',
                'minorUnitExponent' => -2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'BE' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
            'Invalid Country Argument' => [
                'name' => new StdClass(),
                'alphaCode' => 'Fo0',
                'numCode' => 42,
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => new StdClass(),
                    'BE' => 'BELGIUM',
                ],
                'exception' => InvalidArgumentException::class,
            ],
            'Country argument can not be empty' => [
                'name' => 'Foo',
                'alphaCode' => 'FoO',
                'numCode' => 42,
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    '' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'X1' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
            'Country argument should be a valid TLD pattern' => [
                'name' => 'Foo',
                'alphaCode' => 'FoO',
                'numCode' => 42,
                'minorUnitExponent' => 2,
                'isFund' => false,
                'countries' => [
                    'CD' => 'CONGO (THE DEMOCRATIC REPUBLIC OF THE)',
                    'X1' => 'BELGIUM',
                ],
                'exception' => DomainException::class,
            ],
        ];
    }
}
