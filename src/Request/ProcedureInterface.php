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

use JsonRPC\Batch\BatchInterface;
use JsonRPC\Batch\RecoveryInterface;

interface ProcedureInterface extends \JsonSerializable, BatchInterface, RecoveryInterface
{
    /**
     * A String containing the name of the method to be invoked.
     * Method names that begin with the word rpc followed by a period character
     * (U+002E or ASCII 46) are reserved for rpc-internal
     * methods and extensions and MUST NOT be used for anything else.
     *
     * @return string
     */
    public function method();

    /**
     * A Structured value that holds the parameter values to be used during the invocation of the method. This member MAY be omitted.
     *
     * @return array
     */
    public function params();

    public function equals($procedure);
}
