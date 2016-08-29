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

use JsonRPC\Batch\BatchTrait;
use JsonRPC\Batch\RecoveryTrait;
use JsonRPC\Exception\InvalidRequestException;

class Notification extends Procedure
{
    use BatchTrait;
    use RecoveryTrait;

    public function equals($notification)
    {
        if (!$notification instanceof self && count($notification) > 0) {
            foreach ($notification as $offset => $not) {
                if (!$not->params()->equals($this->offsetGet($offset)->params()) ||
                    !$not->method()->equals($this->offsetGet($offset)->method())
                ) {
                    return false;
                }
            }

            return true;
        }

        return
            $notification->params()->equals($this->params()) &&
            $notification->method()->equals($this->method())
            ;
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
        $json = array(
            'jsonrpc' => self::JSONRPC_VERSION,
            'method'  => $this->method(),
            'params'  => $this->params(),
        );

        if ($this->params()->count() <= 0) {
            unset($json['params']);
        }

        return $json;
    }

    public static function fromObject($stdClass)
    {
        $stdClass->params = empty($stdClass->params) ? null : json_decode(json_encode($stdClass->params), true);

        if (empty($stdClass->method) || (!is_null($stdClass->params) && !is_array($stdClass->params))) {
            throw new InvalidRequestException();
        }

        return new static(new Method($stdClass->method), new Param($stdClass->params));
    }
}
