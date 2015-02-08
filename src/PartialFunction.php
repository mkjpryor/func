<?php

namespace Mkjp\FunctionUtils;


/**
 * Invoke-able class for a function that can have its arguments partially applied
 */
final class PartialFunction {
    // The function to call once all arguments are available
    private $fn = null;
    
    // The arguments given so far
    private $bound = [];
    
    // The number of arguments that we require before calling $f
    private $required = 0;
    
    /**
     * Create a new partial function with the given "underlying" function and the
     * given bound arguments
     * 
     * At the consumers peril, the number of required arguments can be given as
     * an argument to avoid the overhead of using reflection to detect it
     */
    public function __construct(callable $fn, array $bound, $required = -1) {
        $this->fn = $fn;
        $this->bound = $bound;
        $this->required = $required >= 0 ? $required : n_required_args($fn);
    }
    
    /**
     * Invoke the partial function with some more arguments
     * 
     * If enough arguments have been given to call the underlying function, the
     * result of calling the function is returned
     * 
     * If there are not enough arguments to call the underlying function, a new
     * partial function is returned to gather the rest of the arguments
     */
    public function __invoke(...$args) {
        // Copy the currently bound arguments so we are not modifying this function
        $bound = $this->bound;
        
        // Merge in the args from the left, observing any placeholders
        foreach( $bound as $pos => $arg ) {
            if( count($args) <= 0 ) break;
            if( $arg instanceof Placeholder ) $bound[$pos] = array_shift($args);
        }
        $bound = array_merge($bound, $args);
        
        // Take at most the number of args we need to call the function
        $bound = array_slice($bound, 0, $this->required);
        
        // If we don't have enough, return a new PF
        if( count($bound) < $this->required )
            return new PartialFunction($this->fn, $bound, $this->required);
            
        // If we have any placeholders left, return a new PF
        foreach( $bound as $arg ) {
            if( $arg instanceof Placeholder ) {
                return new PartialFunction($this->fn, $bound, $this->required);
            }
        }
        
        // Otherwise, return the result of calling the function
        return call_user_func_array($this->fn, $bound);
    }
}
