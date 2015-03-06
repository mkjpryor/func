<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\memoize;


class MemoizeTest extends \PHPUnit_Framework_TestCase {
    public function testMemoize() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1)->once()->andReturn(101)
                 ->shouldReceive('__invoke')
                     ->with(1, 2)->once()->andReturn(201)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3)->once()->andReturn(301)
                 ->getMock();
        
        $g = memoize($f);
        
        // Call $g twice with each combination of args - $f should only get called once
        $this->assertSame($g(1), 101);
        $this->assertSame($g(1), 101);
        $this->assertSame($g(1, 2), 201);
        $this->assertSame($g(1, 2), 201);
        $this->assertSame($g(1, 2, 3), 301);
        $this->assertSame($g(1, 2, 3), 301);
    }
}
