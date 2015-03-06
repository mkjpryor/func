<?php

namespace Mkjp\FunctionUtils\Test;

use function Mkjp\FunctionUtils\auto_bind_namespace;
use function Mkjp\FunctionUtils\_;



class AutoBindNamespaceTest extends \PHPUnit_Framework_TestCase {
    public function testAutoBoundFunctionExists() {
        // In order to get coverage for auto_bind_namespace, we can't call it in
        // setUpBeforeClass
        // So we do all our assertions in one test
        
        // Import the namespace and auto-bind all functions accept fun2
        require_once dirname(__FILE__) . "/test_namespace.php";
        auto_bind_namespace("test_namespace", "_p", ['fun2']);
        
        // Check that the new functions exist, or don't, as appropriate
        $this->assertTrue(function_exists("test_namespace\\fun1_p"));
        $this->assertFalse(function_exists("test_namespace\\fun3_p"));
        
        // Check that the auto-bound function behaves correctly
        // Run in 3 different ways against the same auto-bound function
        $this->assertSame(\test_namespace\fun1_p(1, 2, 3), \test_namespace\fun1(1, 2, 3));
        
        // We re-use this partially-bound function
        $f = \test_namespace\fun1_p(_(), 2);
        
        $this->assertSame($f(1, 3), \test_namespace\fun1(1, 2, 3));
        
        $f = $f(_(), 3);
        $this->assertSame($f(1), \test_namespace\fun1(1, 2, 3));
    }
}
