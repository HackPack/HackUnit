<?hh // strict

namespace HackPack\HackUnit\Tests\Event;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\Failure;

trait FailureEventTraceAssertions
{
    private function testFailureEvent(
        Assert $assert,
        Failure $event,
        int $assertionLine,
        string $testMethod,
        string $testClass,
        string $testFile,
    ) : void
    {
        $actualLine = $event->assertionLine();
        $actualMethod = $event->testMethod();
        $actualClass = $event->testClass();
        $actualFile = $event->testFile();

        $assert->mixed($actualLine)->isInt();
        invariant(is_int($actualLine), '');

        $assert->mixed($actualMethod)->isString();
        invariant(is_string($actualMethod), '');

        $assert->mixed($actualClass)->isString();
        invariant(is_string($actualClass), '');

        $assert->mixed($actualFile)->isString();
        invariant(is_string($actualFile), '');

        $assert->int($actualLine)->eq($assertionLine);
        $assert->string($actualMethod)->is($testMethod);
        $assert->string($actualClass)->is($testClass);
        $assert->string($actualFile)->is($testFile);
    }
}
