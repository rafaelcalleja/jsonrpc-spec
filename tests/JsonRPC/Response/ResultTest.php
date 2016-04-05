<?php

/*
 * This file is part of the jsonrpc spec package.
 *
 * (c) Rafael Calleja <rafaelcalleja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonRPC\Tests\Response;

use JsonRPC\Response\Success;
use JsonRPC\Tests\TestCase;

class ResultTest extends TestCase
{
    /** factory result ? */
    public function testJsonWithResultBuildObjectSuccess()
    {
    }
    public function testJsonWithErrorBuildObjectError()
    {
    }
    /**
     * This member is REQUIRED on success.
     * This member MUST NOT exist if there was an error invoking the method.
     * The value of this member is determined by the method invoked on the Server.
     **/
    public function testResultCanBeEncoded()
    {
        $result = new Success();
        $this->assertInstanceOf('\JsonRPC\Response\ResultInterface', $result);
    }

    public function SuccessResponse()
    {
        array(
               /* rpc call with positional parameters*/
               array('{"jsonrpc":"2.0","result":19,"id":1}'),
               array('{"jsonrpc":"2.0","result":-19,"id":2}'),
               /* rpc call with named parameters */
               array('{"jsonrpc":"2.0","result":19,"id":3}'),
               array('{"jsonrpc":"2.0","result":19,"id":4}'),

           );
    }
}
