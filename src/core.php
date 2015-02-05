<?php

namespace Mkjp\FunctionUtils;


/**
 * Returns a placeholder object
 * 
 * NOTE: Since it is namespaced, this doesn't count as re-declaring _ as used by
 *       gettext, and it will override the gettext version in any files where it
 *       is imported
 *       If you need to use both in the same file, you can either use the fully-
 *       qualified name for this function or use an alias, for example to
 *       __ (double underscore):
 * 
 *         use function Mkjp\FunctionUtils\_ as __;
 */
function _() {
    return Placeholder::get();
}


/**
 * Returns a new function that is the composition of the given functions from left
 * to right, i.e. the result of the first gets passed to the second, etc.
 * 
 * For example, given
 * 
 *     $fn = compose($f, $g, $h);
 * 
 * then
 * 
 *     $fn($x) === $h($g($f($x)));
 * 
 * The returned function will take as many arguments as the left-most function
 * All other functions should take a single argument - the result of the previous
 * function
 */
function compose(...$fns) {
    // If no functions are given, just use the id function
    if( count($fns) < 1 ) $fns = ['Mkjp\FunctionUtils\id'];
    
    return function(...$args) use($fns) {
        // Do the first call manually by splatting the args
        // NOTE: We know there is at least one function
        $result = $fns[0](...$args);
        // Apply the rest of the functions recursively
        foreach( array_slice($fns, 1) as $f ) $result = $f($result);
        return $result;
    };
}


/**
 * Takes a callable $f taking several arguments and converts it into a cascade
 * of functions of a single argument, e.g. given a function
 * 
 *     $f = function($x1, $x2, $x3) { ... };
 * 
 * curry($f) produces a new function $g such that:
 * 
 *     $g = function($x1) {
 *         return function($x2) {
 *             return function($x3) {
 *                 return $f($x1, $x2, $x3);
 *             };
 *         };
 *     };
 * 
 * By default, curry "curries" all the **required** arguments of a function
 * If $n is given, the "cascade of functions" is only created for that many arguments
 * This is useful for functions that are variadic or have optional arguments
 */
function curry(callable $f, $n = -1) {
    if( $n < 0 ) $n = n_required_args($f);
    
    // If we have a function of 0 or 1 arguments, there is nothing to do
    if( $n < 2 ) return $f;
    
    // Otherwise return a new function that gathers the arguments
    // We know that $f takes at least 2 arguments
    return function($x) use($f, $n) {
        return curry(partial($f, $x), $n - 1);
    };
}


/**
 * Returns a new function that is like $f but with the argument order flipped
 */
function flip(callable $f) {
    return function(...$args) use($f) {
        $flipped = array_reverse($args);
        return $f(...$flipped);
    };
}


/**
 * The id function, i.e. returns the given object unchanged
 */
function id($x) {
    return $x;
}


/**
 * Uses reflection to determine the number of required arguements for a callable
 * 
 * NOTE: This will NOT consider any arguments that are not required (i.e. that
 *       take default values if not given)
 */
function n_required_args(callable $f) {
    /*
     * Get an appropriate reflector
     * 
     * If we have some kind of method (static or instance), this should be a
     * ReflectionMethod
     * 
     * If we have a function name or closure, this should be a ReflectionFunction
     */
    $reflector = null;
    
    if( is_array($f) ) {
        // If we have an array, that is a method
        $reflector = new \ReflectionMethod($f[0], $f[1]);
    }
    elseif( is_string($f) && strpos($f, '::') !== false ) {
        // If we have a string containing '::', that is a static method
        $reflector = new \ReflectionMethod($f);
    }
    else {
        // Otherwise, we have some kind of function
        $reflector = new \ReflectionFunction($f);
    }
    
    return $reflector->getNumberOfRequiredParameters();
}


/**
 * Returns a new function where the arguments of $f are frozen with the given
 * arguments
 * 
 * Any arguments for which a value is not given or for which the placeholder
 * is given remain "free", and become arguments of the returned function
 */
function partial(callable $f, ...$frozen) {
    // If there are no frozen arguments, just return the function
    if( empty($frozen) ) return $f;
    
    // Otherwise, return a function that replaces placeholders with the given
    // arguments when it is executed
    return function(...$args) use($f, $frozen) {
        // Take a copy of the frozen arguments to modify
        // This is so we don't touch $frozen so that the closure can be reused
        $merged = $frozen;
        
        // Wherever there is a placeholder, replace it with a given argument
        foreach( $merged as &$arg ) {
            if( $arg instanceof Placeholder ) $arg = array_shift($args);
        }

        // Add the rest of the given arguments to the end of the call to $f
        return $f(...$merged, ...$args);
    };
}
