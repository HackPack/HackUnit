<?hh //strict
namespace HackUnit\Runner;

class Options
{
    protected ?string $testPath;

    /**
     * @todo Annotate type as "this" when fixed in
     * nightly. Currently broken when using namespaces
     */
    public function setTestPath(string $testPath): Options
    {
        $this->testPath = $testPath;
        return $this;
    }

    public function getTestPath(): ?string
    {
        return $this->testPath;
    }
}
