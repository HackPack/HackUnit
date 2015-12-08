<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

use HackPack\HackUnit\Assert;

class MissingTestAnnotation
{
    public function thisCouldBeATest(Assert $assert) : void
    {
    }
}
