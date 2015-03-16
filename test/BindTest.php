<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\bind;
use function Mkjp\FunctionUtils\_;


class BindTest extends \PHPUnit_Framework_TestCase {
    public function testNoBoundArguments() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = bind($f);
        
        $this->assertSame($g(1, 2, 3, 4), 101);
    }
    
    public function testNoPlaceholder() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = bind($f, 1, 2);

        $this->assertSame($g(3, 4), 101);
    }
    
    public function testWithPlaceholder() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = bind($f, 1, _() , 3);
        
        $this->assertSame($g(2, 4), 101);
    }

    public function testBoundFunctionIsReusable() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->times(2)->andReturn(101)
                 ->getMock();
        
        $g = bind($f, 1, _(), 3);
        
        $this->assertSame($g(2, 4), 101);
        $this->assertSame($g(2, 4), 101);
    }
}
