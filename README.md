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
To mark a method as a setup, annotate the method with a `<<setup>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple setup methods may be declared, but the execution order is not guaranteed.

Each setup method (both suite and test) MUST require exactly 0 parameters.  If you mark a method as setup and it requires a parameter, it will not be executed.

```php
<<setup(‘suite’)>>
public function setUpSuite() : void
{
  // Perform tasks before any tests in this suite are run
}

<<setup(‘test’)>>
public function setUpTest() : void
{
  // Perform tasks just before each test in this suite is run
}

<<setup>>
public function setUpTest() : void
{
  // Multiple set up methods may be defined
  // If there are no parameters to the stup attribute, the method is treated like a test setup
}
```

###Teardown##
To mark a method as teardown, annotate the method with a `<<setup>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple teardown methods may be declared, but the execution order is not guaranteed.

Each teardown method (both suite and test) MUST require exactly 0 parameters.  If you mark a method as teardown and it requires a parameter, it will not be executed.

```php
<<teardown(‘suite’)>>
public function cleanUpAfterSuite() : void
{
  // Perform tasks after all tests in this suite are run
}

<<teardown(‘test’)>>
public function cleanUpAfterTest() : void
{
  // Perform tasks just after each test in this suite is run
}

<<teardown>>
public function cleanUpMoarStuff() : void
{
  // This is also a ‘test’ teardown method
}
```

###Tests###
Individual test methods are defined using the `<<test>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Execution order of the tests is not guaranteed.

Each test method MUST *accept* exactly 1 parameter, with the type hint of `\HackPack\Hackunit\Assertion\AssertionBuilder`.
If you mark a method as a test and the signature does not match, the test will not be run.

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
