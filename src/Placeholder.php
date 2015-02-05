<?php

namespace Mkjp\FunctionUtils;


/**
 * Singleton whose instance is used as a placeholder in partial function application
 */
final class Placeholder {
    private static $instance = null;
    
    private function __construct() { }
    
    public static function get() {
        if( self::$instance === null ) self::$instance = new Placeholder();
        return self::$instance;
    }
}
