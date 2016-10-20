<?hh // strict

namespace HackPack\HackUnit\Report;

use HackPack\HackUnit\Event\BuildFailure;
use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Pass;
use HackPack\HackUnit\Util\Options;

class Status {

  public function __construct(private resource $out) {}

  public function handleRunStart(): void {
    $out = [];
    exec('hhvm --version', $out);
    $hhvmVersion = $out[0];
    fwrite(
      $this->out,
      sprintf(
        "\nHackUnit by HackPack version %s\nHHVM version %s\n",
        Options::VERSION,
        $hhvmVersion,
      ),
    );
  }

  public function handlePass(Pass $event): void {
    fwrite($this->out, '.');
  }

  public function handleFailure(Failure $event): void {
    fwrite($this->out, 'F');
  }

  public function handleSkip(Skip $event): void {
    fwrite($this->out, 'S');
  }

  public function handleBuildFailure(BuildFailure $event): void {
    sprintf(
      "\n~*~*~*~ Build Failure ~*~*~*~\nFile: %s\nMessage: %s\n",
      $event->filePath(),
      $event->exception()->getMessage(),
    )
      |> fwrite($this->out, $$);
  }
}
