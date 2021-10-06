<?PHP

namespace Knight\armor;

class Request
{
    const ISJSON = 0x1;

    protected function __construct() {}

    public static function JSONDecode(string $string)
    {
        $decode = json_decode($string, JSON_OBJECT_AS_ARRAY);
        if (null === $decode
            && json_last_error() !== JSON_ERROR_NONE) throw new CustomException('developer/request/json/decode');

        return $decode;
    }

    public static function filter(array $input) : array
    {
        $callback = array(static::class, 'callback');
        $response = array();
        foreach ($input as $key => $data) $response[$key] = is_array($data) ? static::filter($data) : $data;
        $response = array_filter($response, $callback);
        return $response;
    }

    public static function get(string $selector = null)
    {
        if ($selector === null) return (object)$_GET;
        if (array_key_exists($selector, $_GET)) return $_GET[$selector];
        return null;
    }

    public static function post(string $selector = null)
    {
        if ($selector === null) return (object)$_POST;
        if (array_key_exists($selector, $_POST)) return $_POST[$selector];
        return null;
    }

    public static function input(int $flags = 0, string $selector = null)
    {
        $input = file_get_contents('php://input');
        if (false === (bool)($flags & static::ISJSON)) return $input;
        if (is_string($input)) {
            $input = static::JSONDecode($input);
            if ($selector === null) return (object)$input;
            if (array_key_exists($selector, $input))
                return $input[$selector];
        }
        return null;
    }

    public static function header(string $selector = null)
    {
        $header_keys = array_keys($_SERVER);
        $header_keys = preg_grep('/^http/i', $header_keys);
        $header_keys = array_flip($header_keys);

        $header = array_intersect_key($_SERVER, $header_keys);
        if ($selector === null) return (object)$header;

        $selector = preg_replace('/\W/', chr(95), $selector);
        $selector = 'http' . chr(95) . $selector;
        $selector = strtoupper($selector);
        if (array_key_exists($selector, $header)) return $header[$selector];

        return null;
    }

    protected static function callback($item) : bool
    {
        return !is_null($item) && (!is_array($item)
            || !empty($item));
    }
}