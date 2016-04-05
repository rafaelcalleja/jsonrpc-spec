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

class ResultCollection implements ResultInterface
{
    /**
     * @var []
     */
    private $elements = array();

    public function push(ResultInterface $object)
    {
        $this->elements[] = $object;
    }

    public function pop()
    {
        if (!$this->elements) {
            return;
        }

        return array_shift($this->elements);
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
        return $this->elements;
    }
}
