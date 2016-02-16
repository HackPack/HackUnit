<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

class TestUpParams
{
    <<Setup>>
    public function invalid(int $requiredParam) : void { }
}

class TestUpConstructor
{
    <<Setup>>
    public function __construct() { }
}

class TestDownParams
{
    <<TearDown>>
    public function invalid(int $requiredParam) : void { }
}

class TestDownConstructor
{
    <<TearDown>>
    public function __construct() { }
}
