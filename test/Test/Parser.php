<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Parser;
use FredEmmott\DefinitionFinder\TreeParser;

class SuiteParserTest
{
    private static string $suiteNamespace = 'HackPack\HackUnit\Tests\Fixtures\ValidSuites\\';

    private static Map<string, Parser> $validParsersBySuiteName = Map{};
    private static Map<string, Parser> $invalidParsersBySuiteName = Map{};

    <<Setup('suite')>>
    private static function buildParsers() : void
    {
        self::$validParsersBySuiteName->addAll(
            TreeParser::FromPath(dirname(__DIR__) . '/Fixtures/ValidSuites/')
            ->getClasses()
            ->map($class ==> Pair{$class->getName(), new Parser($class->getName(), $class->getFileName())})
        );

        self::$invalidParsersBySuiteName->addAll(
            TreeParser::FromPath(dirname(__DIR__) . '/Fixtures/InvalidSuites/')
            ->getClasses()
            ->map($class ==> Pair{$class->getName(), new Parser($class->getName(), $class->getFileName())})
        );
    }

    private function parserFromSuiteName(string $name, Assert $assert) : Parser
    {
        $fullName = self::$suiteNamespace . $name;

        if(self::$validParsersBySuiteName->containsKey($fullName)) {
            return self::$validParsersBySuiteName->at($fullName);
        }

        if(self::$invalidParsersBySuiteName->containsKey($fullName)) {
            return self::$invalidParsersBySuiteName->at($fullName);
        }

        throw new \RuntimeException('Unable to locate suite ' . $fullName);
    }

    <<Test>>
    public function validSuitesParseWithoutError(Assert $assert) : void
    {
        foreach(self::$validParsersBySuiteName as $parser) {
            $assert->bool($parser->errors()->isEmpty())->is(true);
        }
    }

    <<Test>>
    public function invalidSuitesParseWithError(Assert $assert) : void
    {
        foreach(self::$invalidParsersBySuiteName as $parser) {
            $assert->bool($parser->errors()->isEmpty())->is(false);
        }
    }

    <<Test>>
    public function factoryParsing(Assert $assert) : void
    {
        $factoryList = $this
            ->parserFromSuiteName('ConstructorIsDefaultWithNoParams', $assert)
            ->factories()
            ;

        $assert->int($factoryList->count())->eq(2);
        $assert->bool($factoryList->containsKey(''))->is(true);
        $assert->bool($factoryList->containsKey('named'))->is(true);
        $assert->string($factoryList->at(''))->is('__construct');
        $assert->string($factoryList->at('named'))->is('factory');

        $factoryList = $this
            ->parserFromSuiteName('ConstructorIsDefaultWithParams', $assert)
            ->factories()
            ;

        $assert->int($factoryList->count())->eq(2);
        $assert->bool($factoryList->containsKey(''))->is(true);
        $assert->bool($factoryList->containsKey('named'))->is(true);
        $assert->string($factoryList->at(''))->is('__construct');
        $assert->string($factoryList->at('named'))->is('factory');

        $factoryList = $this
            ->parserFromSuiteName('ConstructorIsNotDefault', $assert)
            ->factories()
            ;

        $assert->bool($factoryList->containsKey(''))->is(true);
        $assert->string($factoryList->at(''))->is('factory');
    }

    <<Test>>
    public function setupParsing(Assert $assert) : void
    {
        $this->updownParsing('Setup', $assert);
    }

    <<Test>>
    public function teardownParsing(Assert $assert) : void
    {
        $this->updownParsing('TearDown', $assert);
    }

    private function updownParsing(string $type, Assert $assert) : void
    {
        $parser = $this->parserFromSuiteName($type, $assert);

        $expectedSuiteUp = Vector{
            'suiteOnly',
                'both',
        };

        $suiteUp = $type === 'Setup' ?
            $parser->suiteUp() :
            $parser->suiteDown();

        $extraSuiteUp = array_diff($suiteUp, $expectedSuiteUp);
        $missingSuiteUp = array_diff($expectedSuiteUp, $suiteUp);

        $assert->int(count($extraSuiteUp))->eq(0);
        $assert->int(count($missingSuiteUp))->eq(0);

        $expectedTestUp = Vector{
            'both',
                'testOnlyExplicit',
                'testOnlyImplicit'
        };
        $testUp = $type === 'Setup' ?
            $parser->testUp() :
            $parser->testDown();

        $extraTestUp = array_diff($testUp, $expectedTestUp);
        $missingTestUp = array_diff($expectedTestUp, $testUp);

        $assert->int(count($extraTestUp))->eq(0);
        $assert->int(count($missingTestUp))->eq(0);
    }

    <<Test>>
    public function testParsing(Assert $assert) : void
    {
        $parser = $this->parserFromSuiteName('Test', $assert);

        $tests = Map::fromItems(
            $parser->tests()
            ->map($test ==> Pair{$test['method'], $test})
        );

        $testNames = $tests->keys();
        $expectedTestNames = Vector{
            'defaultProvider',
            'namedProvider',
            'staticTest',
            'skippedTest',
        };

        $missingTests = array_diff($expectedTestNames, $testNames);
        $extraTests = array_diff($testNames, $expectedTestNames);
        $assert->int(count($missingTests))->eq(0);
        $assert->int(count($extraTests))->eq(0);

        $defaultProvider = $tests->at('defaultProvider');
        $assert->string($defaultProvider['factory name'])->is('');
        $assert->bool($defaultProvider['skip'])->is(false);

        $defaultProvider = $tests->at('namedProvider');
        $assert->string($defaultProvider['factory name'])->is('named');
        $assert->bool($defaultProvider['skip'])->is(false);

        $defaultProvider = $tests->at('skippedTest');
        $assert->string($defaultProvider['factory name'])->is('');
        $assert->bool($defaultProvider['skip'])->is(true);
    }

    <<Test>>
    public function inheritedTests(Assert $assert) : void
    {
        $parser = $this->parserFromSuiteName('BaseSuite', $assert);

        $tests = Map::fromItems(
            $parser->tests()
            ->map($test ==> Pair{$test['method'], $test})
        );

        $testNames = $tests->keys();
        $expectedTestNames = Vector{
            'testInsideBaseSuite',
            'testInsideAbstractSuite',
        };

        $missingTests = array_diff($expectedTestNames, $testNames);
        $extraTests = array_diff($testNames, $expectedTestNames);
        $assert->int(count($missingTests))->eq(0);
        $assert->int(count($extraTests))->eq(0);

        $parser = $this->parserFromSuiteName('HasExternalSuite', $assert);

        $tests = Map::fromItems(
            $parser->tests()
            ->map($test ==> Pair{$test['method'], $test})
        );

        $testNames = $tests->keys();
        $expectedTestNames = Vector{
            'testInsideBaseSuite',
            'testInsideAbstractSuite',
            'testInsideTrait'
        };

        $missingTests = array_diff($expectedTestNames, $testNames);
        $extraTests = array_diff($testNames, $expectedTestNames);
        $assert->int(count($missingTests))->eq(0);
        $assert->int(count($extraTests))->eq(0);
    }

    <<Test>>
    public function abstractClassesDoNotHaveTests(Assert $assert) : void
    {
        $parser = $this->parserFromSuiteName('AbstractSuite', $assert);

        $assert->int($parser->tests()->count())->eq(0);
    }
}
