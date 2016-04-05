<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Exception;

/**
 * The JSON sent is not a valid Request object.
 */
class InvalidRequestException extends \RuntimeException implements ClientExceptionInterface
{
    public function __construct($message = 'Invalid Request', $code = -32600, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
