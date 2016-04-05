<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Tests\Request;

use JsonRPC\Request\Param;
use JsonRPC\Tests\TestCase;

class ParamTest extends TestCase
{
    /**
     * @dataProvider RequestDecodeExpectedProvider
     ***/
    public function testCreateFromString($expected, $string)
    {
        $actual = Param::fromString($string);
        $this->assertTrue($actual->equals($expected));
    }

    public function testParamAreCaseSensitive()
    {
    }

    public function testEquality()
    {
        $expected = new Param(array(1));
        $actual = new Param(array(1));

        $this->assertTrue($expected->equals($actual));
        $this->assertFalse($expected->equals(new Param()));
    }

    /**
     * @dataProvider NameExpectedProvider
     **/
    public function testIndexNameIsActive($arguments)
    {
        $param = new Param($arguments);

        $expected = Param::INDEX_NAME;
        $this->assertSame($expected, $param->mode());
        $this->assertTrue($param->isIndexByName());
        $this->assertFalse($param->isIndexByPosition());
    }

    /**
     * @dataProvider PositionExpectedProvider
     **/
    public function testIndexPositionIsActive($arguments)
    {
        $param = new Param($arguments);

        $expected = Param::INDEX_POSITION;
        $this->assertSame($expected, $param->mode());
        $this->assertTrue($param->isIndexByPosition());
        $this->assertFalse($param->isIndexByName());
    }

    /**
     * @dataProvider MixingExpectedProvider
     **/
    public function testModeMixedFlagsAreActivated($arguments)
    {
        $param = new Param($arguments);

        $expected = (Param::INDEX_NAME & Param::INDEX_POSITION);
        $this->assertSame($expected, $param->mode());
        $this->assertTrue($param->isIndexByBoth());
        $this->assertFalse($param->isIndexByPosition());
        $this->assertFalse($param->isIndexByName());
    }

    public function NameExpectedProvider()
    {
        /* rpc call named parameters  */
        return array(
            array(array('foo' => 'bar', 'bar' => 'foo')),
            array(array('foo' => 'foo', 'foo' => 'bar')),
        );
    }

    public function PositionExpectedProvider()
    {
        return array(
            /* rpc call positional parameters  */
            array(array(0 => 'foo', 1 => 'bar')),
            array(array(42, 24)),
            array(array(1 => 'foo', 5 => 'bar')),
            array(array(3 => 'foo', 24)),
        );
    }

    /** Mixing positional and named parameters in one call is not possible. */
    public function MixingExpectedProvider()
    {
        return array(
            /* rpc call mixing named and positional parameters  */
            array(array(0 => 'foo', 'subtrahend' => 23)),
            array(array('minuend' => 42, 42)),
        );
    }

    /**
     * @dataProvider RequestDecodeExpectedProvider
     **/
    public function testDecodeSuccess($object, $expected)
    {
        $actual = json_encode($object);
        $this->assertSame($expected, $actual);
    }

    /** Mixing positional and named parameters are possible in param object. */
    public function RequestDecodeExpectedProvider()
    {
        return array(
            /* rpc call with positional parameters */
            array(new Param(array(42, 23)), '[42,23]'),
            array(new Param(array(23, 42)), '[23,42]'),
            /* rpc call with named parameters */
            array(new Param(array('subtrahend' => 23, 'minuend' => 42)), '{"subtrahend":23,"minuend":42}'),
            array(new Param(array('minuend' => 42, 'subtrahend' => 23)), '{"minuend":42,"subtrahend":23}'),
            /* rpc call with omitted parameters */
            array(new Param(null), '{}'),
            array(new Param(null), '{}'),
            /*mixed*/
            array(new Param(array(23, 'minuend' => 42)), '{"0":23,"minuend":42}'),
            array(new Param(array('minuend' => 42, 23)), '{"minuend":42,"0":23}'),
            array(new Param(array('minuend' => 42, 23, 42)), '{"minuend":42,"0":23,"1":42}'),
            array(new Param(array(null, 23, 42, null)), '[null,23,42,null]'),
            array(new Param(array(23, 42, 'minuend' => 42, null)), '{"0":23,"1":42,"minuend":42,"2":null}'),
        );
    }

    public function testIsCountableInterface()
    {
        $this->assertInstanceOf('\Countable', new Param());
    }
}
