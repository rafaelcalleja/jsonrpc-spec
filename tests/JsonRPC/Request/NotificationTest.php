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

use JsonRPC\Request\Notification;
use JsonRPC\Request\Request;
use JsonRPC\Tests\TestCase;

class NotificationTest extends TestCase
{
    public function testNotificationObjectHasSpecification2Methods()
    {
        $method = $this->getMock('JsonRPC\Request\Method', array(), array('foo'));
        $params = $this->getMockParam(array('bar'));

        $object = new Notification($method, $params);

        $this->assertSame('2.0', Request::JSONRPC_VERSION);

        $this->assertInstanceOf('JsonRPC\Request\Method', $object->method());
        $this->assertInstanceOf('JsonRPC\Request\Param', $object->params());

        $this->assertInstanceOf('JsonRPC\Request\ProcedureInterface', $object);
        $this->assertInstanceOf('JsonRPC\Batch\BatchInterface', $object);
        $this->assertFalse($object instanceof \JsonRPC\Request\RequestInterface);
        $this->assertInstanceOf('\JsonSerializable', $object);
    }

    /**
     * @dataProvider NotificationSuccessExpectedProvider
     */
    public function testCreateFromString($expected, $string)
    {
        $actual = Notification::fromString($string);
        $this->assertTrue($actual->equals($expected));
    }

    /**
     * @dataProvider NotificationSuccessExpectedProvider
     */
    public function testRequestObjectIsSerializedUsingJSON($object, $expected)
    {
        $actual = json_encode($object);

        $this->assertSame($expected, $actual);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('No error', json_last_error_msg());
    }

    public function NotificationSuccessExpectedProvider()
    {
        return array(
            /* rpc call with positional parameters */
            array(new Notification($this->getMockMethod('update'), $this->getMockParam(array(1, 2, 3, 4, 5), true)), '{"jsonrpc":"2.0","method":"update","params":[1,2,3,4,5]}'),
            array(new Notification($this->getMockMethod('foobar'), $this->getMockParam(null, true)), '{"jsonrpc":"2.0","method":"foobar"}'),
            array(new Notification($this->getMockMethod('notify_hello'), $this->getMockParam(array(7), true)), '{"jsonrpc":"2.0","method":"notify_hello","params":[7]}'),
            array(new Notification($this->getMockMethod('notify_sum'), $this->getMockParam(array(1, 2, 4), true)), '{"jsonrpc":"2.0","method":"notify_sum","params":[1,2,4]}'),
            /* rpc batch*/
            array(
                array(
                    new Notification($this->getMockMethod('update'), $this->getMockParam(array(1, 2, 3, 4, 5), true)),
                    new Notification($this->getMockMethod('foobar'), $this->getMockParam(null, true)),
                    new Notification($this->getMockMethod('notify_hello'), $this->getMockParam(array(7), true)),
                    new Notification($this->getMockMethod('notify_sum'), $this->getMockParam(array(1, 2, 4), true)),
                ),
                '[{"jsonrpc":"2.0","method":"update","params":[1,2,3,4,5]},{"jsonrpc":"2.0","method":"foobar"},{"jsonrpc":"2.0","method":"notify_hello","params":[7]},{"jsonrpc":"2.0","method":"notify_sum","params":[1,2,4]}]',
            ),
        );
    }
}
