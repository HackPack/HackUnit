<?hh // strict

namespace HackPack\HackUnit;

enum TestGroupAttribute : string as string
{
    start = 'groupLoad';
    setup = 'setUp';
    test = 'test';
    teardown = 'tearDown';
    end = 'groupUnload';
}
