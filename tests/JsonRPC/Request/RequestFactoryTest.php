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

use JsonRPC\Request\RequestFactory;
use JsonRPC\Tests\TestCase;

class RequestFactoryTest extends TestCase
{
    protected $factory;

    public function setUp()
    {
        $this->factory = new RequestFactory();
    }

    /**
     * @dataProvider RequestProvider
     */
    public function testFactoryReturnCorrectObjectTypeFromJsonDecoded($json, $class)
    {
        $actual = $this->factory->create($json);
        $this->assertInstanceOf($class, $actual);
        $this->assertSame($json, json_encode($actual));
    }

    public function RequestProvider()
    {
        return array(
            array('{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}', '\JsonRPC\Request\RequestInterface'),
            array('{"jsonrpc":"2.0","method":"subtract","params":[23,42],"id":2}', '\JsonRPC\Request\RequestInterface'),
            array('{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":3}', '\JsonRPC\Request\RequestInterface'),
            array('{"jsonrpc":"2.0","method":"subtract","params":{"minuend":42,"subtrahend":23},"id":4}', '\JsonRPC\Request\RequestInterface'),
            array('{"jsonrpc":"2.0","method":"subtract","id":1}', '\JsonRPC\Request\RequestInterface'),
            array('{"jsonrpc":"2.0","method":"subtract","id":3}', '\JsonRPC\Request\RequestInterface'),
            array('{"jsonrpc":"2.0","method":"update","params":[1,2,3,4,5]}', '\JsonRPC\Request\ProcedureInterface'),
            array('{"jsonrpc":"2.0","method":"foobar"}', '\JsonRPC\Request\ProcedureInterface'),
            array('{"jsonrpc":"2.0","method":"notify_hello","params":[7]}', '\JsonRPC\Request\ProcedureInterface'),
            array('{"jsonrpc":"2.0","method":"notify_sum","params":[1,2,4]}', '\JsonRPC\Request\ProcedureInterface'),
        );
    }
}
