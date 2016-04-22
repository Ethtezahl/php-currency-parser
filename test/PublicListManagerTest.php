<?php

namespace Pcp\Tests;

use org\bovigo\vfs\vfsStream;
use Pcp\Collection;
use Pcp\Http\Client;
use Pcp\PublicListManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group manager
 */
class PublicListManagerTest extends TestCase
{
    protected $manager;

    protected $cacheDir;

    protected $root;

    protected $client;

    public function setUp()
    {
        $bodyXML = <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<ISO_4217 Pblshd="2016-02-24">
    <CcyTbl>
        <CcyNtry>
            <CtryNm>ANTARCTICA</CtryNm>
            <CcyNm>No universal currency</CcyNm>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>AFGHANISTAN</CtryNm>
            <CcyNm>Afghani</CcyNm>
            <Ccy>AFN</Ccy>
            <CcyNbr>971</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>ÅLAND ISLANDS</CtryNm>
            <CcyNm>Euro</CcyNm>
            <Ccy>EUR</Ccy>
            <CcyNbr>978</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>FRANCE</CtryNm>
            <CcyNm>Euro</CcyNm>
            <Ccy>EUR</Ccy>
            <CcyNbr>978</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>ALBANIA</CtryNm>
            <CcyNm>Lek</CcyNm>
            <Ccy>ALL</Ccy>
            <CcyNbr>008</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>KOREA (THE DEMOCRATIC PEOPLE’S REPUBLIC OF)</CtryNm>
            <CcyNm>North Korean Won</CcyNm>
            <Ccy>KPW</Ccy>
            <CcyNbr>408</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>KOREA (THE REPUBLIC OF)</CtryNm>
            <CcyNm>Won</CcyNm>
            <Ccy>KRW</Ccy>
            <CcyNbr>410</CcyNbr>
            <CcyMnrUnts>0</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>BOLIVIA (PLURINATIONAL STATE OF)</CtryNm>
            <CcyNm IsFund="true">Mvdol</CcyNm>
            <Ccy>BOV</Ccy>
            <CcyNbr>984</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>INTERNATIONAL MONETARY FUND (IMF) </CtryNm>
            <CcyNm>SDR (Special Drawing Right)</CcyNm>
            <Ccy>XDR</Ccy>
            <CcyNbr>960</CcyNbr>
            <CcyMnrUnts>N.A.</CcyMnrUnts>
        </CcyNtry>
        <CcyNtry>
            <CtryNm>BELGIUM</CtryNm>
            <CcyNm>Euro</CcyNm>
            <Ccy>EUR</Ccy>
            <CcyNbr>978</CcyNbr>
            <CcyMnrUnts>2</CcyMnrUnts>
        </CcyNtry>
    </CcyTbl>
</ISO_4217>
EOF;

        $bodyJson = <<<JSON
[
    {
        "ISO3166-1-Alpha-2": "AF",
        "currency_country_name": "AFGHANISTAN"
    },
    {
        "ISO3166-1-Alpha-2": "KP",
        "currency_country_name": "KOREA, DEMOCRATIC PEOPLE\u2019S REPUBLIC OF"
    },
    {
        "ISO3166-1-Alpha-2": "KR",
        "currency_country_name": "KOREA, REPUBLIC OF"
    },
    {
        "ISO3166-1-Alpha-2": "FI",
        "currency_country_name": "FINLAND"
    },
    {
        "ISO3166-1-Alpha-2": "FR",
        "currency_country_name": "FRANCE"
    },
    {
        "ISO3166-1-Alpha-2": "AX",
        "currency_country_name": "\u00c5LAND ISLANDS"
    },
    {
        "ISO3166-1-Alpha-2": "AL",
        "currency_country_name": "ALBANIA"
    },
    {
        "ISO3166-1-Alpha-2": "CD",
        "currency_country_name": ""
    }
]
JSON;
        $this->client = $this->getMock(Client::class);
        $this->client
            ->method('getBody')
            ->will($this->onConsecutiveCalls($bodyXML, $bodyJson));

        $this->root = vfsStream::setup('foo');
        vfsStream::create(['cache' => []], $this->root);
        $this->cacheDir = vfsStream::url('foo/cache');

        $this->manager = new PublicListManager($this->cacheDir, $this->client);
    }

    public function tearDown()
    {
        $this->manager = null;
        $this->cacheDir = null;
        $this->root = null;
        $this->client = null;
    }

    public function testRefresh()
    {
        $this->assertFileNotExists($this->cacheDir.'/'.PublicListManager::FILE_CURRENCY_XML);
        $this->assertFileNotExists($this->cacheDir.'/'.PublicListManager::FILE_COUNTRY_CODE_JSON);
        $this->assertFileNotExists($this->cacheDir.'/'.PublicListManager::FILE_CURRENCY_PHP);
        $this->manager->refreshList();
        $this->assertFileExists($this->cacheDir.'/'.PublicListManager::FILE_CURRENCY_XML);
        $this->assertFileExists($this->cacheDir.'/'.PublicListManager::FILE_COUNTRY_CODE_JSON);
        $this->assertFileExists($this->cacheDir.'/'.PublicListManager::FILE_CURRENCY_PHP);
    }

    public function testGetListFromCache()
    {
        $this->assertInstanceOf(Collection::class, (new PublicListManager())->getList());
        $this->assertInstanceOf(Collection::class, $this->manager->getList());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWriteToFileSystemThrowException()
    {
        $manager = new PublicListManager('/usfsdfqfdf', $this->client);
        $manager->refreshList();
    }
}
