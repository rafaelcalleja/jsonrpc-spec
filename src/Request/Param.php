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

/**
 * If present, parameters for the rpc call MUST be provided as a Structured value. Either by-position through an Array or by-name through an Object.
 * by-position: params MUST be an Array, containing the values in the Server expected order.
 * by-name: params MUST be an Object, with member names that match the Server expected parameter names.
 * The absence of expected names MAY result in an error being generated. The names MUST match exactly, including case, to the method's expected parameters.
 * CASE SENSITIVE.
 */
class Param implements \JsonSerializable, \Countable
{
    const INDEX_POSITION = 1;
    const INDEX_NAME = 2;

    protected $params = null;

    protected $mode = null;

    public function __construct(array $parameters = null)
    {
        $this->setParams($parameters);
    }

    public function mode()
    {
        return $this->mode;
    }

    public function params()
    {
        return $this->params;
    }

    public function isIndexByName()
    {
        return self::INDEX_NAME === (self::INDEX_NAME & $this->mode());
    }

    public function isIndexByPosition()
    {
        return self::INDEX_POSITION === (self::INDEX_POSITION & $this->mode());
    }

    public function isIndexByBoth()
    {
        return (self::INDEX_NAME & self::INDEX_POSITION) === (self::INDEX_NAME & self::INDEX_POSITION & $this->mode());
    }

    public static function fromString($string)
    {
        return new self(json_decode($string, true));
    }

    public function equals(Param $param)
    {
        return $param->params() === $this->params();
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
        return $this->params() ?: new \StdClass();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->params());
    }

    private function setParams($parameters)
    {
        if (!is_null($parameters)) {
            foreach ($parameters as $key => $value) {
                $mode = is_int($key) ? self::INDEX_POSITION : self::INDEX_NAME;
                $this->setMode($mode);

                $this->params[$key] = $value;
            }
        }
    }

    private function setMode($mode)
    {
        $this->mode = is_null($this->mode) ? $mode : ($this->mode & $mode);
    }
}
