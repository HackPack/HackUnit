<?hh // strict

namespace HackPack\HackUnit\Test\Fixtures\InvalidSuites;

use HackPack\HackUnit\Assertion\AssertionBuilder;

<<TestSuite>>
class MissingTestAnnotation
{
    public function thisCouldBeATest(AssertionBuilder $assert) : void
    {
        $assert->context('this should not run')->identicalTo('at all');
    }
}
