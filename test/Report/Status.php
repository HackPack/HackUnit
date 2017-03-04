<?hh // strict

namespace HackPack\HackUnit\Tests\Report;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Pass;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Report\Status;

final class StatusTest {
  public function __construct(private resource $out, private Status $status) {}

  <<SuiteProvider>>
  public static function provider(): this {
    $out = fopen('php://memory', 'w+');
    return new static($out, new Status($out));
  }

  public function __destruct() {
    fclose($this->out);
  }

  <<Test>>
  public function passShowsDot(Assert $assert): void {
    $this->status->handlePass(Pass::fromCallStack());
    $this->assertOutput($assert, '.');
  }

  <<Test>>
  public function failureShowsF(Assert $assert): void {
    $this->status->handleFailure(Failure::fromCallStack('testing'));
    $this->assertOutput($assert, 'F');
  }

  <<Test>>
  public function skipShowsS(Assert $assert): void {
    $this->status->handleSkip(Skip::fromCallStack('testing'));
    $this->assertOutput($assert, 'S');
  }

  private function assertOutput(Assert $assert, string $expected): void {
    rewind($this->out);
    $actual = stream_get_contents($this->out);
    $assert->string($actual)->is($expected);
  }
}
