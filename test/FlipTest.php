<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\flip;


class FlipTest extends \PHPUnit_Framework_TestCase {
    public function testFlipNoArgs() {
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->withNoArgs()->once()->andReturn(101)
                 ->getMock();
                 
        $g = flip($f);

        $this->assertSame($g(), 101);
    }
    
    public function testFlipOneArg() {
        $f = \Mockery::mock(CallableInterface::class)
                ->shouldReceive('__invoke')
                    ->with(1)->once()->andReturn(101)
                ->getMock();
                
        $g = flip($f);
        
        $this->assertSame($g(1), 101);
    }
    
    public function testFlipTwoArgs() {
        $f = \Mockery::mock(CallableInterface::class)
                ->shouldReceive('__invoke')
                    ->with(1, 2)->once()->andReturn(101)
                ->getMock();

        $g = flip($f);

        $this->assertSame($g(2, 1), 101);
    }
    
    public function testFlipThreeArgs() {
        $f = \Mockery::mock(CallableInterface::class)
                ->shouldReceive('__invoke')
                    ->with(1, 2, 3)->once()->andReturn(101)
                ->getMock();

        $g = flip($f);

        $this->assertSame($g(3, 2, 1), 101);
    }
}
