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

use Ramsey\Uuid\Uuid;

class RequestId implements \JsonSerializable
{
    /**
     * @var string | Uuid
     */
    protected $requestId;

    public function __construct($requestId = null)
    {
        $this->setRequestId($requestId);
    }

    public function id()
    {
        return $this->requestId;
    }

    public function equals(RequestId $requestId)
    {
        return $requestId->id() === $this->id();
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
        return $this->id();
    }

    private function setRequestId($requestId)
    {
        if ($requestId === null) {
            $requestId = Uuid::uuid4()->toString();
        }

        //Todo set exception messages
        if (is_bool($requestId) || is_float($requestId) || is_object($requestId) || is_callable($requestId) || (is_numeric($requestId) && (int) $requestId < 1)) {
            throw new \InvalidArgumentException('');
        }

        $this->requestId = $requestId;
    }
}
