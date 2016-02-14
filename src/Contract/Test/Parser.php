<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Event\MalformedSuite;

interface Parser
{
    /**
     * Return a mapping of factory aliases to public static methods
     */
    public function factories() : \ConstMap<string, string>;

    /**
     * Return a list of public static methods to run before the suite starts
     */
    public function suiteUp() : \ConstVector<string>;

    /**
     * Return a list of public static methods to run after the suite ends
     */
    public function suiteDown() : \ConstVector<string>;

    /**
     * Return a list of public methods (static or not) to run just before each test
     */
    public function testUp() : \ConstVector<string>;

    /**
     * Return a list of public methods (static or not) to run just after each test
     */
    public function testDown() : \ConstVector<string>;

    /**
     * Return enough data to define a test method
     *
     * The factory name SHOULD be contained in the list of factories returned above,
     * but this is not required.
     */
    public function tests() : \ConstVector<
        shape(
            'factory name' => string,
            'method' => string,
            'skip' => bool,
        )
    >;

    /**
     * List of malformed suite events to broadcast
     */
    public function errors() : \ConstVector<MalformedSuite>;
}
