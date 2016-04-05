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

use JsonRPC\Response\Response;
use JsonRPC\Response\ResultInterface;
use JsonRPC\Tests\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @dataProvider ExceptionResponse
     */
    public function testResponseHandleResultExceptions($exception, $request)
    {
        $e = new $exception();

        $object = new Response($request);
        $data = uniqid();

        $object = $object->makeException($e, $data);
        $this->assertInstanceOf('\JsonRPC\Response\Error', $object->result());
        $this->assertSame($data, $object->result()->data());
    }

    /**
     * @dataProvider ErrorResponse
     */
    public function testInvalidRequestHasImmutableErrorIfItIsSuccess($request, $class)
    {
        $object = new Response($request);

        $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $object = $object->handleResult($mock);

        if (!$object->request() instanceof $class) {
            $this->assertNotSame($mock, $object->result());
        }
    }

    public function testWhenErrorResponseObjectExists()
    {
    }

    /**
     * @dataProvider ErrorResponse
     */
    public function testErrorResult($request, $class, $result, $mockResult)
    {
        $object = new Response($request);
        $mock = $this->getMockBuilder('\JsonRPC\Response\Error')
            ->disableOriginalConstructor()
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $object = $object->handleResult($mock);

        if ($object->request() instanceof $class) {
            $this->assertInstanceOf($class, $object->request());
            $this->assertTrue($object->id()->equals($object->request()->id()));
        } else {
            $this->assertNull($object->id());
            $this->assertNull($object->request());
        }

        $mock->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue(json_decode($mockResult, true)));

        $this->assertSame($result, json_encode($object));
    }

    /**
     * @dataProvider RequestProvider
     */
    public function testSuccessResult($request, $class, $result, $mockResult)
    {
        $object = new Response($request);
        $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $object = $object->handleResult($mock);
        $this->assertInstanceOf($class, $object->request());

        $mock->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue($mockResult));

        $this->assertJsonStringEqualsJsonString($result, json_encode($object));
    }

    /**
     * @dataProvider RequestProvider
     */
    public function testSuccessResultFromCollection($request, $class, $result, $mockResult)
    {
        $object = new Response($request);
        $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $collection = $this->getMockBuilder('\JsonRPC\Response\ResultCollection')
            ->setMethods(array())
            ->getMock();

        $collection->expects($this->once())
            ->method('push')
        ;

        $collection->expects($this->once())
            ->method('pop')
            ->willReturn($mock)
        ;

        $collection->push($mock);
        $object = $object->handleResult($collection);
        $this->assertInstanceOf($class, $object->request());

        $mock->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue($mockResult));

        $this->assertJsonStringEqualsJsonString($result, json_encode($object));
    }

    /**
     * @dataProvider RequestProvider
     */
    public function testAddResultReplaceOriginal($request)
    {
        $object = new Response($request);
        $mock = $this->getMock('\JsonRPC\Response\ResultInterface');

        $copied = $object->handleResult($mock);
        $this->assertFalse($object->equals($copied));
        $this->assertFalse($object->result() instanceof ResultInterface);
        $this->assertInstanceOf('\JsonRPC\Response\ResultInterface', $copied->result());

        $second = $object->handleResult($mock);
        $this->assertTrue($second->equals($copied));
    }

    public function ExceptionResponse()
    {
        return array(
            array('JsonRPC\Exception\MethodNotFoundException', '{"jsonrpc":"2.0","method":"foobar","id":"1"}'),
            array('JsonRPC\Exception\InvalidParamsException', '{"jsonrpc":"2.0","method":"foobar","id":"2"}'),
            array('JsonRPC\Exception\InternalErrorException', '{"jsonrpc":"2.0","method":"foobar","id":"3"}'),
            array('JsonRPC\Exception\ServerErrorException', '{"jsonrpc":"2.0","method":"foobar","id":"4"}'),
        );
    }

    public function SuccessResponse()
    {
        return array(
               /* rpc call with positional parameters*/
               array('{"jsonrpc":"2.0","result":19,"id":1}', '19'),
               array('{"jsonrpc":"2.0","result":-19,"id":2}', '-19'),
               /* rpc call with named parameters */
               array('{"jsonrpc":"2.0","result":19,"id":3}', '19'),
               array('{"jsonrpc":"2.0","result":19,"id":4}', '19'),

           );
    }

    public function ErrorResponse()
    {
        return array(
            /* rpc call of non-existent method:*/
            array('{"jsonrpc":"2.0","method":"foobar","id":"1"}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}', '{"code":-32601,"message":"Method not found"}'),
            /* rpc call with invalid JSON*/
            array('{"jsonrpc":"2.0","method":"foobar,"params":"bar","baz]', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}', '{"code":-32700,"message":"Parse error"}'),
            /*rpc call with invalid Request object*/
            array('{"jsonrpc":"2.0","method":1,"params":"bar"}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}', '{"code":-32600,"message":"Invalid Request"}'),
            /*rpc call with an empty Array:--> []*/
            array('[]', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}', '{"code":-32600,"message":"Invalid Request"}'),
        );
    }

    /**
     * @dataProvider NotificationProvider
     */
    public function testNoResponseWhenIsNotification($json, $class)
    {
        $actual = new Response($json);

        $this->assertInstanceOf($class, $actual->request());
        $this->assertSame($json, json_encode($actual->request()));
        $this->assertSame(json_encode(''), json_encode($actual));
        $this->assertNull($actual->id());
    }

    /**
     * @dataProvider RequestProvider
     */
    public function testResponseWhenIsRequest($json, $class)
    {
        $actual = new Response($json);

        $this->assertInstanceOf($class, $actual->request());
        $this->assertSame($json, json_encode($actual->request()));
        $this->assertTrue((bool) json_encode($actual));
        $this->assertTrue($actual->id()->equals($actual->request()->id()));
    }

    public function RequestProvider()
    {
        return array(
            array('{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","result":19,"id":1}', '19'),
            array('{"jsonrpc":"2.0","method":"subtract","params":[23,42],"id":2}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","result":-19,"id":2}', '-19'),
            array('{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":3}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","result":19,"id":3}', '19'),
            array('{"jsonrpc":"2.0","method":"subtract","params":{"minuend":42,"subtrahend":23},"id":4}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","result":19,"id":4}', '19'),
            array('{"jsonrpc":"2.0","method":"subtract","id":1}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","result":19,"id":1}', '19'),
            array('{"jsonrpc":"2.0","method":"subtract","id":3}', '\JsonRPC\Request\RequestInterface', '{"jsonrpc":"2.0","result":-19,"id":3}', '-19'),
        );
    }

    public function NotificationProvider()
    {
        return array(
            array('{"jsonrpc":"2.0","method":"update","params":[1,2,3,4,5]}', '\JsonRPC\Request\ProcedureInterface'),
            array('{"jsonrpc":"2.0","method":"foobar"}', '\JsonRPC\Request\ProcedureInterface'),
            array('{"jsonrpc":"2.0","method":"notify_hello","params":[7]}', '\JsonRPC\Request\ProcedureInterface'),
            array('{"jsonrpc":"2.0","method":"notify_sum","params":[1,2,4]}', '\JsonRPC\Request\ProcedureInterface'),
        );
    }

    /**
     * @dataProvider BatchSuccessRequestProvider
     */
    public function testResponseResultWithoutCollectionWhenIsBatchRequest($json)
    {
        $object = new Response($json);

        $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $copy = $object->handleResult($mock);

        $this->assertFalse($object->equals($copy));
        $this->assertNotSame(json_encode($copy), json_encode($object));
    }

    /**
     * @dataProvider BatchSuccessRequestProvider
     */
    public function testResponseConsecutiveBatchRequests($json, $result, $mockResults)
    {
        $object = new Response($json);
        $mockResults = json_decode($mockResults, true);

        $collection = $this->
        getMockBuilder('\JsonRPC\Response\ResultCollection')
            ->setMethods(array())
            ->getMock();

        $collection->expects($this->exactly(count($object)))
            ->method('push')
        ;

        $map = array();
        for ($x = 0;$x < count($object);++$x) {
            $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
                ->setMethods(array('jsonSerialize'))
                ->getMock();

            $mock->expects($this->once())
                ->method('jsonSerialize')
                ->will($this->returnValue($mockResults[$x]));

            $map[] = $mock;

            $collection->push($mock);
        }

        $collection->expects($this->exactly(count($object)))
            ->method('pop')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($map));

        $object = $object->handleResult($collection);

        $this->assertJsonStringEqualsJsonString($result, json_encode($object));
    }

    /**
     * @dataProvider BatchSuccessRequestProvider
     */
    public function testResponseWhenIsBatchRequestsAtConcreteRequest($json, $result, $mockResults)
    {
        $object = new Response($json);
        $mockResults = json_decode($mockResults, true);

        foreach ($object as $index => $single) {
            $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
                ->setMethods(array('jsonSerialize'))
                ->getMock();

            $object = $object->resolveResponse($mock, $single);

            $mock->expects($this->once())
                ->method('jsonSerialize')
                ->will($this->returnValue($mockResults[$index]));
        }

        $this->assertJsonStringEqualsJsonString($result, json_encode($object));
    }

    /**
     * @dataProvider BatchErrorRequestProvider
     */
    public function testResponseWhenIsBatchRequestsWithError($json, $result)
    {
        $object = new Response($json);
        $iterations = 0;

        foreach ($object as $index => $single) {
            $mock = $this->getMockBuilder('\JsonRPC\Response\Success')
                ->setMethods(array('jsonSerialize'))
                ->getMock();

            $object = $object->resolveResponse($mock, $single);
            $this->assertInstanceOf('\JsonRPC\Response\Error', $single->result());
            ++$iterations;
        }

        $this->assertInstanceOf('\JsonRPC\Response\Error', $object->result());
        $this->assertJsonStringEqualsJsonString($result, json_encode($object));
        $this->assertSame($iterations, count($object));
    }

    public function testBatchWithRequestAndNotificationSuccessAndError()
    {
        $request = '[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},{"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},{"foo": "boo"},{"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},{"jsonrpc": "2.0", "method": "get_data", "id": "9"}]';

        $expected = '[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},{"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}]';

        $object = new Response($request);

        $success1 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $success1->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue('7'));

        $notification2 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $notification2->expects($this->never())
            ->method('jsonSerialize')
            ->will($this->returnValue(''));

        $success3 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $success3->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue('19'));

        $successCantOverwrite = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $successCantOverwrite->expects($this->never())
            ->method('jsonSerialize')
            ->will($this->returnValue('can\'t overwrite previous exception'));

        $error5 = $this->getMockBuilder('\JsonRPC\Response\Error')
            ->disableOriginalConstructor()
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $error5->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue(json_decode('{"code": -32601, "message": "Method not found"}')));

        $success6 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $success6->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue(array('hello', 5)));

        $collection = $this->getMockBuilder('\JsonRPC\Response\ResultCollection')
            ->setMethods(array('pop'))
            ->getMock();

        $collection->expects($this->exactly(6))
            ->method('pop')
            ->willReturnOnConsecutiveCalls(
                 $success1, $notification2, $success3, $successCantOverwrite, $error5, $success6
            );

        //Real context require PUSH each ResultInterface to the collection, omitted on test
        $result = $object->handleResult($collection);
        $json = json_encode($result);
        $this->assertFalse($object->equals($result));
        $this->assertJsonStringEqualsJsonString($expected, $json);
    }

    public function testBatchResponsesStatusSingleMode()
    {
        $request = '[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},{"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},{"foo": "boo"},{"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},{"jsonrpc": "2.0", "method": "get_data", "id": "9"}]';
        $expected = '[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},{"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}]';

        $object = new Response($request);

        $success1 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $success1->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue('7'));

        $notification2 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $notification2->expects($this->never())
            ->method('jsonSerialize')
            ->will($this->returnValue(''));

        $success3 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $success3->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue('19'));

        $successCantOverwrite = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $successCantOverwrite->expects($this->never())
            ->method('jsonSerialize')
            ->will($this->returnValue('can\'t overwrite previous exception'));

        $error5 = $this->getMockBuilder('\JsonRPC\Response\Error')
            ->disableOriginalConstructor()
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $error5->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue(json_decode('{"code": -32601, "message": "Method not found"}')));

        $success6 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $success6->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue(array('hello', 5)));

        $result = $object->handleResult($success1);
        $this->assertFalse($object->equals($result));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc":"2.0","error":null,"id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":null,"id":"5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($result));

        $result1 = $result->handleResult($notification2);
        $this->assertFalse($result1->equals($result));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc":"2.0","error":null,"id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":null,"id":"5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($result1));
        $this->assertSame(json_encode($result), json_encode($result1)); //SAME JSON !== OBJECTS

        $result2 = $result1->handleResult($success3);
        $this->assertFalse($result2->equals($result1));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":null,"id":"5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($result2));
        $this->assertNotSame(json_encode($result1), json_encode($result2));

        $result3 = $result2->handleResult($successCantOverwrite);
        $this->assertFalse($result3->equals($result2));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":null,"id":"5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($result3));
        $this->assertSame(json_encode($result2), json_encode($result3)); //SAME JSON !== OBJECTS

        $result4 = $result3->handleResult($error5);
        $this->assertFalse($result4->equals($result3));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($result4));
        $this->assertNotSame(json_encode($result3), json_encode($result4));

        $result5 = $result4->handleResult($success6);
        $this->assertFalse($result5->equals($result4));
        $this->assertJsonStringEqualsJsonString($expected, json_encode($result5));
        $this->assertNotSame(json_encode($result4), json_encode($result5));

        $r1 = $result1->handleResult($error5);
        $r2 = $result1->handleResult($error5);
        $r3 = $result1->handleResult($error5);

        $this->assertTrue($r1->equals($r2));
        $this->assertTrue($r2->equals($r3));
        $this->assertTrue($r3->equals($r1));
        $this->assertTrue($r2->equals($r1));

        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":null,"id":"5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($r1));

        $r4 = $result3->handleResult($success1);
        $r5 = $result3->handleResult($success1);

        $this->assertTrue($r4->equals($r5));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc": "2.0", "result": 7, "id": "5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($r4));

        $r6 = $result4->handleResult($error5);
        $r7 = $result4->handleResult($error5);

        $this->assertTrue($r6->equals($r7));
        $this->assertFalse($result5->equals($r7));

        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "result": 7, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},{"jsonrpc":"2.0", "error": {"code": -32601, "message": "Method not found"},"id":"9"}]', json_encode($r6));

        $r8 = $r6->handleResult($error5);
        $r9 = $r7->handleResult($error5);

        $this->assertTrue($r8->equals($r9));
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"},{"jsonrpc": "2.0", "result": 19, "id": "2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},{"jsonrpc":"2.0", "error": {"code": -32601, "message": "Method not found"},"id":"9"}]', json_encode($r8));

        $custom = new Response(json_encode($object[4]->request()));
        $r10 = $object->resolveResponse($success6, $custom);
        $this->assertJsonStringEqualsJsonString('[{"jsonrpc":"2.0","error":null,"id":"1"},{"jsonrpc":"2.0","error":null,"id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0", "result": ["hello", 5],"id":"5"},{"jsonrpc":"2.0","error":null,"id":"9"}]', json_encode($r10));

        $r11 = $object;

        foreach ($object as $request) {
            $response = new Response($request->originalRequest());
            $r11 = $r11->resolveResponse($success1, $response);
        }

        $this->assertJsonStringEqualsJsonString('[{"jsonrpc":"2.0","result":"7","id":"1"},{"jsonrpc":"2.0","result":"7","id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","result":"7","id":"5"},{"jsonrpc":"2.0","result":"7","id":"9"}]', json_encode($r11));
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function testResponseFromString($json, $class, $requestId, $elements = null)
    {
        $object = Response::fromString($json);

        $this->assertInstanceOf($class, $object->result());
        $this->assertNull($object->originalRequest());
        $id = $object->id() ? $object->id()->id() : null;
        $this->assertSame($requestId, $id);

        $decode = json_decode($json);
        $count = count($decode);

        $this->assertCount($count, $object);

        $encode = json_encode($object);
        $this->assertJsonStringEqualsJsonString($json, $encode);

        $copy = Response::fromString($json);
        $this->assertTrue($object->equals($copy));
        $this->assertJsonStringEqualsJsonString(json_encode($copy), $encode);

        for ($x = 0;$x < 3;++$x) {
            $iterations = 0;
            $objectCount = count($object);

            foreach ($object as $key => $response) {
                ++$iterations;
                if ($elements) {
                    $fromObject = Response::fromString(json_encode($response));
                    $expectedObject =  Response::fromString($elements[$key]);
                    $this->assertTrue($fromObject->equals($expectedObject));
                }
            }

            $iterations = $iterations > 0 ? $iterations : 1;
            $this->assertSame($iterations, $objectCount);
            $this->assertSame($objectCount, count($object));
            $this->assertCount($iterations, $object);
        }
    }

    /**
     * @dataProvider fromStringProvider
     * @expectedException \InvalidArgumentException
     */
    public function testClientCantModifyResults($json)
    {
        $object = Response::fromString($json);
        $success1 = $this->getMockBuilder('\JsonRPC\Response\Success')
            ->setMethods(array('jsonSerialize'))
            ->getMock();

        $object->handleResult($success1);
    }

    public function fromStringProvider()
    {
        return array(
            array('{"jsonrpc":"2.0","result":["hello",5],"id": 9}', '\JsonRPC\Response\Success', 9),
            array('{"jsonrpc":"2.0","error":{"code": -32601, "message": "Method not found"},"id":"10"}', '\JsonRPC\Response\Error', '10'),
            array('{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}', '\JsonRPC\Response\Error', null),
            array('[{"jsonrpc":"2.0","result":7,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]', '\JsonRPC\Response\Success', '1', array('{"jsonrpc":"2.0","result":7,"id":"1"}', '{"jsonrpc":"2.0","result":19,"id":"2"}', '{"jsonrpc":"2.0","result":["hello",5],"id":"9"}')),
            array('[{"jsonrpc":"2.0","result":"7","id": null},{"jsonrpc":"2.0","result":"7","id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","result":"7","id":"5"},{"jsonrpc":"2.0","result":"7","id":"9"}]', '\JsonRPC\Response\Success', null, array('{"jsonrpc":"2.0","result":"7","id": null}', '{"jsonrpc":"2.0","result":"7","id":"2"}', '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}', '{"jsonrpc":"2.0","result":"7","id":"5"}', '{"jsonrpc":"2.0","result":"7","id":"9"}')),
            array('[{"jsonrpc":"2.0","error":{"code": -32601, "message": "Method not found"},"id":10},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]', '\JsonRPC\Response\Error', 10, array('{"jsonrpc":"2.0","error":{"code": -32601, "message": "Method not found"},"id":"10"}', '{"jsonrpc":"2.0","result":["hello",5],"id":"9"}')),
            array('[{"jsonrpc":"2.0","error":{"code": -32601, "message": "Method not found"},"id":"8"}]', '\JsonRPC\Response\Error', '8', array('{"jsonrpc":"2.0","error":{"code": -32601, "message": "Method not found"},"id":"8"}')),
        );
    }

    public function BatchSuccessRequestProvider()
    {
        return array(
            array(
                '[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"},{"jsonrpc": "2.0","method":"get_data","id":"9"}]',
                '[{"jsonrpc":"2.0","result":7,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]',
                '[7,19,["hello", 5]]', ),
        );
    }

    public function BatchErrorRequestProvider()
    {
        return array(
            array(
                '[1,2,3]',
                '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]',
            ),
            array(
                '[1]',
                '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]',
            ),
            array(
                '[]',
                '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            ),
            array(
                '[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method"]',
                '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            ),
            array(
                '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
                '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}',
            ),

        );
    }

    /**
     * @dataProvider SuccessResponse
     */
    public function testCanIterateWhenNoBatch($json)
    {
        $response = new Response($json);
        $this->assertCount(1, $response);

        $iteration = 0;
        foreach ($response as $res) {
            ++$iteration;
        }

        $this->assertSame(1, $iteration);
    }
}
