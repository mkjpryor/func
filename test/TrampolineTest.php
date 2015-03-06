<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\trampoline;


function factorial($n, $accum = 1) {
    if( $n === 0 ) return $accum;
    return function() use($n, $accum) {
        return factorial($n - 1, $n * $accum);
    };
}


class TrampolineTest extends \PHPUnit_Framework_TestCase {
    public function testTrampolineNoRecursion() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
                 
        $g = trampoline($f);
        $this->assertSame($g(1, 2, 3, 4), 101);
    }
    
    public function testTrampolineRecursion() {
        $thunk = \Mockery::mock(CallableInterface::class)
                     ->shouldReceive('__invoke')
                         ->withNoArgs()->once()->andReturn(101)
                     ->getMock();
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3)->once()->andReturn($thunk)
                 ->getMock();
                 
        $g = trampoline($f);
        $this->assertSame($g(1, 2, 3), 101);
    }
    
    public function testTrampolineFactorial() {
        // This is just a realistic test with a factorial function
        $g = trampoline(__NAMESPACE__ . "\\factorial");
        
        $this->assertSame($g(10), 3628800);
    }
}
