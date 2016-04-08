<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureEmitter;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SuccessEmitter;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class CallableAssertion
  implements \HackPack\HackUnit\Contract\Assertion\CallableAssertion {
  use FailureEmitter;
  use SuccessEmitter;

  public function __construct(
    private (function(): void) $context,
    Vector<FailureListener> $failureListeners,
    Vector<SuccessListener> $successListeners,
  ) {
    $this->setFailureListeners($failureListeners);
    $this->setSuccessListeners($successListeners);
  }

  public function willThrow(): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      $this->emitSuccess();
      return;
    }
    $this->missingException();
  }

  public function willThrowClass(string $className): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      if (is_a($e, $className)) {
        $this->emitSuccess();
        return;
      }
      $this->wrongClass($className, get_class($e));
      return;
    }
    $this->missingException();
  }

  public function willThrowMessage(string $message): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      if ($e->getMessage() === $message) {
        $this->emitSuccess();
        return;
      }
      $this->wrongMessage($message, $e->getMessage());
      return;
    }
    $this->missingException();
  }

  public function willThrowMessageContaining(string $needle): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      if (strpos($e->getMessage(), $needle) !== false) {
        $this->emitSuccess();
        return;
      }
      $this->messageDoesNotContain($needle, $e->getMessage());
      return;
    }
    $this->missingException();
  }

  public function willThrowClassWithMessage(
    string $className,
    string $message,
  ): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      if ($e->getMessage() !== $message) {
        $this->wrongMessage($message, $e->getMessage());
        return;
      }
      if (!is_a($e, $className)) {
        $this->wrongClass($className, get_class($e));
        return;
      }
      $this->emitSuccess();
      return;
    }
    $this->missingException();
  }

  public function willThrowClassWithMessageContaining(
    string $className,
    string $needle,
  ): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      if (strpos($e->getMessage(), $needle) === false) {
        $this->messageDoesNotContain($needle, $e->getMessage());
        return;
      }
      if (!is_a($e, $className)) {
        $this->wrongClass($className, get_class($e));
        return;
      }
      $this->emitSuccess();
      return;
    }
    $this->missingException();
  }

  public function willNotThrow(): void {
    try {
      $c = $this->context;
      $c();
    } catch (\Exception $e) {
      $this->emitFailure(
        Failure::fromCallStack(
          implode(
            PHP_EOL,
            [
              'Unexpected exception thrown.',
              get_class($e),
              $e->getMessage(),
            ],
          ),
        ),
      );
      return;
    }
    $this->emitSuccess();
  }

  private function missingException(): void {
    $this->emitFailure(
      Failure::fromCallStack('Expected exception to be thrown.'),
    );
  }

  private function wrongClass(string $expected, string $actual): void {
    $this->emitFailure(
      Failure::fromCallStack(
        'Expected exception of type '.
        $expected.
        ' but '.
        $actual.
        ' was thrown.',
      ),
    );
  }

  private function wrongMessage(string $expected, string $actual): void {
    $this->emitFailure(
      Failure::fromCallStack(
        implode(
          PHP_EOL,
          [
            'Expected exception message:',
            $expected,
            'Actual message:',
            $actual,
          ],
        ),
      ),
    );
  }

  private function messageDoesNotContain(
    string $expected,
    string $actual,
  ): void {
    $this->emitFailure(
      Failure::fromCallStack(
        implode(
          PHP_EOL,
          [
            'Expected exception message to contain:',
            $expected,
            'Actual message:',
            $actual,
          ],
        ),
      ),
    );
  }
}
