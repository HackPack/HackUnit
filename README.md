HackUnit
========

Testing framework written in and for [Hack.](http://hacklang.org)

### But Why?!
There are already many testing frameworks available, such as [PHPUnit](https://phpunit.de/) and [behat.](http://behat.org)  Why should you use this one?

*Because you like Hack specific features!*

With HackUnit, you can easily run your tests using cooperative async using the built in `async` keyword.

With HackUnit, you indicate test methods using [annotations.](http://docs.hhvm.com/manual/en/hack.attributes.php)

The original goal of HackUnit was to write a testing framework using Hack's strict mode. The project will stay consistent with this goal as more features are added.

Install
-------

Install HackUnit using [Composer](https://getcomposer.org):

```bash
composer require --dev hackpack/hackunit
```

Usage
-----

HackUnit can be run from the command line using the included executable script `bin/hackunit`. By default, this will be symlinked in your `vendor/bin` directory.

Thus, the most common way to invoke HackUnit is:
```bash
vendor/bin/hackunit path1 [path2] ...
```
where `path1`, `path2`, etc... are each base paths/files to scan for test suites.  If any specified path is a directory, the directory will be recursively scanned.

Some command line options exist to alter the behavior of HackUnit:

* --exclude="path/to/exclude" : Do not scan the file or any file under the path provided.  This option may be given multiple times to exclude multiple paths/files.

Test Suites
-----------
To define a test suite, create a class and [annotate](http://docs.hhvm.com/manual/en/hack.attributes.php) the appropriate methods.

You may inspect HackUnit’s test files for concrete examples.

### Tests

Individual test methods are defined using the
`<<Test>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Execution order of the tests is not guaranteed.

Each test method MUST accept exactly 1 parameter, with the type hint of `HackPack\HackUnit\Contract\Assert`.
If you mark a method as a test and the signature does not match, the test will not be run.

Test methods may be instance methods, or they may be class (static) methods.

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

### Async

Running your tests async is as easy as adding the async keyword to your test method.

```php
namespace My\Namespace\Test;

use HackPack\HackUnit\Contract\Assert;

class MyAsyncSuite
{
    <<Test>>
    public async function testSomething(Assert $assert) : Awaitable<void>
    {
        // Make some async DB calls here as part of your test!
        $user = await get_user();

        // Or maybe an async curl call
        $result = await get_external_user($user->id, 'api password');

        $assert->string($result['user_name'])->is('expected username');
    }
}
```

All such `async` tests are run using cooperative multitasking (see the [async documentation](http://docs.hhvm.com/hack/async/introduction)),
allowing your entire test suite to run faster if your tests perform real I/O operations (DB calls, network calls, etc...).

### Setup

You may have HackUnit run some methods before each individual test method is run and/or before any test method is
run for the suite.  To do so, mark the appropriate method with the
`<<Setup>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple setup methods may be declared, but the execution order is not guaranteed.

Each setup method (both suite and test) MUST require exactly 0 parameters.
If you mark a method as setup and it requires a parameter, it will not be executed and a parse error will be shown in the report.

```php
class MySuite
{
    <<Setup(‘suite’)>>
    public function setUpSuite() : void
    {
      // Suite level Setup methods must be class (static) methods
      // Perform tasks before any tests in this suite are run
    }

    <<Setup(‘test’)>>
    public function setUpTest() : void
    {
      // Perform tasks just before each test in this suite is run
    }

    <<Setup>>
    public function setUpTestAgain() : void
    {
      // Multiple set up methods may be defined
      // If there are no parameters to the setup attribute, the method is treated like a test setup
    }
}
```

Suite setup methods are run once, before any of the test methods in the class are run.

Test setup methods are run just before each test method is run (and thus are potentially run multiple times).

### Teardown
You may have HackUnit run some methods after each individual test method is run and/or after all test methods are
run for the suite.  To do so, mark the appropriate method with the
`<<TearDown>>` [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php).
Multiple teardown methods may be declared, but the execution order is not guaranteed.

Each teardown method (both suite and test) MUST require exactly 0 parameters.
If you mark a method as teardown and it requires a parameter, it will not be executed and a parse error will be shown in the report.

```php
class MySuite
{
    <<TearDown(‘suite’)>>
    public static function cleanUpAfterSuite() : void
    {
      // Suite level TearDown methods must be class (static) methods
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

### Suite Providers
Your test suite may require parameters to be passed to the constructor.  To tell HackUnit how to construct your test suite, you must define at least one Suite Provider.
A Suite Provider is marked with the `<<SuiteProvider>>` attribute.

You may define multiple Suite Providers for a single test suite.  To do so, you must label each one
by passing in one string parameter to the attribute (i.e., `<<SuiteProvider('name of provider')>>`).
There are no restrictions on the name of a provider except that each provider name must be unique.

To use a particular Suite Provider for a particular test, you must pass the name of the Suite Provider to the Test attribute.

```
class SuiteWithProviders
{
    <<SuiteProvider('One')>>
    public static function() : this
    {
        $someDependency = new TestDoubleOne();
        return new static($someDependency);
    }

    <<SuiteProvider('Two')>>
    public static function() : this
    {
        $someDependency = new TestDoubleTwo();
        return new static($someDependency);
    }

    <<Test('One')>>
    public function testOne(Assert $assert) : void
    {
        // Do some assertions using TestDoubleOne
    }

    <<Test('Two')>>
    public function testTwo(Assert $assert) : void
    {
        // Do some assertions using TestDoubleTwo
    }
}
```

Assertions
----------

All test methods must accept exactly one parameter of type `HackPack\HackUnit\Contract\Assert` which should be used to make testable assertions.
This object is used to build assertions that will be checked and reported by HackUnit.

In all examples below, `$assert` contains an instance of `HackPack\HackUnit\Contract\Assert`.

### Bool Assertions

To make assertions about `bool` type variables, call `$assert->bool($myBool)->is($expected)`.

### Numeric Assertions

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

### String Assertions

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

### Collection Assertions

To make assertions about collections and arrays, call `$assert->container($context)`.  The resulting object contains the following methods to perform assertions.

* `$assert->container($context)->isEmpty();` : Assert that the context has no elements
* `$assert->container($context)->contains($value);` : Assert that the context contains the value given
* `$assert->container($context)->containsAny($list);` : Assert that the context contains at least one element in the list provided
* `$assert->container($context)->containsAll($list);` : Assert that the context contains all elements in the list provided
* `$assert->container($context)->containsOnly($list);` : Assert that the context contains all elements in the list provided and no more

All of the `contains*` assertions above accept an optional second parameter which must be a callable.  The callable will be used to compare the elements in the context with the element(s) provided. If the elements passed to the callable should be treated as equivalent, the callable should return `true`, otherwise it should return `false`.

#### Keyed Collections

If the keys of the container are important for the assertions, you should use `$assert->keyedContainer($context)`.  The resulting object contains the following methods to perform assertions.

* `$assert->container($context)->contains($key, $value);` : Assert that the value contained in the context at the key provided matches the value provided
* `$assert->container($context)->containsKey($key);` : Assert that the context contains the provided key
* `$assert->container($context)->containsAny($list);` : Assert that the context contains at least one element in the list provided where both the key and value must be considered equivalent
* `$assert->container($context)->containsAll($list);` : Assert that the context contains all elements in the list provided where both the key and value must be considered equivalent
* `$assert->container($context)->containsOnly($list);` : Assert that the context contains all elements in the list provided and no more where both the key and value must be considered equivalent

All of the assertions above accept an optional second (or third in the case of `contains`) parameter which must be a callable.  The callable will be used to compare the values of the elements in the context with the element(s) provided. If the values passed to the callable should be treated as equivalent, the callable should return `true`, otherwise it should return `false`.

### Mixed Assertions

To make generic assertions about a variable of any type, call `$assert->mixed($context)`.  The resulting object contains the following methods to actually perform the appropriate assertion.

* `$assert->mixed($context)->isNull();` : Assert that `$context === null`
* `$assert->mixed($context)->isBool();` : Assert that `$context` is of type `bool`
* `$assert->mixed($context)->isInt();` : Assert that `$context` is of type `int`
* `$assert->mixed($context)->isFloat();` : Assert that `$context` is of type `float`
* `$assert->mixed($context)->isString();` : Assert that `$context` is of type `string`
* `$assert->mixed($context)->isArray();` : Assert that `$context` is of type `array`
* `$assert->mixed($context)->isObject();` : Assert that `$context` is of type `object`
* `$assert->mixed($context)->isTypeOf($className)` : Assert that `$context instanceof $className`
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
    public function skipFromMiddleOfTest(Assert $assert) : void
    {
        // This will be run
        $assert->skip();
        // This will not be run and the test will be marked skip in the report.
    }
}
```

How HackUnit loads tests
------------------------
All files inside the base path(s) specified from the command line will be scanned for class definitions using Fred Emmott's
[Definition Finder](https://github.com/fredemmott/definition-finder) library. Those files will then be loaded and reflection is used to determine
which classes are test suites, and which methods perform each task in the suite.

Thanks [Fred!](https://github.com/fredemmott)

Strict mode all the files!
-----------------------------

Well... not quite.

Top level code must use `// partial` mode, so the `bin/hackunit` file is not in strict mode.  The rest of the project is, with one exception.
Test suite files must be dynamically loaded after being scanned for test suites.  The only way I can see to perform this dynamic inclusion is to use `include_once` inside
of a class method, which is disallowed in strict mode.  This one exception is marked with a `/* HH_FIXME */` comment, which disables the type checker for that
one line.

Running HackUnit's tests
------------------------
HackUnit is tested with HackUnit. From the project directory run:

```
hhvm /path/to/composer.phar test
```
