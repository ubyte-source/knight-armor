<?PHP

namespace Knight;

use ReflectionClass;

use Knight\armor\CustomException;

defined('CONFIGURATIONS_FOLDER') or define('CONFIGURATIONS_FOLDER', 'configurations');

trait Configuration
{
	final protected static function getConfiguration(int $key, bool $exception = false, ?string $classname = null, string $constant_name = 'PARAMETERS')
    {
		$get = static::getConstantValue($classname, $constant_name, $key);
		if (null !== $get) return $get;
		if (false === $exception) return null;

		throw new CustomException('developer/configuration/require/' . $constant_name . '/' . $key);
	}
	
	final protected static function getConfigurationFileName(?string $classname) : string
	{
		if (is_string($classname)
			&& !class_exists($classname)) return $classname;
		
		$reflection = new ReflectionClass(static::class);
		$reflection_shortname = $reflection->getShortName();
		return $reflection_shortname;
	}
	
	final protected static function getConstant(?string $classname, string $constant_name) :? array
    {
		$configuration_filename = static::getConfigurationFileName($classname);
		$configuration_file = 'configurations' . '\\' . $configuration_filename;
		if (!class_exists($configuration_file, true)) return null;

		$configuration_file_reflection = new ReflectionClass($configuration_file);
		$configuration_file_reflection_constants = $configuration_file_reflection->getConstants();
		if (!array_key_exists($constant_name, $configuration_file_reflection_constants)) return null;
		
		return $configuration_file_reflection_constants[$constant_name];
	}
	
	final protected static function getConstantValue(?string $classname, string $constant_name, string $key)
    {
		$configuration_constant = static::getConstant($classname, $constant_name);
		if (null === $configuration_constant) return null;
        if (array_key_exists($key, $configuration_constant)) return $configuration_constant[$key];
        return null;
	}

	final protected static function getConfigurationNamespace() : string
	{
		return CONFIGURATIONS_FOLDER;
	}
}