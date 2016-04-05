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

class RequestFactory
{
    public function create($string)
    {
        $decode = json_decode($string, true);

        return empty($decode['id']) ?
           Notification::fromString($string) :
           Request::fromString($string);
    }
}
