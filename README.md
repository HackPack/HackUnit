HackUnit
========
xUnit testing framework written in Facebook's language, [Hack](http://docs.hhvm.com/manual/en/index.php)

Goal
----
Write a testing framework using Hack's strict mode - with the exception of the runner (which currently must be partial due to a limitation of hack).

Running tests
-------------

```
bin/hackunit --bootstrap test/bootstrap.php --exclude test/fixtures/ test/
```
