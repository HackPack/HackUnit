HackUnit
========

> xUnit written in Hack, for Hack

xUnit testing framework written in Facebook's language, [Hack](http://docs.hhvm.com/manual/en/index.php)

Built against HHVM nightly.

Usage
-----
HackUnit can be run using `bin/hackunit` or if installed via composer - `vendor/bin/hackunit`.

```
bin/hackunit [--hackunit-file="..."] [--exclude="..."] [path]
```

![HackPack demo](https://raw.github.com/HackPack/HackUnit/master/screenshot.png "HackUnit demo")

###Hackunit.php###
The `Hackunit.php` file is included before tests are run. You can specify the path to this file with the `--hackunit-file` switch, or HackUnit will look in the current working directory for one.

###Excluding paths###
Currently the only way to exclude paths from the HackUnit loader is to specify a space delimited list at the command line: `bin/hackunit --exclude="/path/to/ExcludedTest.php /path/to/many/tests/"`

How HackUnit loads tests
------------------------
Currently HackUnit follows a convention for loading tests. Test case files must end in the `Test.php` extension - i.e `NumbersTest.php`.

Only methods beginning with the word `test` will be considered for execution by the loader (although this may change soon)

Notes
-----
The goal of HackUnit is to write a testing framework using Hack's strict mode. HackUnit itself is tested with HackUnit. 

Top level code must use a hack mode of `//partial` and certain functions like `include_once` and `exit` require the use of `//UNSAFE`. These requirements may change as Hack evolves.

hhi files are not included in this repo. If you want to develop locally with all the goodies of type checking, make sure you copy the hhi files into the project directory

```bash
cp -r /usr/share/hhvm/hack/hhi /path/to/hack-unit
```


Running HackUnit's tests
------------------------
From the project directory run this:

```
bin/hackunit --exclude Tests/Fixtures/ Tests/
```
