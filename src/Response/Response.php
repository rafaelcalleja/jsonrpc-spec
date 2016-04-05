<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Response;

use JsonRPC\Batch\BatchInterface;
use JsonRPC\Batch\BatchTrait;
use JsonRPC\Batch\RecoveryInterface;
use JsonRPC\Batch\RecoveryTrait;
use JsonRPC\Exception\ClientExceptionInterface;
use JsonRPC\Request\RequestId;
use JsonRPC\Request\RequestInterface;

class Response extends \JsonRPC\Protocol implements \JsonSerializable, BatchInterface, RecoveryInterface
{
    use BatchTrait;
    use RecoveryTrait;

    /**
     * @var \JsonRPC\Request\RequestInterface|\JsonRPC\Request\ProcedureInterface|null
     */
    protected $request;

    /**
     * @var \JsonRPC\Request\RequestId
     */
    protected $requestId;

    /**
     * @var ResultInterface
     */
    protected $result;

    /**
     * @var ClientExceptionInterface|null
     */
    protected $selfException;

    /**
     * @var string
     */
    protected $rawrequest;

    /**
     * @param string $request
     */
    public function __construct($request)
    {
        $this->setOriginalRequest($request);
        $this->setRequest($request);
    }

    /**
     * @return \JsonRPC\Request\RequestInterface|\JsonRPC\Request\ProcedureInterface|null
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * @return $string
     */
    public function originalRequest()
    {
        return $this->rawrequest;
    }

    /**
     * @return \JsonRPC\Request\RequestId
     */
    public function id()
    {
        return $this->requestId;
    }

    /**
     * @return ResultInterface
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * @param \Exception $exception
     * @param null       $data
     *
     * @return Response
     */
    public function makeException($exception, $data = null)
    {
        $object = new self($this->rawrequest);
        $object->setResult(Error::fromException($exception, $data));

        return $object;
    }

    /**
     * @param ResultInterface $result
     *
     * @return Response
     */
    public function handleResult(ResultInterface $result)
    {
        if (is_null($this->rawrequest)) {
            throw new \InvalidArgumentException('Invalid request');
        }

        $object = new self($this->rawrequest);
        $object->elements = $this->elements;

        //Todo validate elements size
        if ($result instanceof ResultCollection && count($this->elements) > 0) {
            foreach ($this->elements as $index => $response) {
                $this->resolve($object->elements, $index, $result->pop());
            }

            return $object;
        }

        if ($result instanceof ResultCollection && count($this->elements) == 0) {
            $result = $result->pop();
        }

        if ($result instanceof ResultInterface && count($this->elements) > 0) {
            $response = current($this->elements);
            $hasNext = next($this->elements);
            $object = $this->resolveResponse($result, $response);
            $hasNext ? prev($this->elements) : end($this->elements);
        }

        if (!$this->selfException instanceof ClientExceptionInterface || $result instanceof Error) {
            $object->setResult($result);
        }

        return $object;
    }

    public function resolveResponse(ResultInterface $result, Response $response)
    {
        $object = new self($this->rawrequest);
        $object->elements = $this->elements;

        if (is_null($this->rawrequest)) {
            throw new \InvalidArgumentException('Invalid request');
        }

        if (count($this->elements) == 0 && $this->rawrequest === $response->rawrequest) {
            $object = $object->handleResult($result);

            return $object;
        }

        if (($index = $this->indexOf($response)) === false) {
            throw new \InvalidArgumentException('Response not found');
        }

        $this->resolve($object->elements, $index, $result);

        return $object;
    }

    private function resolve(&$elements, $index, ResultInterface $result)
    {
        $elements[$index] = $elements[$index]->handleResult($result);
    }

    /**
     * @param Response $response
     *
     * @return bool
     */
    public function equals(Response $response)
    {
        return
            $this->request() == $response->request() &&
            $this->result() == $response->result() &&
            $this->originalRequest() == $response->originalRequest() &&
            $this->compare($response->elements)
            ;
    }

    protected static function fromObject($stdClass)
    {
        $object = new self(null);

        if (!empty($stdClass->result)) {
            $class = '\JsonRPC\Response\Success';
            $code = $stdClass->result;
        } else {
            $class = '\JsonRPC\Response\Error';
            $code = json_encode($stdClass->error);
        }

        $object->result = call_user_func(array($class, 'fromString'), $code);

        if ($stdClass->id) {
            $object->requestId = new RequestId($stdClass->id);
        }

        return $object;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return ($this->elements) ?
            array_values(array_filter(array_map(array($this, 'jsonResponse'), $this->elements))) :
            $this->jsonResponse($this);
    }

    private function jsonResponse(Response $response)
    {
        if ($response->request() instanceof \JsonRPC\Request\ProcedureInterface &&  !$response->request() instanceof \JsonRPC\Request\RequestInterface) {
            return '';
        }

        $field = $response->result() instanceof Success ? 'result' : 'error';

        $json = array(
            'jsonrpc' => self::JSONRPC_VERSION,
            $field    => $response->result(),
            'id'      => $response->id(),
        );

        return $json;
    }

    /**
     * @param ResultInterface $result
     */
    private function setResult(ResultInterface $result)
    {
        $this->result = $result;
    }

    /**
     * @param \JsonRPC\Request\Request $request
     */
    private function setRequestId(\JsonRPC\Request\Request $request)
    {
        $this->requestId = $request->id();
    }

    private function setOriginalRequest($string)
    {
        $this->rawrequest = $string;
    }
    /**
     * @param $string
     */
    private function setRequest($string)
    {
        try {
            $decode = json_decode($string);

            if (is_array($decode) && !empty($decode)) {
                $this->elements = array_map(array($this, 'fromSingleObject'), $decode);
                $ele = current($this->elements);

                if (($id = $ele->request()) instanceof RequestInterface) {
                    $this->setRequestId($id);
                }

                if (($result = $ele->result()) instanceof ResultInterface) {
                    $this->setResult($result);
                }
            } else {
                if (empty($decode->id)) {
                    $this->request = \JsonRPC\Request\Notification::fromString($string);
                } else {
                    $this->request = \JsonRPC\Request\Request::fromString($string);
                    $this->setRequestId($this->request());
                }
            }
        } catch (ClientExceptionInterface $e) {
            $this->selfException = $e;
            $this->setResult(Error::fromException($e));
        }
    }

    private function fromSingleObject($object)
    {
        $string = json_encode($object);

        return new self($string);
    }

    private function indexOf($element)
    {
        foreach ($this->elements as $key => $e) {
            if ($e->rawrequest == $element->rawrequest) {
                return $key;
            }
        }

        return false;
    }

    private function compare($collection)
    {
        $clone = $this->elements;

        return array_reduce($collection, function ($equality, $element) use ($clone) {

            if ($equality === false) {
                return false;
            }

            foreach ($clone as $key => $e) {
                if ($e->request() == $element->request() &&
                    $e->result() == $element->result() &&
                    $e->originalRequest() == $element->originalRequest()) {
                    unset($clone[$key]);

                    return true;
                }
            }

            return false;

        }, count($clone) == count($collection));
    }

    public function getIterator()
    {
        if (isset($this->elements[0]) && count($this->elements[0]->elements) > 0) {
            $clone = array();

            foreach ($this->elements[0]->elements as $element) {
                $temp = clone $element;
                $temp->elements = array();
                array_push($clone, $temp);
            }

            return new \ArrayIterator($clone);
        }

        return count($this->elements) == 0 ?  new \ArrayIterator(array($this)) : new \ArrayIterator($this->elements);
    }
}
