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

class InternalErrorException extends \Exception
{
    public function __construct($message = 'Internal error', $code = -32603, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
