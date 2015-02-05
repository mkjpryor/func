# mkjpryor/function-utils

This package provides some simple utilities for working with functions. It's purpose is to provide some of the core capabilities to enable a more functional style of programming.


## Available functions

All functions are in the `Mkjp\FunctionUtils` namespace.

**`_()`**

Returns a placeholder object for use with `partial` below.

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

By default, `curry` "curries" all the **required** arguments of a function. If `$n` is given, the "cascade of functions" is only created for that many arguments. This is useful for functions that are variadic or have optional arguments.

Since PHP doesn't currently support function call dereferencing (e.g. `$f(1)(2)(3)`), this is of limited use for stylistic purposes. However, functions that behave in this way (i.e. returning new functions until all their arguments are given) can be useful in functional programming.

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

**`partial(callable $f, ...$frozen)`**

Returns a new function where the first N arguments of `$f` are "frozen" with the given arguments.

Any arguments for which a value is not given or for which the placeholder is given (see `_()` above) remain "free", and become arguments of the returned function.

Example:

```php
function fun1($a, $b, $c, $d) { return $a + $b - $c + $d; }

// The following all print 4
echo fun1(1, 2, 3, 4);

$fn = partial('fun1', 1, 2);
echo $fn(3, 4);

$fn = partial('fun1', 1, _(), 3);
echo $fn(2, 4);
```
