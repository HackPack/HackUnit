<?hh

// Use this file to run HackUnit's tests

use HackPack\HackUnit\Assert;
use HackPack\HackUnit\HackUnit;
use HackPack\HackUnit\Report\Format\Cli as CliFormat;
use HackPack\HackUnit\Report\Format\JUnit as JUnitFormat;
use HackPack\HackUnit\Report\Status;
use HackPack\HackUnit\Test\Loader;
use HackPack\HackUnit\Test\Parser;
use HackPack\HackUnit\Test\Runner;
use HackPack\HackUnit\Test\SuiteBuilder;

$root = dirname(__DIR__);
require_once $root.'/vendor/autoload.php';

$reportFormatters = Vector {new CliFormat(STDOUT)};

// CircleCI provides this env
// $circleReportDir = getenv('CIRCLE_TEST_REPORTS');
// if (is_string($circleReportDir)) {
//   $reportFormatters->add(JUnitFormat::build($circleReportDir.'/report.xml'));
// }
$status = new Status(STDOUT);

$suiteBuilder = new SuiteBuilder(
  ($className, $fileName) ==> new Parser($className, $fileName),
);

$include = Set {$root.'/test'};
$exclude = Set {
  $root.'/test/self-test.php',
  $root.'/test/self-test.ini',
  $root.'/test/Fixtures',
  $root.'/test/Doubles',
};
$loader = new Loader(
  $class ==> $suiteBuilder->buildSuites($class),
  $include,
  $exclude,
);

$runner = new Runner(class_meth(Assert::class, 'build'));

$hackunit =
  new HackUnit($reportFormatters, $status, $suiteBuilder, $loader, $runner);
$hackunit->run();
