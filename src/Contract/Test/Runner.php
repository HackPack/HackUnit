<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\ExceptionListener;
use HackPack\HackUnit\Event\PassListener;
use HackPack\HackUnit\Event\RunEndListener;
use HackPack\HackUnit\Event\RunStartListener;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Event\SuiteEndListener;
use HackPack\HackUnit\Event\SuiteStartListener;
use HackPack\HackUnit\Contract\Test\Suite;

interface Runner {
  public function onFailure(FailureListener $l): this;
  public function onUncaughtException(ExceptionListener $l): this;
  public function onPass(PassListener $l): this;
  public function onRunEnd(RunEndListener $l): this;
  public function onRunStart(RunStartListener $l): this;
  public function onSkip(SkipListener $l): this;
  public function onSuccess(SuccessListener $l): this;
  public function onSuiteEnd(SuiteEndListener $l): this;
  public function onSuiteStart(SuiteStartListener $l): this;
  public function run(Vector<Suite> $suites): void;
}
