<?hh // strict

namespace HackPack\HackUnit\Test;

use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedBasicClass;
use HackPack\HackUnit\Contract\Test\Parser;
use HackPack\HackUnit\Contract\Test\Suite as SuiteInterface;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Event\MalformedSuiteListener;
use HackPack\HackUnit\Util\Trace;
use ReflectionMethod;
use ReflectionClass;

type InvokerWithParams = (function(mixed,array<mixed>):Awaitable<void>);

final class SuiteBuilder
{
    public function __construct(
        private (function(string,string):Parser) $parserBuilder,
        private Vector<MalformedSuiteListener> $malformedListeners = Vector{},
    )
    {
    }

    public function onMalformedSuite(MalformedSuiteListener $listener) : this
    {
        $this->malformedListeners->add($listener);
        return $this;
    }

    public function buildSuites(string $filename) : Traversable<SuiteInterface>
    {
        $suites = Vector{};
        $parserBuilder = $this->parserBuilder;

        foreach (FileParser::FromFile($filename)->getClasses() as $scannedClass) {

            if($this->markedAsSkipped($scannedClass)) {
                $pos = $scannedClass->getPosition();
                $suites->add(new SkippedSuite(
                    $scannedClass->getName(),
                    Trace::buildItem( [
                        'line' => $pos['line'],
                        'class' => $scannedClass->getName(),
                        'file' => $pos['filename'],
                    ]),
                ));
                continue;
            }

            $mirror = $this->reflectClass($scannedClass);
            if($mirror === null) {
                 continue;
            }

            $parser = $parserBuilder($scannedClass->getName(), $scannedClass->getFileName());
            if($parser->tests()->isEmpty()) {
                continue;
            }

            $suite = $this->buildSuite($mirror, $parser);
            if($suite !== null) {
                 $suites->add($suite);
            }
        }

        return $suites;
    }

    private function markedAsSkipped(ScannedBasicClass $class) : bool
    {
        return $class->getAttributes()->containsKey('Skip');
    }

    private function buildInvoker(ReflectionMethod $method) : InvokerWithParams
    {
        return async ($instance, $params) ==> {

            $result = $method->isStatic() ?
                $method->invokeArgs(null, $params) :
                $method->invokeArgs($instance, $params);

            if($method->isAsync()) {
                await $result;
            }

        };
    }

    private function buildSuite(ReflectionClass $classMirror, Parser $parser) : ?SuiteInterface
    {
        $tests = Vector{};
        $errors = $parser->errors()->toVector();

        // Convert method names to ReflectionMethods
        $getMethod = inst_meth($classMirror, 'getMethod');
        $nameToInvoker = $name ==> $this->buildInvoker($getMethod($name));

        $factories = $parser->factories()->map($methodName ==> {
            return async () ==> {

                if($methodName === '__construct') {
                     return $classMirror->newInstance();
                }

                $method = $classMirror->getMethod($methodName);
                $result = $method->invoke(null);
                if($method->isAsync()) {
                    $result = await $result;
                }
                return $result;
            };
        });
        // Set the default constructor if possible
        if(!$factories->containsKey('')) {
            $default = $this->getDefaultFactory($classMirror);
            if($default !== null) {
                $factories = $factories->toMap()->set('', $default);
            }
        }

        $suiteUp = $parser->suiteUp()->map($nameToInvoker);
        $suiteDown = $parser->suiteDown()->map($nameToInvoker);
        $testUp = $parser->testUp()->map($nameToInvoker);
        $testDown = $parser->testDown()->map($nameToInvoker);

        $nullTests = $parser->tests()->map($test ==> {

            if(!$factories->containsKey($test['factory name'])) {

                $msg = $test['factory name'] === '' ?
                    'You must provide a factory method to construct your test suite and annotate it with <<SuiteProvider(\'name\')>>.' :
                    'Suite provider "' . $test['factory name'] . '" not found.';

                $errors->add(new MalformedSuite(
                    Trace::fromReflectionMethod($getMethod($test['method'])),
                    $msg,
                ));

                return null;
            }

            $method = $getMethod($test['method']);
            return shape(
                'factory' => $factories->at($test['factory name']),
                'method' => $this->buildInvoker($method),
                'trace item' => Trace::fromReflectionMethod($method),
                'skip' => $test['skip'],
            );

        });

        if(!$errors->isEmpty()) {
            foreach($errors as $error) {
                $this->emitMalformedSuite($error);
            }
            return null;
        }

        $tests = Vector{};
        foreach($nullTests as $t) {
            if($t !== null) {
                $tests->add($t);
            }
        }
        return new Suite(
            $tests,
            $suiteUp,
            $suiteDown,
            $testUp,
            $testDown,
        );
    }

    private function getDefaultFactory(ReflectionClass $classMirror) : ?(function():Awaitable<mixed>)
    {
        $constructor = $classMirror->getConstructor();

        if(
            $constructor === null ||
            $constructor->getNumberOfRequiredParameters() === 0
        ) {
            return async () ==> $classMirror->newInstance();
        }

        return null;
    }

    private function emitMalformedSuite(MalformedSuite $event) : void
    {
        foreach($this->malformedListeners as $l) {
            $l($event);
        }
    }

    private function load(string $fileName) : void
    {
        // Is there a better way of dynamically including files?
        /* HH_FIXME[1002] */
        require_once($fileName);
    }

    private function reflectClass(ScannedBasicClass $scannedClass) : ?ReflectionClass
    {
        if (!class_exists($scannedClass->getName())) {
            $this->load($scannedClass->getFileName());
        }

        try {
            return new \ReflectionClass($scannedClass->getName());
        } catch (\ReflectionException $e) {
            // Unable to load the file, or the map was wrong?
            // Should we warn the user?
            //echo PHP_EOL . 'Unable to reflect class ' . $scannedClass->getName() . PHP_EOL;
            return null;
        }
    }
}
