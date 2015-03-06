<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\curry;



class CurryTest extends \PHPUnit_Framework_TestCase {
    public function testCurry() {
        // There isn't much to check here other than making sure the cascade works
        // correctly
        $f = \Mockery::mock(CallableInterface::class)
                 ->shouldReceive('__invoke')
                     ->with(1, 2, 3, 4)->once()->andReturn(101)
                 ->getMock();
                 
        $c = curry($f, 4);
        $c = $c(1);
        $c = $c(2);
        $c = $c(3);
        $this->assertSame($c(4), 101);
    }
}
