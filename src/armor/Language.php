<?PHP

namespace Knight\armor;

use Knight\Configuration;

use Knight\armor\Navigator;

class Language
{
    use Configuration;

    const CONFIGURATION_DEFAULT_SPEECH = 0x190;

    const SHASH_ESCAPE = '#slash#';

    const USER_DEFINED = 'user';

    public static $speech;

    public static function setSpeech(?string $speech) : void
    {
        $instance = static::instance();
        if (null !== $speech) $instance::$speech = $speech;
    }

    public static function getSpeech() :? string
    {
        $instance = static::instance();
        return $instance::$speech;
    }

    public static function dictionary(string $filename) : bool
    {
        static $included;
        if (null === $included) $included = [];

        $path_match = ['dirname', 'filename'];
        $path_match = array_fill_keys($path_match, null);

        $path = pathinfo($filename);
        $path = array_intersect_key($path, $path_match);
        $path = implode(DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $path = $path . '.language.php';

        if (in_array($path, $included)) return true;

        if (!file_exists($path)) return false;
        array_push($included, $path);

        require_once $path;

        return true;
    }

    public static function getTextsNamespaceName(string $filter = '') : array
    {
        $defined = get_defined_constants(true);
        $defined = array_key_exists(static::USER_DEFINED, $defined) ? $defined[static::USER_DEFINED] : [];
        $defined_filter_length = strlen($filter) + 1;
        $filter = str_replace('\\', '\\\\', $filter);
        $defined_namespace_regex = '/^language' . '\\\\' . '\w{2}' . '\\\\' . $filter . '\.*/';
        $defined = array_keys($defined);
        $defined = array_filter($defined, function (string $namespace) use ($defined_namespace_regex) {
            return preg_match($defined_namespace_regex, $namespace);
        });

        $response = [];
        foreach ($defined as $namespace) {
            $string_namespace = explode('\\', $namespace, 3);
            if (!array_key_exists(2, $string_namespace)) continue;

            $string_namespace = array_slice($string_namespace, 2);
            $string_namespace = reset($string_namespace);
            $string_namespace_translated = static::translate($string_namespace);
            $string_namespace = substr($string_namespace, $defined_filter_length);
            $string_namespace_exploded = explode('\\', $string_namespace);
            $string_namespace_key = array_pop($string_namespace_exploded);

            $matrioska = &$response;
            foreach ($string_namespace_exploded as $part){
                if (!array_key_exists($part, $matrioska)) $matrioska[$part] = [];
                $matrioska = &$matrioska[$part];
            }

            if (!is_array($matrioska)
                || array_key_exists($string_namespace_key, $matrioska)) continue;

            $matrioska[$string_namespace_key] = $string_namespace_translated;
        }
        return $response;
    }

    public static function translate(string $text, string ...$replace_attribute) : string
    {
        $instance = static::instance();
        $language = $instance::getSpeech();
        $language_speech_default = $instance::getDefaultSpeech();
        for ($item = 0; $item < 2; $item++) {
            $language_search = $item === 0 ? $language : $language_speech_default;
            if (null === $language_search) continue;

            $constant = 'language' . '\\' . $language_search . '\\' . $text;
            if (!defined($constant)) continue;

            $text = constant($constant);
            break;
        }

        if (!!$replace_attribute) {
            foreach ($replace_attribute as &$attribute) $attribute = str_replace($instance::SHASH_ESCAPE, '/', $attribute);
            unset($attribute);
            $text = preg_replace_callback('/\$(\d+)/', function ($match) use ($replace_attribute) {
                return array_key_exists($match[1], $replace_attribute) ? $replace_attribute[$match[1]] : $match[0];
            }, $text);
        }

        return $text;
    }

    public static function array(array $input_associative) :? string
    {
        $instance = static::instance();
        $language = $instance::getSpeech();
        $language_speech_default = $instance::getDefaultSpeech();
        for ($item = 0; $item < 2; $item++) {
            $language_search = $item === 0 ? $language : $language_speech_default;
            if (null === $language_search) continue;

            if (array_key_exists($language_search, $input_associative)) return $input_associative[$language_search];
        }
        return null;
    }

    protected static function getDefaultSpeech() : string
    {
        return static::getConfiguration(static::CONFIGURATION_DEFAULT_SPEECH, true);
    }

    final protected static function instance() : self
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
            $instance->shouldSpeechBrowser();
        }
        return $instance;
    }

    final protected function shouldSpeechBrowser() : void
    {
        if (!array_key_exists(Navigator::HTTP_ACCEPT_LANGUAGE, $_SERVER)) return;

        $language = substr($_SERVER[Navigator::HTTP_ACCEPT_LANGUAGE], 0, 2);
        $language = mb_strtolower($language);

        static::setSpeech($language);
    }
}