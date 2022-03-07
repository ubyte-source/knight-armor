<?PHP

namespace Knight\armor;

use stdClass;

use Knight\armor\CustomException;

/* Get the lock file, parse it, and return the version of the library */

class Composer
{
    const DOCUMENT_ROOT = 'DOCUMENT_ROOT';

    const LOCK_NAME = 'composer.lock';
    const LOCK_NAME_PACKAGES = 'packages';

    final protected function __construct() {}

    /**
     * Get the version of a package from the lock file
     * 
     * @param string name The name of the package.
     * @param string library The name of the library to get the version for.
     * 
     * @return The version of the package.
     */
    
    public static function getLockVersion(string $name, string $library = null) : string
    {
        if (null !== $library) return $library;
        $packages = static::getLockPackages();
        foreach ($packages as $pack) {
            if (!property_exists($pack, 'version')
                || !property_exists($pack, 'name')
                || $pack->name !== $name) continue;

            return $pack->version;
        }

        throw new CustomException('developer/composer/lock/found');
    }

    /**
     * Read the lock file and return the contents as a PHP object
     * 
     * @return The lock file contents.
     */
    
    protected static function getLock() : stdClass
    {
        $baseroot = rtrim($_SERVER[static::DOCUMENT_ROOT], DIRECTORY_SEPARATOR);
        $baseroot = dirname($baseroot);

        $lock = $baseroot . DIRECTORY_SEPARATOR . static::LOCK_NAME;

        if (!file_exists($lock)
            || is_dir($lock)) throw new CustomException('developer/composer/run');

        $lock_decoded = file_get_contents($lock);
        $lock_decoded = json_decode($lock_decoded, false);
        if (null === $lock_decoded
            && json_last_error() !== JSON_ERROR_NONE) throw new CustomException('developer/composer/lock/decoded');

        return $lock_decoded;
    }

    /**
     * Get the packages from the lock file
     * 
     * @return An array of package names.
     */
    
    protected static function getLockPackages() : array
    {
        $memory_cache = null;
        if (null !== $memory_cache) return $memory_cache;

        $lock = static::getLock();

        if (!property_exists($lock, $name = static::LOCK_NAME_PACKAGES)) throw new CustomException('developer/composer/lock/packages');

        $memory_cache = $lock->$name;

        return $memory_cache;
    }
}