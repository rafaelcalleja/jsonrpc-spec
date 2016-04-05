<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Batch;

use JsonRPC\Exception\InvalidRequestException;
use JsonRPC\Exception\ParseErrorException;

trait RecoveryTrait
{
    public static function fromString($string)
    {
        $data = json_decode($string);

        if (json_last_error() == \JSON_ERROR_SYNTAX) {
            throw new ParseErrorException();
        }

        if (empty($data) || is_scalar($data)) {
            throw new InvalidRequestException();
        }

        return
            is_array($data) ?

                array_reduce($data,  \Closure::bind(function ($batch, $object) {
                    $request = static::fromObject($object);

                    if (empty($batch)) {
                        $batch = $request;
                    }
                    array_push($batch->elements, $request);

                    return $batch;
                }, null, get_called_class())) :

                static::fromObject($data);
    }

    abstract protected static function fromObject($data);
}
