<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Event\MalformedSuite;
use ReflectionClass;
use ReflectionMethod;

interface SuiteParser
{
    public function __construct(ReflectionClass $classMirror);
    public function factories() : \ConstMap<string, (function():mixed)>;
    public function suiteUp() : \ConstVector<ReflectionMethod>;
    public function suiteDown() : \ConstVector<ReflectionMethod>;
    public function testUp() : \ConstVector<ReflectionMethod>;
    public function testDown() : \ConstVector<ReflectionMethod>;
    public function tests() : \ConstVector<
        shape(
            'factory name' => string,
            'method' => ReflectionMethod,
            'skip' => bool,
        )
    >;
    public function errors() : \ConstVector<MalformedSuite>;
}
