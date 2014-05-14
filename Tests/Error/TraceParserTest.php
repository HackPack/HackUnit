<?hh //strict
namespace HackUnit\Error;

use HackUnit\Core\TestCase;

class TraceParserTest extends TestCase
{
    public function test_getOrigin_should_return_class_method_message_file_and_line(): void
    {
        $parser = $this->getParser();
        $info = $parser->getOrigin();
        $this->expect($info['method'])->toEqual('HackUnit\Error\TraceParserTest::test_getOrigin_should_return_class_method_message_file_and_line');
        $this->expect($info['message'])->toEqual('Failure!');
        $this->expect($info['location'])->toEqual(__FILE__ . ':10');
    }

    protected function getParser(): TraceParser
    {
        $parser = null;
        try {
            throw new \Exception("Failure!");
        } catch (\Exception $e) {
            $parser = new TraceParser($e);
        }
        return $parser;
    }
}
