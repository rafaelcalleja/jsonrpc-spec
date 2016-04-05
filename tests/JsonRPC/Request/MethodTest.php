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

use JsonRPC\Request\Method;
use JsonRPC\Tests\TestCase;

class MethodTest extends TestCase
{
    public function testEquality()
    {
        $expected = new Method('subtract');
        $actual = new Method('subtract');

        $this->assertTrue($expected->equals($actual));
        $this->assertFalse($expected->equals(new Method('update')));
    }

    /**
     * @dataProvider MethodDecodeExpectedProvider
     **/
    public function testDecodeSuccess($object, $expected)
    {
        $actual = json_encode($object);
        $this->assertSame($expected, $actual);
        $this->assertFalse($object->isSystemExtension());
    }

    /** Mixing positional and named parameters are possible in param object. */
    public function MethodDecodeExpectedProvider()
    {
        return array(
            array(new Method('subtract'), '"subtract"'),
            array(new Method('update'), '"update"'),
            array(new Method('foobar'), '"foobar"'),
        );
    }

    /** Method names that begin with rpc. are reserved for system extensions,
     *   and MUST NOT be used for anything else. Each system extension is defined in a related specification. All system extensions are OPTIONAL.
     *
     * Procedure names that begin with the word rpc followed by a period character (U+002E or ASCII 46)
     * are reserved for rpc-internal methods and extensions and MUST NOT be used for anything else.
     *
     * @dataProvider MethodExpectedSystemExtensionProvider
     */
    public function testRequestMethodNamesBeginWithRPCAreReservedForSystemExtensionOnly($object)
    {
        $this->assertTrue($object->isSystemExtension());
    }

    /* Procedure names that begin with rpc. are reserved for system extensions,
    * and MUST NOT be used for anything else. Each system extension is defined in a related specification.
    * All system extensions are OPTIONAL.**/
    public function MethodExpectedSystemExtensionProvider()
    {
        return array(
            array(new Method(iconv('UTF-8', 'ASCII', 'rpc.system.date'))),
            array(new Method(iconv('UTF-8', 'ASCII', 'rpc.foo.bar'))),
            array(new Method(iconv('UTF-8', 'ASCII', 'rpc.foo'))),
            array(new Method('"rpc\u002Esystem\u002Edate"')),
            array(new Method('"rpc\u002Efoo\u002Ebar"')),
            array(new Method('"rpc\u002Efoo"')),
        );
    }
}
