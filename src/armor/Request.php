<?PHP

namespace Knight\armor;

/* The Request class is a class that is used to get data from the client */

class Request
{
    const ISJSON = 0x1;

    protected function __construct() {}

    /**
     * It takes a string and attempts to decode it as JSON.
     * 
     * @param string string The string to be decoded.
     * @param int flags The flags parameter is a bitmask of JSON decode options.
     * 
     * @return The decoded JSON object.
     */
    
    public static function JSONDecode(string $string, int $flags = 0)
    {
        $decode = json_decode($string);
        if (null === $decode
            && json_last_error() !== JSON_ERROR_NONE) throw new CustomException('developer/request/json/decode');

        return $decode;
    }

    /**
     * This function filters out all the empty values from an array
     * 
     * @param array input The array to be filtered.
     * 
     * @return An array of filtered data.
     */
    
    public static function filter(array $input) : array
    {
        $callback = array(static::class, 'callback');
        $response = array();
        foreach ($input as $key => $data) $response[$key] = is_array($data) ? static::filter($data) : $data;
        $response = array_filter($response, $callback);
        return $response;
    }

    /**
     * Get the value of a GET parameter
     * 
     * @param string selector The name of the GET parameter to retrieve.
     * 
     * @return An object.
     */
    
    public static function get(string $selector = null)
    {
        if ($selector === null) return (object)$_GET;
        if (array_key_exists($selector, $_GET)) return $_GET[$selector];
        return null;
    }

    /**
     * Returns the value of the POST variable with the given name
     * 
     * @param string selector The name of the form field.
     * 
     * @return An object.
     */
    
    public static function post(string $selector = null)
    {
        if ($selector === null) return (object)$_POST;
        if (array_key_exists($selector, $_POST)) return $_POST[$selector];
        return null;
    }

    /**
     * Reads the input from the PHP server and returns it as a string or an object
     * 
     * @param int flags 
     * @param string selector The key of the value you want to return.
     * 
     * @return The input as an object.
     */
    
    public static function input(int $flags = 0, string $selector = null)
    {
        $input = file_get_contents('php://input');
        if (false === (bool)($flags & static::ISJSON)) return $input;
        if (is_string($input)) {
            $input = static::JSONDecode($input, JSON_OBJECT_AS_ARRAY);
            if ($selector === null) return (object)$input;
            if (array_key_exists($selector, $input))
                return $input[$selector];
        }
        return null;
    }

    /**
     * Returns the HTTP headers from the current request
     * 
     * @param string selector The header you want to retrieve.
     * 
     * @return An object with the header keys as properties and the header values as the property
     * values.
     */
    
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

    /**
     * If the item is not null and is not an empty array, return true. Otherwise, return false
     * 
     * @param item The item to be filtered.
     * 
     * @return The callback function returns a boolean value.
     */
    
    protected static function callback($item) : bool
    {
        return !is_null($item) && (!is_array($item)
            || !empty($item));
    }
}
