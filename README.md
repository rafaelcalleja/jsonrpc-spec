# PHP JSON-RPC 2.0 Specification

[![Build Status](https://travis-ci.org/rafaelcalleja/jsonrpc-spec.svg?branch=master)](https://travis-ci.org/rafaelcalleja/jsonrpc-spec)

## Request Object

```php
use JsonRPC\Request\Method;
use JsonRPC\Request\Param;
use JsonRPC\Request\Request;
use JsonRPC\Request\RequestId;

$request = new Request(
      new Method('subtract'),
      new Param(array(42, 23)),
      new RequestId(1)
  );
  
echo json_encode($request);

{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}
```

## Notification Object

```php
use JsonRPC\Request\Method;
use JsonRPC\Request\Param;
use JsonRPC\Request\Notification;

$request = new Notification(
        new Method('update'),
        new Param(array(1, 2, 3, 4, 5))
    );
  
echo json_encode($request);

{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}
```

## Batch Request 

```php
use JsonRPC\Request\Method;
use JsonRPC\Request\Param;
use JsonRPC\Request\Notification;
use JsonRPC\Request\Request;
use JsonRPC\Request\RequestId;

$request = array(
            new Request(
                new Method('sum'),
                new Param(array(1, 2, 4)),
                new RequestId('1')
            ),
            new Notification(
                new Method('notify_hello'),
                new Param(array(7))
            )
      );
  
echo json_encode($request);

[
  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
  {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
]
```

## Response Object

```php
use JsonRPC\Response\Response;
use JsonRPC\Response\Success;

$response = new Response('{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}');
$request = $response->request();

//... call_user_func_array($request->method()->name(), $request->params()->params());

$result = new Success(-19);
$response = $response->handleResult($result);
  
echo json_encode($response);

{"jsonrpc": "2.0", "result": -19, "id": 2}
```

## Batch Response

```php
use JsonRPC\Exception\MethodNotFoundException;
use JsonRPC\Response\Error;
use JsonRPC\Response\Response;
use JsonRPC\Response\Success;
use JsonRPC\Response\CodeId


$object = new Response('[
                                {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                                {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"}
                          ]');

foreach ($object as $response) {

  $request = $response->request();
  
  try
  {
      //... call_user_func_array($request->method()->name(), $request->params()->params());
      $result = new Success( array(1, 2, 4) );
      
  }catch(MethodNotFoundException $e){
      $result = new Error(
                  new CodeId($e->getCode())
                  $e->getMessage()
        );
  }
  
  $object = $object->resolveResponse($result, $response);
}
                          
echo json_encode($object);

[
  {"jsonrpc": "2.0", "result": 7, "id": "1"},
  {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "2"},
]
```
