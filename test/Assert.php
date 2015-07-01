<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Contract\Assert as iAssert;
use HackPack\HackUnit\Assert;
use HackPack\HackUnit\Event\Skip;

<<TestSuite>>
class AssertTest
{
    private Vector<Skip> $skips = Vector{};

    <<Setup>>
    public function clearEvents() : void
    {
        $this->skips->clear();
    }

    <<Test>>
    public function skipIdentifiesCaller(iAssert $assert) : void
    {
        $sut = new Assert(
            Vector{},
            Vector{
                $skip ==> {$this->skips->add($skip);}
            },
            Vector{},
        );
        $line = __LINE__ + 1;
        $sut->skip('testing');

        $assert->int($this->skips->count())->eq(1);
        $skip = $this->skips->at(0);
        $skipLine = $skip->assertionLine();
        $function = $skip->testMethod();
        $class = $skip->testClass();
        $file = $skip->testFile();

        $assert->string($skip->message())->is('testing');

        $assert->mixed($skipLine)->not()->isNull();
        invariant($skipLine !== null, '');
        $assert->int($skipLine)->eq($line);

        $assert->mixed($function)->not()->isNull();
        invariant($function !== null, '');
        $assert->string($function)->is(__FUNCTION__);

        $assert->mixed($class)->not()->isNull();
        invariant($class !== null, '');
        $assert->string($class)->is(__CLASS__);

        $assert->mixed($file)->not()->isNull();
        invariant($file !== null, '');
        $assert->string($file)->is(__FILE__);
    }
}
