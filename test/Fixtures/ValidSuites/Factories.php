<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

final class ConstructorIsDefaultWithNoParams
{
    public function __construct()
    {
    }

    <<SuiteProvider('named')>>
    public static function factory() : this
    {
        return new static();
    }
}

final class ConstructorIsDefaultWithParams
{
    public function __construct(string $notRequired = '')
    {
    }

    <<SuiteProvider('named')>>
    public static function factory() : this
    {
        return new static();
    }
}

final class ConstructorIsNotDefault
{
    public function __construct()
    {
    }

    <<SuiteProvider>>
    public static function factory() : this
    {
        return new static();
    }
}

<<__ConsistentConstruct>>
abstract class AbstractFactory
{
    <<SuiteProvider>>
    public static function factory() : DerivedFactory
    {
        return new DerivedFactory();
    }
}

class DerivedFactory extends AbstractFactory { }
