<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\auto_bind;
use function Mkjp\FunctionUtils\_;


class AutoBindTest extends \PHPUnit_Framework_TestCase {
    public function testSingleCallAllArguments() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = auto_bind($f, 4);
        
        $this->assertSame($g(1, 2, 3, 4), 101);
    }
    
    public function testTwoCallsNoPlaceholder() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = auto_bind($f, 4);

        $h = $g(1, 2);
        $this->assertSame($h(3, 4), 101);
    }
    
    public function testTwoCallsWithPlaceholder() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = auto_bind($f, 4);
        
        $h = $g(1, _(), 3);
        $this->assertSame($h(2, 4), 101);
    }
    
    public function testThreeCallsNoPlaceholder() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = auto_bind($f, 4);
        
        $h = $g(1, 2);
        $h = $h(3);
        $this->assertSame($h(4), 101);
    }
    
    public function testThreeCallsWithPlaceholder() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
        
        $g = auto_bind($f, 4);
        
        $h = $g(1, 2);
        $h = $h(_(), 4);
        $this->assertSame($h(3), 101);
    }
    
    public function testBoundFunctionIsReusable() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->times(3)->andReturn(101)
                 ->getMock();
        
        $g = auto_bind($f, 4);
        
        // Run in 3 different ways against the same auto-bound function
        $this->assertSame($g(1, 2, 3, 4), 101);
        
        // We re-use this partially-bound function
        $h = $g(1, _(), 3);
        
        $this->assertSame($h(2, 4), 101);
        
        $h = $h(_(), 4);
        $this->assertSame($h(2), 101);
    }
}
