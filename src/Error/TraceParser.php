<?hh //strict
namespace HackPack\HackUnit\Error;

type Origin = shape(
    'test method' => string,
    'message' => string,
    'test location' => Location,
    'top location' => Location,
);

type Location = shape(
    'file' => string,
    'line' => int
);

class TraceParser
{
    public function __construct(protected \Exception $exception)
    {
    }

    public function getOrigin(): Origin
    {
        $testData = $this->findTestData();

        return shape(
            'message' => $this->exception->getMessage(),
            'top location' => shape(
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
            ),
            'test location' => $testData[0],
            'test method' => $testData[1],
        );
    }

    private function findTestData(): (Location, string)
    {
        $out = tuple(
            shape(
                'file' => 'Unknown File',
                'line' => -1,
            ),
            'Unknown Test'
        );

        $expectationClassNames = Set{
            \HackPack\HackUnit\Core\Expectation::class,
            \HackPack\HackUnit\Core\CallableExpectation::class
        };

        foreach($this->exception->getTrace() as $traceItem) {

                $item = new Map($traceItem);
                //$item->removeKey('args');
                $filename = $item->get('file');
                $line = $item->get('line');
                $className = $item->get('class');
                $functionName = $item->get('function');

                // Ensure we have the requisite data
                if(
                    $filename === null || $filename === '' ||
                    $className === null || $className === '' ||
                    $functionName === null || $functionName === ''
                ) {
                    continue;
                }

                // Look for the invocation of an expectation
                if($expectationClassNames->contains($className)) {
                    $out[0] = shape(
                        'file' => $filename,
                        'line' => (int)$line,
                    );
                }

                // Look for TestCase::run
                if(
                    $className === \HackPack\HackUnit\Core\TestCase::class &&
                    $functionName === 'run'
                ) {
                    $args = Vector::fromItems($item->get('args'));
                    // The actual test method is passed as a ReflectionMethod to TestCase::run
                    $testMethod = $args->get(1);
                    if(!($testMethod instanceof \ReflectionMethod)) {
                        // WTF?!
                        continue;
                    }

                    // Found the test method
                    $out[1] = $testMethod->getDeclaringClass()->getName() . '::' . $testMethod->getName();
                }
        }

        return $out;
    }
}
