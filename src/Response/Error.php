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

class Error implements ResultInterface
{
    protected $codeId;

    protected $message;

    protected $data;

    public function __construct(CodeId $codeId, $message, $data = null)
    {
        $this->setCodeId($codeId);
        $this->setMessage($message);
        $this->setData($data);
    }

    public function id()
    {
        return $this->codeId;
    }

    public function message()
    {
        return $this->message;
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
        $json = array(
            'code'    => $this->id(),
            'message' => $this->message(),
            'data'    => $this->data(),
        );

        if (is_null($this->data())) {
            unset($json['data']);
        }

        return $json;
    }

    public static function fromString($string)
    {
        $error = json_decode($string);

        $data = empty($error->data) ? null : $error->data;

        return new self(new CodeId($error->code), $error->message, $data);
    }

    public static function fromException(\Exception $exception, $data = null)
    {
        return new self(new CodeId($exception->getCode()), $exception->getMessage(), $data);
    }

    private function setCodeId($codeId)
    {
        $this->codeId = $codeId;
    }

    private function setMessage($message)
    {
        $this->message = $message;
    }

    private function setData($data)
    {
        $this->data = $data;
    }
}
