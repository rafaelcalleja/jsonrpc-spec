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

use JsonRPC\Request\RequestId;
use JsonRPC\Tests\TestCase;

class RequestIdTest extends TestCase
{
    /*An identifier established by the Client that MUST contain a String, Number, or NULL value if included.
    If it is not included it is assumed to be a notification. The value SHOULD normally not be Null [1] and Numbers SHOULD NOT contain fractional parts [2]
    The Server MUST reply with the same value in the Response object if included. This member is used to correlate the context between the two objects.
    [1] The use of Null as a value for the id member in a Request object is discouraged, because this specification uses a value of Null for Responses with an unknown id.
        Also, because JSON-RPC 1.0 uses an id value of Null for Notifications this could cause confusion in handling.
    [2] Fractional parts may be problematic, since many decimal fractions cannot be represented exactly as binary fractions.
    * */

    // Strategies
    /**
     * - User Generate (User crea el ID)
     * - Persistent Generate ( La bbdd debe devolver el ID, por lo que hay que esperar a persistir)
     * - Application Generate (UUID).
     *
     * @dataProvider validIds
     */
    public function testValidId($id)
    {
        $object = new RequestId($id);
        $this->assertSame($id, $object->id());
    }

    public function testUuidIsAutogenerated()
    {
        $object = new RequestId();
        $this->assertSame(36, strlen($object->id()));
    }

    public function testEquality()
    {
        $expected = new RequestId(1);
        $actual = new RequestId('1');

        $this->assertFalse($expected->equals($actual));

        $this->assertTrue($expected->equals(new RequestId(1)));
        $this->assertTrue($actual->equals(new RequestId('1')));

        $this->assertFalse($expected->equals(new RequestId()));
    }

    /**
     * @dataProvider DecodeValidIdsProvider
     **/
    public function testDecodeSuccess($object, $expected)
    {
        $actual = json_encode($object);
        $this->assertSame($expected, $actual);
    }

    /** Mixing positional and named parameters are possible in param object. */
    public function DecodeValidIdsProvider()
    {
        return array(
            array(new RequestId(1), '1'),
            array(new RequestId(2), '2'),
            array(new RequestId('a'), '"a"'),
            array(new RequestId('b'), '"b"'),
            array(new RequestId('25769c6c-d34d-4bfe-ba98-e0ee856f3e7a'), '"25769c6c-d34d-4bfe-ba98-e0ee856f3e7a"'),
        );
    }

    /**
     * @dataProvider invalidIds
     * @expectedException \InvalidArgumentException
     */
    public function testIdsAreInvalid($id)
    {
        $object = new RequestId($id);
    }

    public function invalidIds()
    {
        return array(
            array(1.2),
            array(false),
            array(true),
            array(new \StdClass()),
            array(function () {}),
            array(-1),
            array(-1.2),
        );
    }
    public function validIds()
    {
        return array(
            array(1),
            array(2),
            array('a'),
            array('b'),
            array('25769c6c-d34d-4bfe-ba98-e0ee856f3e7a'),
        );
    }
}
