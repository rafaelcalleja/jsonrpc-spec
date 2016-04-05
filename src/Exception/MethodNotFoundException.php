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

class MethodNotFoundException extends \BadMethodCallException
{
    public function __construct($message = 'Method not found', $code = -32601, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
