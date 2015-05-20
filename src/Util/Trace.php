<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Assertion\CallableAssertion;
use HackPack\HackUnit\Assertion\ContextAssertion;

type TraceItem = shape(
    'file' => ?string,
    'line' => ?int,
    'function' => ?string,
    'class' => ?string
);

class Trace
{
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
            if(
                $item['class'] === CallableAssertion::class ||
                $item['class'] === ContextAssertion::class ||
                $item['class'] === AssertionBuilder::class
            ) {
                // Next item in the stack was the actual caller
                if($trace->containsKey($idx + 1)) {
                    return $trace->at($idx + 1);
                }
                return self::emptyTraceItem();
            }
        }
        return self::emptyTraceItem();
    }

    public static function findAssertionCall() : TraceItem
    {
        return self::findAssertionCallFromStack(self::generate());
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
