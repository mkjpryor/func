<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\n_required_args;


function test_fun($x, $y, $z) {}


function optional_args($x, $y, $z = 1) {}


class TestClass {
    public function instanceMethod($x, $y, $z) {}
    public static function staticMethod($x, $y, $z) {}
}


class NRequiredArgsTest extends \PHPUnit_Framework_TestCase {
    public function testBuiltinFunction() {
        $this->assertSame(n_required_args('str_repeat'), 2);
    }
    
    public function testUserFunction() {
        $this->assertSame(n_required_args(__NAMESPACE__ . '\\test_fun'), 3);
    }
    
    public function testAnonymousFunction() {
        $this->assertSame(n_required_args(function($x, $y, $z) {}), 3);
    }
    
    public function testOptionalArgs() {
        $this->assertSame(n_required_args(__NAMESPACE__ . '\\optional_args'), 2);
    }
    
    public function testInstanceMethod() {
        $o = new TestClass;
        $this->assertSame(n_required_args([$o, 'instanceMethod']), 3);
    }
    
    public function testStaticMethod() {
        // We test both array and class::method syntaxes
        $this->assertSame(n_required_args([TestClass::class, 'staticMethod']), 3);
        $this->assertSame(n_required_args(TestClass::class . '::staticMethod'), 3);
    }
}
