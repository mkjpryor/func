# `mkjpryor/function-utils`

This package provides some simple utilities for working with functions. It's purpose is to provide some of the core capabilities to enable a more functional style of programming.


## Installation

`mkjpryor/function-utils` can be installed via [Composer](https://getcomposer.org/):

```bash
php composer.phar require mkjpryor/function-utils dev-master
```


## Available functions

All functions are in the `Mkjp\FunctionUtils` namespace. To import them into your code, use the `use function` syntax (PHP 5.6+).

**`_()`**

Returns a placeholder object for use with  below.

----

**`auto_bind(callable $f, $n = -1)`**

Returns a new "auto-binding" function that continues to accept arguments for `$f`, including placeholders, until it has enough bound arguments to call `$f`.

An argument is considered bound when a value has been supplied for it that is not a placeholder.

By default, the returned function only waits for the *required* arguments of `$f` to be bound. If `$n` is given, it will wait for exactly that many arguments to be bound before calling `$f`.

Example:

```php
function fun1($a, $b, $c, $d = 0) { return $a + $b + $c + $d; }

// This only auto-binds the first 3 arguments
$fn = auto_bind('fun1');

// The following both print 6
echo $fn(1, 2, 3);

$tmp = $fn(1, _(), 3);
echo $tmp(2);


// This auto-binds all 4 arguments
$fn = auto_bind('fun1', 4);

// The following all print 10
echo $fn(1, 2, 3, 4);

$tmp = $fn(1, _(), 3);
echo $tmp(2, 4);

$tmp = $fn(1, _(), 3);  // This shows how the "auto-binding" function continues
$tmp = $tmp(_(), 4);    // to accept arguments until it can call $f
echo $tmp(2);
```

----

**`bind(callable $f, ...$args)`**

Returns a new "auto-bound" function (see above) with the first N arguments of `$f` bound to the given arguments.

This is equivalent to, but possibly more efficient than, creating an "auto-bound" function from `$f` and calling it with the given arguments:

```php
$fn = auto_bind($f);
return $fn(...$args);
```

Unlike `auto_bind`, `bind` will only work with functions of an unambiguous arity, i.e. optional and variadic arguments will *not* be considered.

Example:

```php
function fun1($a, $b, $c, $d) { return $a + $b + $c + $d; }

// The following all print 10
echo fun1(1, 2, 3, 4);

$tmp = bind('fun1', 1, _(), 3);
echo $tmp(2, 4);

$tmp = bind('fun1', 1, _(), 3);
$tmp = $tmp(_(), 4);
echo $tmp(2);
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

Returns a new function that caches the results of calls to the given function and returns them instead of calling the given function.

As a result, `$f` is only ever called once for a given combination of arguments. This is useful with pure functions whose result is expensive to compute but depends only on the arguments.

NOTE: It is only worth memoizing a function if it is frequently called with the same arguments and the result takes longer to compute than serializing the arguments and looking up the resulting key. It may be useful to profile your program first to see what functions might be candidates for memoization.

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
