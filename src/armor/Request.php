<?PHP

namespace Knight\armor;

class Request
{
    protected function __construct() {}

    public static function get(string $filter = null)
    {
        if ($filter === null) return (object)$_GET;
        if (array_key_exists($filter, $_GET)) return $_GET[$filter];
        return null;
    }

    public static function post(string $filter = null)
    {
        if ($filter === null) return (object)$_POST;
        if (array_key_exists($filter, $_POST)) return $_POST[$filter];
        return null;
    }

    public static function header(string $filter = null)
    {
        $header_keys = array_keys($_SERVER);
        $header_keys = preg_grep('/^http/i', $header_keys);
        $header_keys = array_flip($header_keys);
        $header = array_intersect_key($_SERVER, $header_keys);
        if ($filter === null) return (object)$header;

        $filter = 'http' . chr(95) . $filter;
        $filter = strtoupper($filter);
        if (array_key_exists($filter, $header)) return $header[$filter];
        return null;
    }
}