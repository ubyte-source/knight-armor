<?PHP

namespace Knight\armor;

use Knight\Configuration;

use Knight\armor\Navigator;
use Knight\armor\CustomException;

class Cookie
{
    use Configuration;

    const CONFIGURATION_SECURE = 0xc8;
    const CONFIGURATION_HTTP_ONLY = 0xc9;
    const CONFIGURATION_SUBDOMAIN = 0x7a508;

    const ACTIVE = true;     // (bool)
    const EXPIRY = 'expiry'; // (string) Name of querystring parameter

    final protected function __construct() {}

    public static function set(string $name, ?string $cookie_content, int $expiry = 0) : bool
    {
        $httponly = static::getConfiguration(static::CONFIGURATION_HTTP_ONLY);
        if (setcookie($name, $cookie_content, $expiry, '/', static::getDomain(), static::getSecure(), true === $httponly)) return true;

        throw new CustomException('developer/cookie/set/content');
    }

    public static function getSecure() : bool
    {
        $secure = static::getConfiguration(static::CONFIGURATION_SECURE);
        return null !== $secure;
    }

    protected static function getDomain() : string
    {
        $subdomain = static::getConfiguration(static::CONFIGURATION_SUBDOMAIN) ?? false;
        if (true !== $subdomain) return $_SERVER[Navigator::HTTP_HOST];

        $domain = explode(chr(46), $_SERVER[Navigator::HTTP_HOST]);
        $domain = array_slice($domain, count($domain) - 2);
        $domain = implode(chr(46), $domain);
        return $domain;
    }
}