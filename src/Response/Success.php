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

class Success implements ResultInterface
{
    private $data;

    public function __construct($data = null)
    {
        $this->setData($data);
    }

    public function data()
    {
        return $this->data;
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
        return $this->data();
    }

    public static function fromString($string)
    {
        return new self($string);
    }

    private function setData($data)
    {
        $this->data = $data;
    }
}
