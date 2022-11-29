<?PHP

namespace Knight\armor;

use Closure;

use Knight\Sso;
use Knight\Configuration;

use Knight\armor\Output;
use Knight\armor\CustomException;

/* The Navigator class is used to determine the current route and to redirect to the appropriate view */

class Navigator
{
    use Configuration;

    const CONFIGURATION_FORCE_IP = 0x1f4;
    const CONFIGURATION_DEPTH = 0x1f6;
    
    const QUERY_STRING = 'QUERY_STRING';
    const REQUEST_URI = 'REQUEST_URI';
    const REMOTE_ADDR = 'REMOTE_ADDR';
    const HTTP_HOST = 'HTTP_HOST';
    const HTTP_CLIENT_IP = 'HTTP_CLIENT_IP';
    const HTTP_X_FORWARDED_FOR = 'HTTP_X_FORWARDED_FOR';
    const HTTP_X_OVERRIDE_IP = 'HTTP_X_OVERRIDE_IP';
    const HTTP_X_OVERRIDE_IP_ENABLE = 0x1;
    const HTTP_USER_AGENT = 'HTTP_USER_AGENT';
    const HTTP_ACCEPT_LANGUAGE = 'HTTP_ACCEPT_LANGUAGE';
    const HTTP_ORIGIN = 'HTTP_ORIGIN';

    const SEPARATOR = '/';

    const REGULAR_EXPRESSION_IP_MATCH = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/m';

    const DEPTH_DEFAULT = 3;

    const RETURN_URL = 'return_url';

    final protected function __construct() {}

    /**
     * Get the URI from the server and split it into an array
     * 
     * @return An array of the URI segments.
     */
    
    public static function get() : array
    {
        $uri = $_SERVER[static::REQUEST_URI];
        if (!!$position = strpos($uri, '?')) $uri = substr($uri, 0, $position);

        $uri = trim($uri, '/');
        $uri_depth = static::getDepth();
        $uri = explode('/', $uri, $uri_depth + 1);
        if (array_key_exists(3, $uri)) unset($uri[$uri_depth]);

        $uri = array_filter($uri);
        return $uri;
    }

    /**
     * Returns the protocol (http or https) of the current request
     * 
     * @return The protocol (http or https) being used to access the application.
     */
    
    public static function getProtocol() : string
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ? 'https' : 'http';
    }

    /**
     * Returns the URL of the current page
     * 
     * @return The URL of the current page.
     */
    
    public static function getUrl() : string
    {
        $protocol = static::getProtocol();
        return $protocol . '://' . $_SERVER[static::HTTP_HOST] . '/';
    }

    /**
     * Returns the URL with the query string attached
     * 
     * @return The URL with the query string.
     */
    
    public static function getUrlWithQueryString() : string
    {
        $url = static::getUrl();
        $url = rtrim($url, '/');
        return $url . $_SERVER[static::REQUEST_URI];
    }

    /**
     * If the route is empty, load the dashboard. Otherwise, load the view
     * 
     * @return The correct file.
     */
    
    public static function view()
    {
        $route = self::get();
        $navigator = BASE_ROOT . 'applications' . DIRECTORY_SEPARATOR;
        static::noCache();
        if (!$route) {
            if (!file_exists($file = $navigator . 'index.php')) throw new CustomException('developer/navigator/missing/dashboard');
            return require_once $file;
        }

        array_splice($route, static::getDepth() - 1, 0, 'views');
        $file = $navigator . implode(DIRECTORY_SEPARATOR, $route) . DIRECTORY_SEPARATOR . 'index.php';
        if (!file_exists($file)) {
            $file = $navigator . '404.php';
            if (file_exists($file)) return require_once $file;

            static::noCache();
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: /');
            exit;
        }
		return require_once $file;
    }

    /**
     * If the request is a POST, then redirect to the URL that was passed in the POST
     * 
     * @param Closure callback A function that takes the URL as a parameter and returns a new URL.
     */
    
    public static function exception(Closure $callback = null) : void
    {
        $url = static::getUrlWithQueryString();
        if ($callback !== null) {
            $callback_response = call_user_func($callback, $url);
            if (filter_var($callback_response, FILTER_VALIDATE_URL)) $url = $callback_response;
        }

        static::noCache();

        if (array_key_exists(static::HTTP_ORIGIN, $_SERVER)
            || array_key_exists(static::HTTP_X_OVERRIDE_IP, $_SERVER)) Output::print(false); 

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);

        exit;
    }

    /**
     * Get the depth of the tree
     * 
     * @return The depth of the tree.
     */
    
    public static function getDepth() : int
    {
        return static::getConfiguration(static::CONFIGURATION_DEPTH) ?? static::DEPTH_DEFAULT;
    }

    /**
     * Get the client IP address
     * 
     * @param int flags 
     * 
     * @return The IP address of the client.
     */
    
    public static function getClientIP(int $flags = 0) : int
    {
        $ip = static::getConfiguration(static::CONFIGURATION_FORCE_IP);
        if (null !== $ip) return ip2long($ip);

        $ip = $_SERVER[static::REMOTE_ADDR] ?? 0;
        if (0 === $ip
            || !is_string($ip)) return 0;

        if (array_key_exists(static::HTTP_CLIENT_IP, $_SERVER)) $ip = $_SERVER[static::HTTP_CLIENT_IP];
        if (array_key_exists(static::HTTP_X_FORWARDED_FOR, $_SERVER)) $ip = $_SERVER[static::HTTP_X_FORWARDED_FOR];
        if (true === (bool)(static::HTTP_X_OVERRIDE_IP_ENABLE & $flags) && array_key_exists(static::HTTP_X_OVERRIDE_IP, $_SERVER))
            $ip = filter_var($_SERVER[static::HTTP_X_OVERRIDE_IP], FILTER_VALIDATE_IP)
                ? $_SERVER[static::HTTP_X_OVERRIDE_IP]
                : long2ip($_SERVER[static::HTTP_X_OVERRIDE_IP]);

        preg_match(static::REGULAR_EXPRESSION_IP_MATCH, $ip, $ip_match_result);
        $ip = reset($ip_match_result);
        if (false === $ip) static::exception();

        return ip2long($ip);
    }

    /**
     * Get the user agent string from the  array
     * 
     * @return The user agent string.
     */
    
    public static function getUserAgent() : string
    {
        $user_agent = 'cli';
        if (array_key_exists(static::HTTP_USER_AGENT, $_SERVER)) $user_agent = $_SERVER[static::HTTP_USER_AGENT];
        return $user_agent;
    }

    /**
     * Send a no-cache header to the client
     * 
     * @return Nothing.
     */
    
    public static function noCache() : void
    {
        static $send;
        if (null !== $send) return;

        $send = true;
        $date_expires = time() - 86400;
        $date_expires = gmdate('D, d M Y H:i:s', $date_expires);
        $date = gmdate('D, d M Y H:i:s', time());

        header('Expires: ' . $date_expires . ' GMT');
        header('Last-Modified: ' . $date . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Connection: close');
    }
}
