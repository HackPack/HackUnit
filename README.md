HackUnit
========

xUnit testing framework written in and for Facebook's language, [Hack](http://hacklang.org)

###But Why?!###
There are already many testing frameworks available, such as [PHPUnit](https://phpunit.de/) and [behat](http://behat.org).  Why should you use this one?

*Because you like Hack's strict mode!*

The goal of HackUnit is to write a testing framework using Hack's strict mode. HackUnit itself is tested with HackUnit.

Top level code must use a hack mode of `// partial`, so the `bin/hackunit` file is not in strict mode.  The rest of the project is, with one exception.
The loader class must dynamically include test suite files.  The only way I can see to perform this dynamic inclusion is to use `include_once` inside
of a class method, which is disallowed in strict mode.  This one exception is marked with a `/* HH_FIXME */` comment, which disables the type checker for that
one line.

These requirements may change as Hack evolves.

Install
-------

Install HackUnit using [Composer](https://getcomposer.org):

```bash
composer require hackpack/hackunit
```

Usage
-----

HackUnit can be run from the command line using the included executable script `bin/hackunit`. By default, this will be symlinked in your `vendor/bin` directory.
Thus, the most common way to invoke HackUnit is:
```bash
vendor/bin/hackunit path1 [path2] ...
```
where `path1`, `path2`, etc... are each base paths/files to scan for test suites.

Some command line options exist to alter the behavior of HackUnit:

* --exclude="path/to/exclude" : Do not scan the file or any file under the path provided.  This option may be given multiple times to exclude multiple paths/files. 

Test Suites
-----------
To define a test suite, create a class and [annotate](http://docs.hhvm.com/manual/en/hack.attributes.php) the appropriate methods.
All methods annotated as described below must be instance methods (non-static), and may not be the constructor, nor the destructor.

You may inspect HackUnit’s test files for concrete examples.

###Setup###
You may have HackUnit run some methods before each individual test method is run and/or before any test method is
run for the suite.  To do so, mark the appropriate method with the
`<<Setup>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple setup methods may be declared, but the execution order is not guaranteed.

Each setup method (both suite and test) MUST require exactly 0 parameters.  If you mark a method as setup and it requires a parameter, it will not be executed.

```php
class MySuite
{
    <<Setup(‘suite’)>>
    public function setUpSuite() : void
    {
      // Perform tasks before any tests in this suite are run
    }

    <<Setup(‘test’)>>
    public function setUpTest() : void
    {
      // Perform tasks just before each test in this suite is run
    }

    <<Setup>>
    public function setUpTest() : void
    {
      // Multiple set up methods may be defined
      // If there are no parameters to the stup attribute, the method is treated like a test setup
    }
}
```

Suite setup methods are run once, before any of the test methods in the class are run.

Test setup methods are run just before each test method is run (and thus are potentially run multiple times).

###Teardown###
You may have HackUnit run some methods after each individual test method is run and/or after all test methods are
run for the suite.  To do so, mark the appropriate method with the
`<<TearDown>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple teardown methods may be declared, but the execution order is not guaranteed.

Each teardown method (both suite and test) MUST require exactly 0 parameters.  If you mark a method as teardown and it requires a parameter, it will not be executed.

```php
class MySuite
{
    <<TearDown(‘suite’)>>
    public function cleanUpAfterSuite() : void
    {
      // Perform tasks after all tests in this suite are run
    }

    <<TearDown(‘test’)>>
    public function cleanUpAfterTest() : void
    {
      // Perform tasks just after each test in this suite is run
    }

    <<TearDown>>
    public function cleanUpMoarStuff() : void
    {
      // This is also a ‘test’ teardown method
    }
}
```

Suite tear down methods are run once, after all of the test methods in the class are run.

Test tear down methods are run just after each test method is run (and thus are potentially run multiple times).

###Tests###

Individual test methods are defined using the 
`<<Test>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Execution order of the tests is not guaranteed.

Each test method MUST accept exactly 1 parameter, with the type hint of `HackPack\HackUnit\Contract\Assert`.
If you mark a method as a test and the signature does not match, the test will not be run.

```php
namespace My\Namespace\Test;

use HackPack\HackUnit\Contract\Assert;

class MySuite
{
    <<Test>>
    public function testSomething(Assert $assert) : void
    {
      // Do some testing here!
      $assert->int(2)->not()->eq(3);
      $assert
          ->whenCalled(() ==> {throw new \Exception(‘bad error)})
          ->willThrowClassWithMessage(\Exception::class, ‘bad error’)
          ;
    }
}
```

Assertions
----------

All test methods must accept exactly one parameter of type `HackPack\HackUnit\Contract\Assert` which should be used to make testable assertions.
This object is used to build assertions that will be checked and reported by HackUnit.

In all examples below, `$assert` contains an instance of `HackPack\HackUnit\Contract\Assert`.

### Bool Assertions ###

To make assertions about `bool` type variables, call `$assert->bool($myBool)->is($expected)`.

### Numeric Assertions ###

To make assertions about `int` and `float` type variables, call `$assert->int($myInt)` and `$assert->float($myFloat)` respectively.
The resulting object contains the following methods to actually perform the appropriate assertion.

* `$assert->int($myInt)->eq($expected);` : Assert that `$myInt` is identical to `$expected`
* `$assert->int($myInt)->gt($expected);` : Assert that `$myInt` is greater than `$expected`
* `$assert->int($myInt)->lt($expected);` : Assert that `$myInt` is less than `$expected`
* `$assert->int($myInt)->gte($expected);` : Assert that `$myInt` is greater than or equal to `$expected`
* `$assert->int($myInt)->lte($expected);` : Assert that `$myInt` is less than or equal to `$expected`

All of the above may be modified with a call to `not()` before the assertion to negate the meaning of the assertion.  For example:

```
 $assert->int($myInt)->not()->eq($expected);
```

*Note*: This library only allows assertions to compare identical numeric types.  `$assert->int(1)->eq(1.0);` produces a type error.

### String Assertions ###

To make assertions about `string` type variables, call `$assert->string($myString)`.  The resulting object contains the following methods to actually perform the appropriate assertion.

* `$assert->string($myString)->is($expected)` : Assert that `$myString === $expected`
* `$assert->string($myString)->hasLength($int)` : Assert that the string has a length of `$int`
* `$assert->string($myString)->matches($pattern)` : Assert that the regular expression contained in `$pattern` matches the string
* `$assert->string($myString)->contains($subString)` : Assert that `$subString` is a substring of `$myString`
* `$assert->string($myString)->containedBy($superString)` : Assert that `$myString` is a substring of `$superString`

All of the above assertions may be negated by calling `not()` before making the assertion.  For example:

```
 $assert->string($myString)->not()->containedBy($superString);
```

### Mixed Assertions ###

To make generic assertions about a variable of any type, call `$assert->mixed($context)`.  The resulting object contains the following methods to actually perform the appropriate assertion.

* `$assert->mixed($context)->isNull();` : Assert that `$context === null`
* `$assert->mixed($context)->isBool();` : Assert that `$context` is of type `bool`
* `$assert->mixed($context)->isInt();` : Assert that `$context` is of type `int`
* `$assert->mixed($context)->isFloat();` : Assert that `$context` is of type `float`
* `$assert->mixed($context)->isString();` : Assert that `$context` is of type `string`
* `$assert->mixed($context)->isArray();` : Assert that `$context` is of type `array`
* `$assert->mixed($context)->isObject();` : Assert that `$context` is of type `object`
* `$assert->mixed($contect)->isTypeOf($className)` : Assert that `$context instanceof $className`  
* `$assert->mixed($context)->looselyEquals($expected)` : Assert that `$context == $expected` *note the loose comparison* 
* `$assert->mixed($context)->identicalTo($expected)` : Assert that `$context === $expected` *note the strict comparison*

Skipping Tests
-------------
There are two ways to skip execution of a particular test method:

1. Add the attribute `<<Skip>>` to the test method or the test suite.  If the `<<Skip>>` attribute is added to the suite, all tests in that class will be skipped.
1. Invoke the `skip()` method of the `Assert` object passed to your test method.

```php
use \HackPack\HackUnit\Contract\Assert;

<<Skip>>
class SkippedSuite
{
    // All methods here would be skipped
}

class MySuite
{
    <<Test, Skip>>
    public function skippedTest(Assertion $assert) : void
    {
        // This will not be run and the test will be marked skip in the report.
    }

    <<Test>>
    public function skippFromMiddleOfTest(Assert $assert) : void
    {
        // This will be run
        $assert->skip();
        // This will not be run and the test will be marked skip in the report.
    }
}
```

Future Plans
------------

I would like to implement collection type assertions.  These may take the form of `$assert->map($myMap)->hasSameKeysAs($expectedMap);` or similar.  If you have suggestions for the types of assertions that could be made on collections, please open a ticket!

How HackUnit loads tests
------------------------
All files inside the base path(s) specified from the command line will be scanned for class definitions using HackPack’s
[Class Scanner](https://github.com/HackPack/HackClassScanner) library.  Those files will then be loaded and reflection is used to determine
which classes are test suites, and which methods perform each task in the suite.

Running HackUnit's tests
------------------------
From the project directory run:

```
bin/hackunit test --exclude test/Fixtures/ --exclude test/Mocks/
```
