<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

final class DuplicateFactories
{
    <<SuiteProvider>>
    public static function one() : this { return new static(); }

    <<SuiteProvider>>
    public static function two() : this { return new static(); }
}

final class FactoryParams
{
    <<SuiteProvider>>
    public static function factory(int $required) : this { return new static(); }
}

final class NonStaticFactory
{
    <<SuiteProvider>>
    public function factory() : this { return new static(); }
}

final class FactoryReturnType
{
    <<SuiteProvider>>
    public static function factory() : int { return 0; }
}

abstract class AbstractFactory
{
    <<SuiteProvider>>
    public static function factory() : AbstractFactory { return new InvalidDerivedFactory(); }
}

class InvalidDerivedFactory extends AbstractFactory { }
