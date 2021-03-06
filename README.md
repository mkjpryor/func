# `mkjpryor/function-utils`

[![Build Status](https://travis-ci.org/mkjpryor/function-utils.svg?branch=master)](https://travis-ci.org/mkjpryor/function-utils) [![Coverage Status](https://coveralls.io/repos/mkjpryor/function-utils/badge.svg)](https://coveralls.io/r/mkjpryor/function-utils)

This package provides some simple utilities for working with functions. It's purpose is to provide some of the core capabilities to enable a more functional style of programming.


## Installation

`mkjpryor/function-utils` can be installed via [Composer](https://getcomposer.org/):

```bash
php composer.phar require mkjpryor/function-utils dev-master
```


## Available functions

All functions are in the `Mkjp\FunctionUtils` namespace. To import them into your code, use the `use function` syntax (PHP 5.6+).

**`_()`**

Returns a placeholder object for use with `bind` below.

----

**`bind(callable $f, ...$bound)`**

Binds the first n arguments of `$f` to the given arguments, returning a new function that accepts the rest of the arguments before calling `$f`.

When binding arguments, a placeholder (see `_()`) can be given to indicate that the argument will be given later.

Example:

```php
function add($a, $b, $c) { return $a + $b + $c; }

$add = bind('add', 1, _(), 3);

echo $add(2);  // Calls $f(1, 2, 3) and prints 6
```

----

**`compose(...$fns)`**

Returns a new function that is the composition of the given functions from left to right, i.e. given these functions

```php
function f($x) { /* something */ };
function g($x) { /* something */ };
function h($x) { /* something */ };

$fn = compose('f', 'g', 'h');
```

the following holds

```php
$fn($x) === h(g(f($x)));
```

The returned function will take as many arguments as the first function. All other given functions should take a single argument.

Example:

```php
function add($x, $y) { return $x + $y; }
function double($x) { return $x * 2; }

$fn = compose('add', 'double');

// The returned function takes the same arguments as add
echo $fn(1, 2);  // Prints 6
```

----

**`curry(callable $f, $n = -1)`**

Takes a callable that takes several arguments and converts it into a cascade of functions of a single argument, e.g. given a function

```php
$f = function($x1, $x2, $x3) { /* something */ };
```

`curry($f)` produces a new function `$g` such that

```php
$g = function($x1) {
    return function($x2) use($x1) {
        return function($x3) use($x1, $x2) {
            return $f($x1, $x2, $x3);
        };
    };
};
```

By default, `curry` "curries" all the *required* arguments of a function. If `$n` is given, the "cascade of functions" is created for exactly that many arguments. This is useful for functions that are variadic or have optional arguments.

Since PHP doesn't currently support function call dereferencing (e.g. `$f(1)(2)(3)`), this is of limited use for stylistic purposes. However, functions that behave in this way (i.e. returning new functions until all their arguments are given) can be useful in functional programming (which is why all functions in Haskell are automatically curried).

Example:

```php
function add($x, $y, $z) { return $x + $y + $z; }

$curried = curry('add');

// If we had function call dereferencing, this could be written $curried(1)(2)(3)
$tmp = $curried(1);
$tmp = $tmp(2);
echo $tmp(3);  // Prints 6
```

----

**`flip(callable $f)`**

Returns a new function that is the same as `$f` but with the order of the arguments flipped.

Example:

```php
function div($x, $y) { return $x / $y; }

$flipped = flip('div');

// Both of these print 5
echo div(10, 2);
echo $flipped(2, 10);
```

----

**`id($x)`**

This is the classic id function. It just returns its argument.

----

**`memoize(callable $f)`**

Returns a new function that caches the results of calls to `$f` and returns them instead of calling `$f`.

As a result, `$f` is only ever called once for a given combination of arguments. This is useful with pure functions whose result is expensive to compute but depends only on the arguments.

**NOTE:** It is only worth memoizing a function if it is frequently called with the same arguments and the result takes longer to compute than serializing the arguments and looking up the resulting key. It may be useful to profile your program first to see what functions might be candidates for memoization.

Example:

```php
function factorial($n) {
    if( $n <= 0 ) return 1;
    return $n * factorial($n - 1);
}

$mem_fac = memoize('factorial');

echo $mem_fac(10);  // Prints 3628800
echo $mem_fac(10);  // Prints 3628800, but the result was loaded from cache
```

----

**`n_required_args(callable $f)`**

Uses reflection to determine the number of required arguements for a callable.

This will only consider *required* arguments, i.e. optional and variadic arguments do *not* contribute to the count.

Example:

```php
function fun1($a, $b, $c, $d = 1) { return $a + $b + $c + $d; }

echo n_required_args('fun1');  // Prints 3

function fun2(...$args) { return count($args); }

echo n_required_args('fun2');  // Prints 0
```

----

**`trampoline(callable $f)`**

Given a function written in trampolined style, `trampoline` returns a new function that executes the trampolined function when called (i.e. iteratively executes the thunk-returning function until the result is no longer callable).

This can be used to emulate tail-recursion without blowing the stack - see the pseudo-recursive implementation of factorial below.

Example:

```php
// This is the traditional tail-recursive fib, except PHP doesn't have tail-recursion
function fib($n, $n_1 = 0, $n_2 = 1) {
    if( $n === 0 ) return $n_1;
    return fib($n - 1, $n_2, $n_1 + $n_2);
};

// This is the trampolined version
function fib_trampoline($n, $n_1 = 0, $n_2 = 1) {
    if( $n === 0 ) return $n_1;
    return function() use($n, $n_1, $n_2) {
        return fib_trampoline($n - 1, $n_2, $n_1 + $n_2);
    };
}

$fib_trampoline = trampoline('fib_trampoline');

echo fib(10);              // Prints 55
echo $fib_trampoline(10);  // Prints 55

echo fib(200);              // BLOWS THE STACK!
echo $fib_trampoline(200);  // Prints 2.8057117299251E+41
```
