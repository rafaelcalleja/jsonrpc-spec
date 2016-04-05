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
 * Invalid JSON was received by the server.
 * An error occurred on the server while parsing the JSON text.
 */
class ParseErrorException extends \RuntimeException implements ClientExceptionInterface
{
    public function __construct($message = 'Parse error', $code = -32700, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
