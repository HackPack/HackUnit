<?hh //strict
abstract class TestCase
{
    public function __construct(protected string $name)
    {
    }

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function run(): void
    {
        $this->setUp();
        $class = get_class($this);
        hphp_invoke_method($this, $class, $this->name, []);
        $this->tearDown();
    }
}
