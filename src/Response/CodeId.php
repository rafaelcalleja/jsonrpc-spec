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

class CodeId implements \JsonSerializable
{
    /**
     * @var number
     */
    protected $codeId;

    public function __construct($codeId)
    {
        $this->setCodeId($codeId);
    }

    public function id()
    {
        return $this->codeId;
    }

    public function equals(CodeId $codeId)
    {
        return $codeId->id() == $this->id();
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

    private function setCodeId($codeId)
    {
        //Todo set exception messages
        if (!is_numeric($codeId)) {
            throw new \InvalidArgumentException('');
        }

        $this->codeId = $codeId;
    }
}
