<?hh //strict
abstract class TestCase
{
    public function __construct(protected string $name)
    {
    }

    public function run(): void
    {
        $class = get_class($this);
        hphp_invoke_method($this, $class, $this->name, []);
    }
}
