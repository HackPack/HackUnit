<?hh // strict

namespace HackPack\HackUnit\Util;

type TraceItem = shape(
    'file' => string,
    'line' => int,
    'function' => string,
    'class' => string
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
        return (new Vector($trace))
            ->map($t ==> shape(
                'file' => array_key_exists('file', $t) ? (string)$t['file'] : '',
                'line' => array_key_exists('line', $t) ? (int)$t['line'] : -1,
                'function' => array_key_exists('function', $t) ? (string)$t['function'] : '',
                'class' => array_key_exists('class', $t) ? (string)$t['class'] : '',
            ));
    }
}
