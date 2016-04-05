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

class Method implements \JsonSerializable
{
    const SYSTEM_EXTENSION = 1;

    protected $name;

    protected $flag;

    public function __construct($name)
    {
        $this->setName($name);
    }

    public function equals(Method $method)
    {
        return $method->name() == $this->name();
    }

    public function name()
    {
        return $this->name;
    }

    public function isSystemExtension()
    {
        return $this->flag === ($this->flag & self::SYSTEM_EXTENSION);
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
        return $this->name();
    }

    private function setName($name)
    {
        if (strpos(json_decode($name), 'rpc.') === 0 ||
            strpos(utf8_decode($name), 'rpc.') === 0 ||
            strpos($name, 'rpc.') === 0) {
            $this->flag = self::SYSTEM_EXTENSION;
        }

        $this->name = $name;
    }
}
