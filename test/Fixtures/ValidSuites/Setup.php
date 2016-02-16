<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

class Setup
{
    <<Setup('suite')>>
    public static function suiteOnly() : void { }

    <<Setup('suite')>>
    public static function nonRequiredParam(int $param = 0) : void { }

    <<Setup('test', 'suite')>>
    public static function both() : void { }

    <<Setup('test')>>
    public function testOnlyExplicit() : void { }

    <<Setup>>
    public function testOnlyImplicit() : void { }
}
