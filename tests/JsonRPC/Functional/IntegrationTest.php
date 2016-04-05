<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Tests\Functional;

use JsonRPC\Exception\MethodNotFoundException;
use JsonRPC\Request\Method;
use JsonRPC\Request\Notification;
use JsonRPC\Request\Param;
use JsonRPC\Request\Request;
use JsonRPC\Request\RequestId;
use JsonRPC\Response\Error;
use JsonRPC\Response\Response;
use JsonRPC\Response\Success;
use JsonRPC\Tests\TestCase;

/**
 * --> data sent to Server
 * <-- data sent to Client.
 */
class IntegrationTest extends TestCase
{
    //-->{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}
    //<--{"jsonrpc": "2.0", "result": 19, "id": 1}
    public function testRpcCallWithPositionalParameters()
    {

        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';

        $request = new Request(
            new Method('subtract'),
            new Param(array(42, 23)),
            new RequestId(1)
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\RequestInterface', $request);
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('subtract', $request->method()->name());
        $this->assertSame(array(42, 23), $request->params()->params());
        $this->assertSame(1, $request->id()->id());

        //... call_user_func_array($request->method()->name(), $request->params()->params());
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "result": 19, "id": 1}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Success', $response->result());
        $this->assertSame(19, $response->result()->data());
        $this->assertSame(1, $response->id()->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    //-->{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}
    //<--{"jsonrpc": "2.0", "result": -19, "id": 2}
    public function testRpcCallWithPositionalParameters2()
    {

        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}';

        $request = new Request(
            new Method('subtract'),
            new Param(array(23, 42)),
            new RequestId(2)
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\RequestInterface', $request);
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('subtract', $request->method()->name());
        $this->assertSame(array(23, 42), $request->params()->params());
        $this->assertSame(2, $request->id()->id());

        //... call_user_func_array($request->method()->name(), $request->params()->params());
        $result = new Success(-19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "result": -19, "id": 2}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Success', $response->result());
        $this->assertSame(-19, $response->result()->data());
        $this->assertSame(2, $response->id()->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     * --> {"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}
     * <-- {"jsonrpc": "2.0", "result": 19, "id": 3}.
     */
    public function testRpcCallWithNamedParameters1()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}';

        $request = new Request(
            new Method('subtract'),
            new Param(array('subtrahend' => 23, 'minuend' => 42)),
            new RequestId(3)
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\RequestInterface', $request);
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('subtract', $request->method()->name());
        $this->assertSame(array('subtrahend' => 23, 'minuend' => 42), $request->params()->params());
        $this->assertSame(3, $request->id()->id());

        //... call_user_func_array($request->method()->name(), $request->params()->params());
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "result": 19, "id": 3}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Success', $response->result());
        $this->assertSame(19, $response->result()->data());
        $this->assertSame(3, $response->id()->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     * --> {"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}
     * <-- {"jsonrpc": "2.0", "result": 19, "id": 4}.
     */
    public function testRpcCallWithNamedParameters2()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}';

        $request = new Request(
            new Method('subtract'),
            new Param(array('minuend' => 42, 'subtrahend' => 23)),
            new RequestId(4)
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\RequestInterface', $request);
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('subtract', $request->method()->name());
        $this->assertSame(array('minuend' => 42, 'subtrahend' => 23), $request->params()->params());
        $this->assertSame(4, $request->id()->id());

        //... call_user_func_array($request->method()->name(), $request->params()->params());
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "result": 19, "id": 4}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Success', $response->result());
        $this->assertSame(19, $response->result()->data());
        $this->assertSame(4, $response->id()->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     * --> {"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}.
     */
    public function testNotification1()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}';

        $request = new Notification(
            new Method('update'),
            new Param(array(1, 2, 3, 4, 5))
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('update', $request->method()->name());
        $this->assertSame(array(1, 2, 3, 4, 5), $request->params()->params());
        $this->assertSame(null, $response->id());

        //no result will be applied to the response , Notification don't have response
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER
    }

    /**
     * --> {"jsonrpc": "2.0", "method": "foobar"}.
     */
    public function testNotification2()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "foobar"}';

        $request = new Notification(
            new Method('foobar')
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('foobar', $request->method()->name());
        $this->assertSame(null, $request->params()->params());
        $this->assertSame(null, $response->id());

        //the $result will not be applied to the $response, Notification don't have
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER
    }

    /**
     * --> {"jsonrpc": "2.0", "method": "foobar", "id": "1"}
     *  <-- {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}.
     */
    public function testRpcCallOfNonExistentMethod()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}';

        $request = new Request(
            new Method('foobar'),
            null,
            new RequestId('1')
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertInstanceOf('\JsonRPC\Request\RequestInterface', $request);
        $this->assertInstanceOf('\JsonRPC\Request\ProcedureInterface', $request);
        $this->assertSame('foobar', $request->method()->name());
        $this->assertSame(null, $request->params()->params());
        $this->assertSame('1', $request->id()->id());

        //... call_user_func_array($request->method()->name(), $request->params()->params());
        try {
            throw new MethodNotFoundException();
        } catch (MethodNotFoundException $e) {
            $response = $response->makeException($e);
        }

        $expected_response = '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32601, $response->result()->id()->id());
        $this->assertSame('Method not found', $response->result()->message());
        $this->assertSame('1', $response->id()->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     *  --> {"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]
     *  <-- {"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}.
     */
    public function testRpcCallWithInvalidJSON()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]';

        //Can't assert invalid json

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());

        //the $result will not be applied to the $response, Error is persistent
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32700, $response->result()->id()->id());
        $this->assertSame('Parse error', $response->result()->message());
        $this->assertNull($response->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     *  --> {"jsonrpc": "2.0", "method": 1, "params": "bar"}
     *  <-- {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}.
     */
    public function testRpcCallWithInvalidRequestObject()
    {
        //START CLIENT
        $expected = '{"jsonrpc": "2.0", "method": 1, "params": "bar"}';

        //Can't assert invalid json

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());

        //the $result will not be applied to the $response, Error is persistent
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32600, $response->result()->id()->id());
        $this->assertSame('Invalid Request', $response->result()->message());
        $this->assertNull($response->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     * --> [
     *      {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
     *      {"jsonrpc": "2.0", "method"
     *    ]
     *    <-- {"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}.
     */
    public function testRpcCallBatchInvalidJSON()
    {
        //START CLIENT
        $expected = '[
                        {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                        {"jsonrpc": "2.0", "method"
                     ]';

        //Can't assert invalid json

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());

        //the $result will not be applied to the $response, Error is persistent
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32700, $response->result()->id()->id());
        $this->assertSame('Parse error', $response->result()->message());
        $this->assertNull($response->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     *  --> []
     *  <-- {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}.
     */
    public function testRpcCallWithAnEmptyArray()
    {
        //START CLIENT
        $expected = '[]';

        //Can't build invalid request

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);
        //Single request mode                        -

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());

        //the $result will not be applied to the $response, Error is persistent
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32600, $response->result()->id()->id());
        $this->assertSame('Invalid Request', $response->result()->message());
        $this->assertNull($response->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     *  --> [1]
     *  <-- [
     *          {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
     *      ].
     */
    public function testRpcCallWithAnInvalidBatchButNotEmpty()
    {
        //START CLIENT
        $expected = '[1]';

        //Can't build invalid request

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(1, $response);

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());

        //the $result will not be applied to the $response, Error is persistent
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '[
                                {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
                              ]';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32600, $response->result()->id()->id());
        $this->assertSame('Invalid Request', $response->result()->message());
        $this->assertNull($response->id());
        $this->assertNull($response->originalRequest());
        //END CLIENT
    }

    /**
     *  --> [1,2,3]
     *  <-- [
     *          {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
     *          {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
     *          {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
     *      ].
     */
    public function testRpcCallWithAnInvalidBatch()
    {
        //START CLIENT
        $expected = '[1,2,3]';

        //Can't build invalid request

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(3, $response);

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());

        //the $result will not be applied to the $response, Error is persistent
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '[
                                  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
                                  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
                                  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
                              ]';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertCount(3, $response);
        $this->assertInstanceOf('\JsonRPC\Response\Error', $response->result());
        $this->assertSame(-32600, $response->result()->id()->id());
        $this->assertSame('Invalid Request', $response->result()->message());
        $this->assertNull($response->id());
        $this->assertNull($response->originalRequest());

        foreach ($response as $res) {
            $this->assertCount(1, $res);
            $this->assertInstanceOf('\JsonRPC\Response\Error', $res->result());
            $this->assertSame(-32600, $res->result()->id()->id());
            $this->assertSame('Invalid Request', $res->result()->message());
            $this->assertNull($res->id());
            $this->assertNull($res->originalRequest());
        }
        //END CLIENT
    }

    /**
     *  --> [
     *    {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
     *    {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
     *    {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
     *    {"foo": "boo"},
     *    {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
     *    {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
     *    ]
     *    <-- [
     *    {"jsonrpc": "2.0", "result": 7, "id": "1"},
     *    {"jsonrpc": "2.0", "result": 19, "id": "2"},
     *    {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
     *    {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},
     *    {"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}
     *    ].
     */
    public function testRpcCallBatch()
    {
        //START CLIENT
        $expected = '[
                        {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                        {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
                        {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
                        {"foo": "boo"},
                        {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
                        {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
                     ]';

        $request = array(
            new Request(
                new Method('sum'),
                new Param(array(1, 2, 4)),
                new RequestId('1')
            ),
            new Notification(
                new Method('notify_hello'),
                new Param(array(7))
            ),
            new Request(
                new Method('subtract'),
                new Param(array(42, 23)),
                new RequestId('2')
            ),
            array('foo' => 'boo'),
            new Request(
                new Method('foo.get'),
                new Param(array('name' => 'myself')),
                new RequestId('5')
            ),
            new Request(
                new Method('get_data'),
                null,
                new RequestId('9')
            ),

        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(6, $response);

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());

        $result1 = new Success(7);
        $notification = new Success();
        $result2 = new Success(19);

        $error1 = new Success('cant override previous exception');
        $error2 = Error::fromException(new MethodNotFoundException());
        $result3 = new Success(array('hello', 5));

        $results = array($result1, $notification, $result2, $error1, $error2, $result3);

        foreach ($results as $result) {
            $response = $response->handleResult($result);
        }

        $expected_response = '[
                                {"jsonrpc": "2.0", "result": 7, "id": "1"},
                                {"jsonrpc": "2.0", "result": 19, "id": "2"},
                                {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
                                {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},
                                {"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}
                              ]';
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER

        //START CLIENT
        $response = Response::fromString($expected_response);

        $this->assertInstanceOf('\JsonRPC\Response\Success', $response->result());
        $this->assertSame(7, $response->result()->data());
        $this->assertSame('1', $response->id()->id());
        $this->assertNull($response->originalRequest());

        $this->assertCount(5, $response);

        //END CLIENT
    }

    /**
     *  --> [
     *      {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
     *      {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
     *      ]
     *  <-- //Nothing is returned for all notification batches.
     */
    public function testRpcCallBatchAllNotifications()
    {
        //START CLIENT
        $expected = '[
                        {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
                        {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
                    ]';

        $request = array(
            new Notification(
                new Method('notify_sum'),
                new Param(array(1, 2, 4))
            ),
            new Notification(
                new Method('notify_hello'),
                new Param(array(7))
            ),
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($request));

        //END CLIENT

        //START SERVER
        $response = new Response($expected);

        $this->assertCount(2, $response);

        $request = $response->request();
        $this->assertSame(null, $request);
        $this->assertSame($expected, $response->originalRequest());

        //the $result will not be applied to the $response,
        $result = new Success(19);
        $response = $response->handleResult($result);

        $expected_response = '[]'; //Todo fix null response
        $this->assertJsonStringEqualsJsonString($expected_response, json_encode($response));

        //END SERVER
    }
}
