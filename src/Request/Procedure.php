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

use JsonRPC\Batch\BatchTrait;
use JsonRPC\Batch\RecoveryTrait;
use JsonRPC\Protocol;

abstract class Procedure extends Protocol  implements ProcedureInterface
{
    use BatchTrait;
    use RecoveryTrait;

    /**
     * @var Method
     */
    private $method;

    /**
     * @var Param
     */
    private $params;

    /**
     * @param Method $method
     * @param Param  $params
     */
    public function __construct(Method $method, Param $params = null)
    {
        $this->setMethod($method);
        $this->setParams($params);
    }

    /**
     * A String containing the name of the method to be invoked.
     * Method names that begin with the word rpc followed by a period character
     * (U+002E or ASCII 46) are reserved for rpc-internal
     * methods and extensions and MUST NOT be used for anything else.
     *
     * @return Method
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * A Structured value that holds the parameter values to be used during the invocation of the method. This member MAY be omitted.
     *
     * @return Param
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * @param Method $method
     */
    private function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param Param $params
     */
    private function setParams($params)
    {
        $this->params = $params ?: new Param($params);

        if ($this->params->mode() === (Param::INDEX_POSITION & Param::INDEX_NAME)) {
            //Todo defined exception message
            throw new \InvalidArgumentException('');
        }
    }
}
