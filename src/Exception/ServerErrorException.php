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

class ServerErrorException extends \Exception
{
    public function __construct($message = 'Server error', $code = -32000, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
