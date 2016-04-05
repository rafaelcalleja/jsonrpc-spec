<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Tests\Response;

use JsonRPC\Response\Error;
use JsonRPC\Tests\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @dataProvider ErrorResponse
     */
    public function testErrorCanBeEncoded($code, $message, $decoded, $data = null)
    {
        $mock = $this->getMockBuilder('\JsonRPC\Response\CodeId')
            ->disableOriginalConstructor()
            ->setMethods(array('id'))
            ->getMock();

        $mock->expects($this->once())
            ->method('id')
            ->willReturn($code);

        $result = new Error($mock, $message, $data);
        $this->assertInstanceOf('\JsonRPC\Response\ResultInterface', $result);

        $this->assertSame($decoded, json_encode($result));
    }

    public function ErrorResponse()
    {
        return array(
            array(-32601, 'Method not found', '{"code":-32601,"message":"Method not found"}'),
            array(-32700, 'Parse error', '{"code":-32700,"message":"Parse error"}'),
            array(-32600, 'Invalid Request', '{"code":-32600,"message":"Invalid Request"}'),
            array(-32600, 'Invalid Request', '{"code":-32600,"message":"Invalid Request"}'),
            array(-32000, 'Server Error', '{"code":-32000,"message":"Server Error","data":[1,2,3]}', array(1, 2, 3)),
            array(-32001, 'Server Error', '{"code":-32001,"message":"Server Error","data":{"foo":"bar","bar":"foo"}}', array('foo' => 'bar', 'bar' => 'foo')),
            array(-32002, 'Server Error', '{"code":-32002,"message":"Server Error","data":[{}]}', array(new \StdClass())),
        );
    }
}
