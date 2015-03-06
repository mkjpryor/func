<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\compose;


class ComposeTest extends \PHPUnit_Framework_TestCase {
    public function testComposeNoFunctionsIsId() {
        $f = compose();
        $this->assertSame($f(10), 10);
    }
    
    public function testComposeOneFunctionIsFunction() {
        // Build a function that expects the correct arguments and returns a known value
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2)->once()->andReturn(101)
                 ->getMock();
        
        $g = compose($f);
        
        $this->assertSame($g(1, 2), 101);
    }
    
    public function testComposeMultipleFunctions() {
        // Build the chain of functions that expect the correct arguments
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2)->once()->andReturn(101)
                 ->getMock();
        $g = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(101)->once()->andReturn(201)
                 ->getMock();
        $h = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(201)->once()->andReturn(301)
                 ->getMock();
        
        $c = compose($f, $g, $h);
        
        $this->assertSame($c(1, 2), 301);
    }
}
