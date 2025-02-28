<?php

namespace Elastica\Test;

use Elastica\Test\Base as BaseTest;
use Elastica\Util;

/**
 * @internal
 */
class UtilTest extends BaseTest
{
    /**
     * @group unit
     *
     * @dataProvider getIsDateMathEscapedPairs
     *
     * @param mixed $requestUri
     * @param mixed $expectedIsEscaped
     */
    public function testIsDateMathEscaped($requestUri, $expectedIsEscaped): void
    {
        $this->assertEquals($expectedIsEscaped, Util::isDateMathEscaped($requestUri));
    }

    public function getIsDateMathEscapedPairs(): array
    {
        return [
            ['', false],
            ['', false],
            ['<log-{now/d}>/type/_search', false],
            ['<log-{now%2Fd}>/type/_search', true],
            ['<logstash-{now/d-2d}>,<logstash-{now/d-1d}>,<logstash-{now/d}>/_search', false],
            ['%3Clogstash-%7Bnow%2Fd-2d%7D%3E%2C%3Clogstash-%7Bnow%2Fd-1d%7D%3E%2C%3Clogstash-%7Bnow%2fd%7D%3E/_search', true],
            ['%3Clogstash-%7Bnow%2Fd-2d%7D%3E%2C%3Clogstash-%7Bnow%2Fd-1d%7D%3E%2C%3Clogstash-%7Bnow%2Fd%7D%3E/_search', true],
        ];
    }

    /**
     * @group unit
     *
     * @dataProvider getEscapeDateMathPairs
     *
     * @param mixed $requestUri
     * @param mixed $expectedEscapedRequestUri
     */
    public function testEscapeDateMath($requestUri, $expectedEscapedRequestUri): void
    {
        $this->assertEquals($expectedEscapedRequestUri, Util::escapeDateMath($requestUri));
    }

    public function getEscapeDateMathPairs(): array
    {
        return [
            ['', ''],
            ['_bulk', '_bulk'],
            ['bulk', 'bulk'],
            ['index/type/id/_create', 'index/type/id/_create'],
            ['index/_warmer', 'index/_warmer'],
            ['index/type/id/_percolate/count', 'index/type/id/_percolate/count'],
            ['<logstash-{now/d}>/_search', '%3Clogstash-%7Bnow%2Fd%7D%3E/_search'],
            ['<log-{now/d}>,log-2011.12.01/log/_refresh', '%3Clog-%7Bnow%2Fd%7D%3E%2Clog-2011.12.01/log/_refresh'],
            [
                '<logstash-{now/d-2d}>,<logstash-{now/d-1d}>,<logstash-{now/d}>/_search',
                '%3Clogstash-%7Bnow%2Fd-2d%7D%3E%2C%3Clogstash-%7Bnow%2Fd-1d%7D%3E%2C%3Clogstash-%7Bnow%2Fd%7D%3E/_search',
            ],
            [
                '<elastic\\\\{ON\\\\}-{now/M}>', // <elastic\\{ON\\}-{now/M}>
                '%3Celastic\\\\{ON\\\\}-%7Bnow%2FM%7D%3E',
            ],
        ];
    }

    /**
     * @group unit
     *
     * @dataProvider getEscapeTermPairs
     *
     * @param mixed $unescaped
     * @param mixed $escaped
     */
    public function testEscapeTerm($unescaped, $escaped): void
    {
        $this->assertEquals($escaped, Util::escapeTerm($unescaped));
    }

    public function getEscapeTermPairs(): array
    {
        return [
            ['', ''],
            ['pragmatic banana', 'pragmatic banana'],
            ['oh yeah!', 'oh yeah\\!'],
            // Separate test below because phpunit seems to have some problems
            // array('\\+-&&||!(){}[]^"~*?:', '\\\\\\+\\-\\&&\\||\\!\\(\\)\\{\\}\\[\\]\\^\\"\\~\\*\\?\\:'),
            ['some signs, can stay.', 'some signs, can stay.'],
        ];
    }

    /**
     * @group unit
     *
     * @dataProvider getReplaceBooleanWordsPairs
     *
     * @param mixed $before
     * @param mixed $after
     */
    public function testReplaceBooleanWords($before, $after): void
    {
        $this->assertEquals($after, Util::replaceBooleanWords($before));
    }

    public function getReplaceBooleanWordsPairs(): array
    {
        return [
            ['to be OR not to be', 'to be || not to be'],
            ['ORIGINAL GIFTS', 'ORIGINAL GIFTS'],
            ['Black AND White', 'Black && White'],
            ['TIMBERLAND Men`s', 'TIMBERLAND Men`s'],
            ['hello NOT kitty', 'hello !kitty'],
            ['SEND NOTIFICATION', 'SEND NOTIFICATION'],
        ];
    }

    /**
     * @group unit
     */
    public function testEscapeTermSpecialCharacters(): void
    {
        $before = '\\+-&&||!(){}[]^"~*?:/<>';
        $after = '\\\\\\+\\-\\&&\\||\\!\\(\\)\\{\\}\\[\\]\\^\\"\\~\\*\\?\\:\\/';

        $this->assertEquals(Util::escapeTerm($before), $after);
    }

    /**
     * @group unit
     */
    public function testToCamelCase(): void
    {
        $string = 'hello_world';
        $this->assertEquals('HelloWorld', Util::toCamelCase($string));

        $string = 'how_are_you_today';
        $this->assertEquals('HowAreYouToday', Util::toCamelCase($string));
    }

    /**
     * @group unit
     */
    public function testToSnakeCase(): void
    {
        $this->assertSame('foo_bar', Util::toSnakeCase('fooBar'));
        $this->assertSame('hello_world', Util::toSnakeCase('HelloWorld'));
        $this->assertSame('how_are_you_today', Util::toSnakeCase('HowAreYouToday'));
    }

    /**
     * @group unit
     */
    public function testConvertDateTimeObjectWithTimezone(): void
    {
        $dateTimeObject = new \DateTime();
        $timestamp = $dateTimeObject->getTimestamp();

        $convertedString = Util::convertDateTimeObject($dateTimeObject);

        $date = \date('Y-m-d\TH:i:sP', $timestamp);

        $this->assertEquals($convertedString, $date);
    }

    /**
     * @group unit
     */
    public function testConvertDateTimeObjectWithoutTimezone(): void
    {
        $dateTimeObject = new \DateTime();
        $timestamp = $dateTimeObject->getTimestamp();

        $convertedString = Util::convertDateTimeObject($dateTimeObject, false);

        $date = \date('Y-m-d\TH:i:s\Z', $timestamp);

        $this->assertEquals($convertedString, $date);
    }

    /**
     * @group unit
     */
    public function testConvertDateWithInt(): void
    {
        $dateInt = 1705361024;

        $convertedString = Util::convertDate($dateInt);

        $date = \date('Y-m-d\TH:i:s\Z', $dateInt);

        $this->assertEquals($convertedString, $date);
    }

    /**
     * @group unit
     */
    public function testConvertDateWithString(): void
    {
        $dateString = 'Mon, 15 Jan 2024 23:23:44 GMT';

        $convertedString = Util::convertDate($dateString);

        $date = \date('Y-m-d\TH:i:s\Z', \strtotime($dateString));

        $this->assertEquals($convertedString, $date);
    }
}
