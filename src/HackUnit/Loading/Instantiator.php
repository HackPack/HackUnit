<?hh //strict
namespace HackUnit\Loading;

class Instantiator
{
    const int T_NAMESPACE = 377;
    const int T_STRING = 307;
    const int T_CLASS = 353;

    public function fromFile<T>(string $classPath, array<mixed> $args): T
    {
        $fp = fopen($classPath, 'r');
        $namespace = $class = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;
            $buffer .= (string) fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (; $i < count($tokens); $i++) {

                if ($tokens[$i][0] === Instantiator::T_NAMESPACE) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === Instantiator::T_STRING) {
                            $namespace .= '\\' . (string) $tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === Instantiator::T_CLASS) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i +2][1];
                        }
                    }
                }
            }
        }
        $className = $namespace . '\\' . $class;
        return hphp_create_object($className, $args);
    }
}
