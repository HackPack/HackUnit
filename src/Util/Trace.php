<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Contract\Assertion\Assertion;

type TraceItem = shape(
    'file' => ?string,
    'line' => ?int,
    'function' => ?string,
    'class' => ?string
);

class Trace
{
    public static function fromReflectionMethod(\ReflectionMethod $methodMirror) : TraceItem
    {
        return self::buildItem([
            'line' => $methodMirror->getStartLine(),
            'function' => $methodMirror->name,
            'class' => $methodMirror->class,
            'file' => $methodMirror->getFileName(),
        ]);
    }

    public static function generate() : Vector<TraceItem>
    {
        return self::convert(
            (new Vector(debug_backtrace()))
            ->removeKey(0)
            ->toArray()
        );
    }

    public static function convert(array<array<string,mixed>> $trace) : Vector<TraceItem>
    {
        return (new Vector($trace))->map($t ==> self::buildItem($t));
    }

    public static function buildItem(array<string,mixed> $item) : TraceItem
    {
        return shape(
            'line' => array_key_exists('line', $item) && is_int($item['line']) ? (int)$item['line'] : null,
            'function' => array_key_exists('function', $item) && is_string($item['function']) ? (string)$item['function'] : null,
            'class' => array_key_exists('class', $item) && is_string($item['class']) ? (string)$item['class'] : null,
            'file' => array_key_exists('file', $item) && is_string($item['file']) ? (string)$item['file'] : null,
        );
    }

    public static function findAssertionCallFromStack(Vector<TraceItem> $trace) : TraceItem
    {
        foreach($trace as $idx => $item) {
            // Always switching classes when making an assertion
            if(
                (
                    $trace->containsKey($idx + 1) &&
                    $item['class'] === $trace->at($idx + 1)['class']
                )
                ||
                $item['class'] === null
            ) {
                continue;
            }

            // See if current item implements the assertion interface
            $implements = class_implements($item['class']);
            if(is_array($implements) && array_key_exists(Assertion::class, $implements)) {
                // Next item in the stack was the actual caller
                if($trace->containsKey($idx + 1)) {
                    return shape(
                        'line' => $trace->at($idx)['line'],
                        'function' => $trace->at($idx + 1)['function'],
                        'class' => $trace->at($idx + 1)['class'],
                        'file' => $trace->at($idx)['file'],
                    );
                }
                return self::emptyTraceItem();
            }
        }
        return self::emptyTraceItem();
    }

    public static function findTestMethodFromStack(Vector<TraceItem> $trace) : TraceItem
    {
        foreach($trace as $idx => $item) {
            // Make sure we know the class and method names
            if($item['class'] === null || $item['function'] === null) {
                continue;
            }
            try{
                $class = new \ReflectionClass($item['class']);
                $method = $class->getMethod((string)$item['function']);
            } catch (\ReflectionException $e) {
                // Either class or function were not defined
                continue;
            }

            // See if the class is a suite and the method is a test
            $classAttributes = new Map($class->getAttributes());
            $methodAttributes = new Map($method->getAttributes());

            if($methodAttributes->get('Test') !== null) {
                 // Found the marked test method
                return shape(
                        'line' => $trace->at($idx - 1)['line'],
                        'function' => $trace->at($idx)['function'],
                        'class' => $trace->at($idx)['class'],
                        'file' => $trace->at($idx - 1)['file'],
                );
            }
        }

        return self::emptyTraceItem();
    }

    public static function findAssertionCall() : TraceItem
    {
        return self::findAssertionCallFromStack(self::generate());
    }

    public static function findTestMethod() : TraceItem
    {
         return self::findTestMethodFromStack(self::generate());
    }

    private static function emptyTraceItem() : TraceItem
    {
        return shape(
            'line' => null,
            'function' => null,
            'class' => null,
            'file' => null,
        );
    }
}
