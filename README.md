HackUnit
========

[![Build Status](https://travis-ci.org/HackPack/HackUnit.png)](https://travis-ci.org/HackPack/HackUnit)
[![HHVM Status](http://hhvm.h4cc.de/badge/HackPack/HackUnit.svg)](http://hhvm.h4cc.de/package/HackPack/HackUnit)

> xUnit written in Hack, for Hack

xUnit testing framework written in Facebook's language, [Hack](http://docs.hhvm.com/manual/en/index.php)

Built against latest HHVM stable release.

Usage
-----
HackUnit can be run using `bin/hackunit` or if installed via composer - `vendor/bin/hackunit`.

```
bin/hackunit [--exclude="exclude/path1"] [--exclude=”exclude/path2”] ... path1 [path2] ...
```

###Excluding paths###
To exclude files/paths from being loaded, specify them with the `--exclude` option on the command line.
This option may be specified multiple times, one for each directory and/or file to exclude.

Test Suites
-----------
To define a test suite, you simply need to create a class and [annotate](http://docs.hhvm.com/manual/en/hack.attributes.php) the appropriate methods.
All methods annotated as described below must be instance methods (non-static), and may not be the constructor, nor the destructor.

You may inspect HackUnit’s test files for concrete examples.

###Setup###
To mark a method as a setup, annotate the method with a `<<Setup>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple setup methods may be declared, but the execution order is not guaranteed.

Each setup method (both suite and test) MUST require exactly 0 parameters.  If you mark a method as setup and it requires a parameter, it will not be executed.

```php
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
```

###Teardown##
To mark a method as teardown, annotate the method with a `<<TearDown>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple teardown methods may be declared, but the execution order is not guaranteed.

Each teardown method (both suite and test) MUST require exactly 0 parameters.  If you mark a method as teardown and it requires a parameter, it will not be executed.

```php
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
```

###Tests###
Individual test methods are defined using the `<<Test>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Execution order of the tests is not guaranteed.

Each test method MUST *accept* exactly 1 parameter, with the type hint of `\HackPack\HackUnit\Assertion\AssertionBuilder`.
If you mark a method as a test and the signature does not match, the test will not be run.

```php
namespace My\Namespace\Test;

use \HackPack\HackUnit\Assertion\AssertionBuilder;

<<Test>>
public function testSomething(AssertionBuilder $assert) : void
{
  // Do some testing here!
  $assert->context(2)->isNot()->equalTo(3);
  $assert->callable(() ==> {throw new \Exception(‘bad error)})
    ->willThrow(\Exception::class, ‘bad error’);
}
```

Assertions
----------

All test methods must accept exactly one parameter of type `\HackPack\HackUnit\Assertion\AssertionBuilder` which should be used to make testable assertions.
This object is used to build assertions that will be checked and reported by HackUnit.

There are two classes of assertions that may be made: constraints on a context and determining if a closure will throw an exception.

###Context Assertions###

To build a context assertion, call the `context()` method of the `AssertionBuilder` object passed to your test.

```php
use HackPack\HackUnit\Assertion\AssertionBuilder;

<<TestSuite>>
class MyTest{
    <<Test>>
    public function testSomething(AssertionBuilder $assert) : void
    {
        // Do some testing here!
        $assert->context(2)->isNot()->equalTo(3);
        $assert->context(2)->identicalTo()->equalTo(3);
        // etc...
    }
}
```

The available assertions are currently:

 * equalTo
 * identicalTo
 * greaterThan
 * lessThan
 * contains (see below)

All of the assertions may be negated by calling `isNot()`, `willNot()`, or `not()` before calling the assertion method listed above.
The `contains` assertion uses `strpos` to determine if the string passed is a substring of the context given.  This assertion will always fail if the context is not a string.

###Callable Assertions###

To check if a particular function and/or method throws an exception (or does not throw), use the `callable()` method of the `AssertionBuilder`.

```php
use HackPack\HackUnit\Assertion\AssertionBuilder;

<<TestSuite>>
class MyTest{
    <<Test>>
    public function testSomething(AssertionBuilder $assert) : void
    {
        $obj = new SubjectUnderTest();

        // Do some testing here!
        $assert->callable(() ==> {
            throw new \Exception(‘bad message’);
        })->willThrow(\Exception::class, ‘bad message’);
        $assert->callable(() ==> {
            $obj->methodUnderTest();
        })->willNot()->raiseException(\Exception::class, ‘bad message’);
        // etc...
    }
}
```

There is only one assertion method defined in the callable assertion: `willThrow` (with an alias of `raiseException`) which accepts two strings as optional parameters.  The first one is the name
of the exception class you expect to be thrown, the second is the exception message you expect.  Setting either parameter to `null` (or not providing it) will
allow any exception class/message to pass the assertion, respectively.

Notes
-----
###Project Goal###
The goal of HackUnit is to write a testing framework using Hack's strict mode. HackUnit itself is tested with HackUnit.

Top level code must use a hack mode of `// partial`, so the `bin/hackunit` file is not in strict mode.  The rest of the project is, with one exception.
The loader class must dynamically include test suite files.  The only way I can see to perform this dynamic inclusion is to use `include_once` inside
of a class method, which is disallowed in strict mode.  This one exception is marked with a `/* HH_FIXME */` comment, which disables the type checker for that
one line.

These requirements may change as Hack evolves.

###How HackUnit loads tests###
All files inside the base path(s) specified from the command line will be scanned for class definitions using HackPack’s
[Class Scanner](https://github.com/HackPack/HackClassScanner) library.  Those files will then be loaded and reflection is used to determine
which classes are test suites, and which methods perform each task in the suite.

Running HackUnit's tests
------------------------
From the project directory run this:

```
bin/hackunit --exclude Tests/Fixtures/ Tests/
```
