<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;;
use HackPack\HackUnit\Test\SuiteParser;
use FredEmmott\DefinitionFinder\FileParser;

class SuiteParserTest
{
    private string $suiteDir;
    private string $suiteNamespace;

    public function __construct()
    {
        $this->suiteDir = dirname(__DIR__) . '/Fixtures/ValidSuites';
        $this->suiteNamespace = 'HackPack\HackUnit\Tests\Fixtures\ValidSuites';
    }

    private function buildParsers(string $path) : Map<string, SuiteParser>
    {
        $parsers = Map{};
        $parsers->addAll(
            FileParser::FromFile($path)
            ->getClasses()
            ->map($class ==> Pair{$class->getName(), new SuiteParser($class)})
        );
        return $parsers;
    }

    private function fixtureFile(string $name) : string
    {
        return $this->suiteDir . '/' . ltrim($name, '/');
    }

    private function suiteName(string $name) : string
    {
         return $this->suiteNamespace . '\\' . $name;
    }

    <<Test>>
    public function factoryParsing(Assert $assert) : void
    {
        $factoryListsByClassName = $this
            ->buildParsers($this->fixtureFile('Factories.php'))
            ->map($p ==> $p->factories())
        ;

        $factoryList = $factoryListsByClassName->at(
            $this->suiteName('ConstructorIsDefaultWithNoParams')
        );

        $assert->bool($factoryList->containsKey(''))->is(true);
        $assert->bool($factoryList->containsKey('named'))->is(true);
        $assert->string($factoryList->at(''))->is('__construct');
        $assert->string($factoryList->at('named'))->is('factory');

        $factoryList = $factoryListsByClassName->at(
            $this->suiteName('ConstructorIsDefaultWithParams')
        );

        $assert->bool($factoryList->containsKey(''))->is(true);
        $assert->bool($factoryList->containsKey('named'))->is(true);
        $assert->string($factoryList->at(''))->is('__construct');
        $assert->string($factoryList->at('named'))->is('factory');

        $factoryList = $factoryListsByClassName->at(
            $this->suiteName('ConstructorIsNotDefault')
        );

        $assert->bool($factoryList->containsKey(''))->is(true);
        $assert->string($factoryList->at(''))->is('factory');
    }
}
