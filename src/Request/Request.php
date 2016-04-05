<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Request;

use JsonRPC\Exception\InvalidRequestException;

class Request extends Notification implements RequestInterface
{
    /**
     * @var RequestId
     */
    protected $id;

    /**
     * @param Method    $method
     * @param Param     $params
     * @param RequestId $id
     */
    public function __construct(Method $method, Param $params = null, RequestId $id)
    {
        parent::__construct($method, $params);
        $this->setId($id);
    }

    /**
     * @return RequestId
     */
    public function id()
    {
        return $this->id;
    }

    public function equals($request)
    {
        if (!$request instanceof self && count($request) > 0) {
            foreach ($request as $offset => $req) {
                if (!$req->params()->equals($this->offsetGet($offset)->params()) ||
                    !$req->method()->equals($this->offsetGet($offset)->method()) ||
                    !$req->id()->equals($this->offsetGet($offset)->id())
                ) {
                    return false;
                }
            }

            return true;
        }

        return $request->params()->equals($this->params()) &&
            $request->method()->equals($this->method()) &&
            $request->id()->equals($this->id())
            ;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            array('id' => $this->id())
        );
    }

    protected static function fromObject($stdClass)
    {
        $stdClass->params = empty($stdClass->params) ? null : json_decode(json_encode($stdClass->params), true);

        if (empty($stdClass->method) || (!is_null($stdClass->params) && !is_array($stdClass->params))) {
            throw new InvalidRequestException();
        }

        return new self(new Method($stdClass->method), new Param($stdClass->params), new RequestId($stdClass->id));
    }

    /**
     * @param RequestId $id
     */
    private function setId($id)
    {
        $this->id = $id;
    }
}
