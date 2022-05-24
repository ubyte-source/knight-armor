<?PHP

namespace Knight;

use ReflectionClass;

use Knight\armor\CustomException;

defined('CONFIGURATIONS_FOLDER') or define('CONFIGURATIONS_FOLDER', 'configurations');

/* The `Configuration` trait is a helper trait that provides a few methods to help us get configuration
values. */

trait Configuration
{
	/* A helper method that provides a few methods to help us get configuration values. */
	
	final protected static function getConfiguration(int $key, bool $exception = false, ?string $classname = null, string $constant_name = 'PARAMETERS')
    {
		$get = static::getConstantValue($classname, $constant_name, $key);
		if (null !== $get) return $get;
		if (false === $exception) return null;

		throw new CustomException('developer/configuration/require/' . $constant_name . '/' . $key);
	}
	
	/* Returning the class name of the class that is using the trait. */
	
	final protected static function getConfigurationClass(?string $classname) : string
	{
		if (is_string($classname) && false === class_exists($classname)) return $classname;
		if (false === is_string($classname)) $classname = static::class;

		$reflection = new ReflectionClass($classname);
		$reflection_shortname = $reflection->getShortName();
		return $reflection_shortname;
	}

	/* Returning the value of the constant. */
	
	final protected static function getConstant(?string $classname, string $constant_name) :? array
    {
		$configuration_class = static::getConfigurationClass($classname);
		$configuration_class = '\\' . CONFIGURATIONS_FOLDER . '\\' . $configuration_class;
		if (false === class_exists($configuration_class, true)) return null;

		$configuration_class_reflection = new ReflectionClass($configuration_class);
		$configuration_class_reflection_constants = $configuration_class_reflection->getConstants();
		if (false === array_key_exists($constant_name, $configuration_class_reflection_constants)) return null;
		
		return $configuration_class_reflection_constants[$constant_name];
	}

	/* Returning the value of the constant. */
	
	final protected static function getConstantValue(?string $classname, string $constant_name, string $key)
    {
		$configuration_constant = static::getConstant($classname, $constant_name);
		if (null === $configuration_constant) return null;
        if (array_key_exists($key, $configuration_constant)) return $configuration_constant[$key];
        return null;
	}
}