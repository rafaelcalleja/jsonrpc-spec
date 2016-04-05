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
use JsonRPC\Request\Param;
use JsonRPC\Request\Request;
use JsonRPC\Request\RequestId;
use JsonRPC\Tests\TestCase;

class RequestTest extends TestCase
{
    /** @var $object Request */
    protected $object;

    public function setUp()
    {
        $r = Request::fromString('[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"}]');
        //die(var_dump(get_class($r)));
        $method = $this->getMock('JsonRPC\Request\Method', array(), array('foo'));
        $params = $this->getMockParam(array('bar'));
        $id = $this->getMock('JsonRPC\Request\RequestId');

        $this->object = new Request($method, $params, $id);
    }

    public function testRequestObjectHasSpecification2Methods()
    {
        /* If jsonrpc is missing, the server MAY handle the Request as JSON-RPC V1.0-Request. */
        $this->assertSame('2.0', Request::JSONRPC_VERSION);

        //A Structured value that holds the parameter values to be used during the invocation of the method.
        $this->assertInstanceOf('JsonRPC\Request\Method', $this->object->method());
        $this->assertInstanceOf('JsonRPC\Request\Param', $this->object->params());
        $this->assertInstanceOf('JsonRPC\Request\RequestId', $this->object->id());

        $this->assertInstanceOf('JsonRPC\Request\ProcedureInterface', $this->object);
        $this->assertInstanceOf('JsonRPC\Request\RequestInterface', $this->object);
        $this->assertInstanceOf('JsonRPC\Batch\BatchInterface', $this->object);
        $this->assertInstanceOf('\JsonSerializable', $this->object);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMixingParamsAreNotPossible()
    {
        $mock = $this->getMockBuilder('JsonRPC\Request\Param')
            ->disableOriginalConstructor()
            ->setMethods(array('mode'))
            ->getMock()
        ;

        $mock->expects($this->once())
             ->method('mode')
             ->will($this->returnValue(0));

        $request =  new Request($this->getMock('JsonRPC\Request\Method', array(), array('foo')), $mock, $this->getMock('JsonRPC\Request\RequestId'));

        $this->assertInstanceOf('JsonRPC\Request\RequestInterface', $request);
    }

    /**
     * @dataProvider RequestSuccessExpectedProvider
     */
    public function testRequestObjectIsSerializedUsingJSON($object, $expected)
    {
        $actual = json_encode($object);
        if (is_array($object)) {
            foreach ($object as $b) {
                $this->assertInstanceOf('JsonRPC\Request\RequestInterface', $b);
            }
        } else {
            $this->assertInstanceOf('JsonRPC\Request\RequestInterface', $object);
        }

        $this->assertSame($expected, $actual);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('No error', json_last_error_msg());
    }

    public function RequestSuccessExpectedProvider()
    {
        return array(
            /* rpc call with positional parameters */
            array(new Request($this->getMockMethod('subtract'), $this->getMockParam(array(42, 23), true), $this->getMockRequestId(1)), '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}'),
            array(new Request($this->getMockMethod('subtract'), $this->getMockParam(array(23, 42), true), $this->getMockRequestId(2)), '{"jsonrpc":"2.0","method":"subtract","params":[23,42],"id":2}'),
            /* rpc call with named parameters */
            array(new Request($this->getMockMethod('subtract'), $this->getMockParam(array('subtrahend' => 23, 'minuend' => 42), true), $this->getMockRequestId(3)), '{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":3}'),
            array(new Request($this->getMockMethod('subtract'), $this->getMockParam(array('minuend' => 42, 'subtrahend' => 23), true), $this->getMockRequestId(4)), '{"jsonrpc":"2.0","method":"subtract","params":{"minuend":42,"subtrahend":23},"id":4}'),
            /* rpc call with omitted parameters */
            array(new Request($this->getMockMethod('subtract'), $this->getMockParam(null, true), $this->getMockRequestId(1)), '{"jsonrpc":"2.0","method":"subtract","id":1}'),
            array(new Request($this->getMockMethod('subtract'), $this->getMockParam(null, true), $this->getMockRequestId(2)), '{"jsonrpc":"2.0","method":"subtract","id":2}'),
            array(new Request($this->getMockMethod('subtract'), new Param(null), $this->getMockRequestId(3)), '{"jsonrpc":"2.0","method":"subtract","id":3}'),
            array(new Request($this->getMockMethod('subtract'), null, $this->getMockRequestId(3)), '{"jsonrpc":"2.0","method":"subtract","id":3}'),
            /* rpc batch*/
            array(
                array(
                    new Request($this->getMockMethod('sum'), $this->getMockParam(array(1, 2, 4), true), $this->getMockRequestId('1')),
                    new Request($this->getMockMethod('subtract'), $this->getMockParam(array(42, 23), true), $this->getMockRequestId('2')),
                    new Request($this->getMockMethod('foo.get'), $this->getMockParam(array('name' => 'myself'), true), $this->getMockRequestId('5')),
                    new Request($this->getMockMethod('get_data'), null, $this->getMockRequestId('9')),
                ),
                '[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"},{"jsonrpc":"2.0","method":"foo.get","params":{"name":"myself"},"id":"5"},{"jsonrpc":"2.0","method":"get_data","id":"9"}]',
            ),
        );
    }

    public function testBatchEquality()
    {
        $disordered = '[{"jsonrpc":"2.0","method":"foo.get","params":{"name":"myself"},"id":"5"},{"jsonrpc":"2.0","method":"get_data","id":"9"},{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"},{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"}]';
        $ordered = '[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"},{"jsonrpc":"2.0","method":"foo.get","params":{"name":"myself"},"id":"5"},{"jsonrpc":"2.0","method":"get_data","id":"9"}]';

        $batch =  array(
            new Request($this->getMock('JsonRPC\Request\Method', array(), array('sum')), $this->getMock('JsonRPC\Request\Param', array(), array(array(1, 2, 4))), $this->getMock('JsonRPC\Request\RequestId', array(), array('1'))),
            new Request($this->getMock('JsonRPC\Request\Method', array(), array('subtract')), $this->getMock('JsonRPC\Request\Param', array(), array(array(42, 23))), $this->getMock('JsonRPC\Request\RequestId', array(), array('2'))),
            new Request($this->getMock('JsonRPC\Request\Method', array(), array('foo.get')), $this->getMock('JsonRPC\Request\Param', array(), array(array('name' => 'myself'))), $this->getMock('JsonRPC\Request\RequestId', array(), array('5'))),
            new Request($this->getMock('JsonRPC\Request\Method', array(), array('get_data')), $this->getMock('JsonRPC\Request\Param', array(), array(null)), $this->getMock('JsonRPC\Request\RequestId', array(), array('9'))),
        );

        /*$integration = array(
            new Request(new Method('sum'), new Param(array(1, 2, 4) ), new RequestId("1")),
            new Request(new Method('subtract'), new Param(array(42, 23) ), new RequestId("2")),
            new Request(new Method('foo.get'), new Param(array('name' => 'myself')), new RequestId("5")),
            new Request(new Method('get_data'), null, new RequestId("9"))
        ); */

        $integration = array(
            new Request($this->getMockMethod('sum'), $this->getMockParam(array(1, 2, 4), true), $this->getMockRequestId('1')),
            new Request($this->getMockMethod('subtract'), $this->getMockParam(array(42, 23), true), $this->getMockRequestId('2')),
            new Request($this->getMockMethod('foo.get'), $this->getMockParam(array('name' => 'myself'), true), $this->getMockRequestId('5')),
            new Request($this->getMockMethod('get_data'), null, $this->getMockRequestId('9')),
        );

        $compare = array(
            new Request($this->getMockMethod('sum'), $this->getMockParam(array(1, 2, 4), true), $this->getMockRequestId('1')),
            new Request($this->getMockMethod('subtract'), $this->getMockParam(array(42, 23), true), $this->getMockRequestId('2')),
            new Request($this->getMockMethod('foo.get'), $this->getMockParam(array('name' => 'myself'), true), $this->getMockRequestId('5')),
            new Request($this->getMockMethod('get_data'), null, $this->getMockRequestId('9')),
        );

        $this->assertSame(json_encode($integration), $ordered);
        $actual = Request::fromString($ordered);

        for ($x = 0;$x < count($batch);++$x) {
            $this->assertInstanceOf('\JsonRPC\Request\RequestInterface', $actual[$x]);
        }

        $this->assertTrue($actual->equals($integration));

        $this->assertCount(4, $actual);

        $actual = Request::fromString($disordered);
        $this->assertFalse($actual->equals($batch));
        $this->assertCount(4, $actual);

        $this->assertSame(json_encode($actual[0]), json_encode($compare[2]));
        $this->assertSame(json_encode($actual[1]), json_encode($compare[3]));
        $this->assertSame(json_encode($actual[2]), json_encode($compare[1]));
        $this->assertSame(json_encode($actual[3]), json_encode($compare[0]));

        $this->assertTrue($actual[0]->equals($compare[2]));
        $this->assertTrue($actual[1]->equals($compare[3]));
        $this->assertTrue($actual[2]->equals($compare[1]));
        $this->assertTrue($actual[3]->equals($compare[0]));
    }

    /**
     * @dataProvider RequestSuccessExpectedProvider
     */
    public function testCreateFromString($expected, $string)
    {
        $actual = Request::fromString($string);
        $this->assertTrue($actual->equals($expected));
    }

    public function testRequestAreCorrelatives()
    {
    }

    public function testRequestAreNoCorrelatives()
    {
    }

    /**Every Request, except Notifications, MUST be replied to with a Response.*/
    public function testBehaviorResponse()
    {
    }

    //¿ modificar request ??

    // intentar modificar varios objectos guardando su freferencia y comprobar que el resultado final esta sin mutar
    public function testEquality()
    {
    }

    public function testElementOnlyContainsSingleRequest()
    {
    }
}
