<?hh //strict
namespace HackUnit\Runner;

class Runner<TLoader>
{
    protected TLoader $loader;

    public function __construct(protected Options $options, (function(Options): TLoader) $factory)
    {
        $this->loader = $factory($options);
    }

    public function getLoader(): TLoader
    {
        return $this->loader;
    }
}
