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
 * Returns a new function that continues to accept arguments to $f, including 
 * placeholders, until it has enough arguments to call $f
 * 
 * By default, the returned function only waits for all the *required* arguments
 * to be bound
 * To auto-bind a function with optional or variadic arguments, the number of
 * arguments to bind can be specified
 * 
 * See the README for example usage
 */
function auto_bind(callable $f, $n = -1) {
    return new PartialFunction($f, [], $n);
}


/**
 * Returns a new auto-bound function (see above) with the first N arguments bound
 * to the given arguments
 * 
 * NOTE: This function will only work with functions of an unambiguous arity, i.e.
 *       optional and variadic arguments will NOT be considered
 *       For this, you must manually create an auto_bound function, specifying the
 *       number of arguments to fill
 */
function bind(callable $f, ...$args) {
    return new PartialFunction($f, $args);
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
        for( $i = 1, $l = count($fns); $i < $l; $i++ ) {
            $result = $fns[$i]($result);
        }
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
        $fn = auto_bind($f, $n);
        return curry($fn($x), $n - 1);
    };
}


/**
 * Returns a new function that is like $f but with the argument order flipped
 */
function flip(callable $f) {
    return function(...$args) use($f) {
        return $f(...array_reverse($args));
    };
}


/**
 * The id function, i.e. returns the given object unchanged
 */
function id($x) {
    return $x;
}


/**
 * Returns a new function that caches the results of calls to the given function
 * and returns them instead of calling the given function
 * 
 * As a result, $f is only ever called once for a given combination of arguments
 * 
 * This is useful with pure functions whose result is expensive to compute
 */
function memoize(callable $f) {
    return function(...$args) use($f) {
        static $cache = [];

        $key = md5(serialize($args));
        if( !isset($cache[$key]) ) {
            $cache[$key] = $f(...$args);
        }
        return $cache[$key];
    };
}


/**
 * Uses reflection to determine the number of required arguements for a callable
 * 
 * NOTE: This will only consider *required* arguments, i.e. optional and variadic
 *       arguments do *not* contribute to the count
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
