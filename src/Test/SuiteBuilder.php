<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Test\SuiteParser;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Event\MalformedSuiteListener;
use HackPack\HackUnit\Util\Trace;

final class SuiteBuilder<Tparser as SuiteParser>
{
    public function __construct(
        private classname<Tparser> $parserClass,
        private Vector<MalformedSuiteListener> $malformedListeners = Vector{},
    )
    {
    }

    public function onMalformedSuite(MalformedSuiteListener $listener) : this
    {
        $this->malformedListeners->add($listener);
        return $this;
    }

    public function buildSuite(\ReflectionClass $classMirror) : ?Suite
    {
        $className = $classMirror->getName();
        $classFile = $classMirror->getFileName();
        if( ! is_string($className) || ! is_string($classFile)) {
            // Reflector unable to figure the class name/file
            // This indicates something is wrong, so conservatively do not
            // load this suite
            return null;
        }

        $parserClass = $this->parserClass;
        $parser = new $parserClass($classMirror);

        $errors = $parser->errors()->toVector();

        $tests = Vector{};
        $factories = $parser->factories();
        foreach($parser->tests() as $test) {
            $factory = $factories->get($test['factory name']);
            if($factory === null) {
                $msg = $test['factory name'] === '' ?
                    'You must provide a factory method to construct your test suite and annotate it with <<SuiteProvider(\'name\')>>.' :
                    'Suite provider "' . $test['factory name'] . '" not found.';
                $errors->add(new MalformedSuite(
                    Trace::fromReflectionMethod($test['method']),
                    $msg,
                ));
                continue;
            }
            $tests->add(shape(
                'factory' => $factories->at($test['factory name']),
                'method' => $test['method'],
                'skip' => $test['skip'],
            ));
        }

        if(!$errors->isEmpty()) {
            foreach($errors as $error) {
                $this->emitMalformedSuite($error);
            }
            return null;
        }

        return new Suite(
            $tests,
            $parser->suiteUp(),
            $parser->suiteDown(),
            $parser->testUp(),
            $parser->testDown(),
        );
    }


    private function emitMalformedSuite(MalformedSuite $event) : void
    {
        foreach($this->malformedListeners as $l) {
            $l($event);
        }
    }
}
