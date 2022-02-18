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
	
	final protected static function getConfigurationClass(?string $classname) : string
	{
		if (is_string($classname) && false === class_exists($classname)) return $classname;
		if (false === is_string($classname)) $classname = static::class;

		$reflection = new ReflectionClass($classname);
		$reflection_shortname = $reflection->getShortName();
		return $reflection_shortname;
	}

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

	final protected static function getConstantValue(?string $classname, string $constant_name, string $key)
    {
		$configuration_constant = static::getConstant($classname, $constant_name);
		if (null === $configuration_constant) return null;
        if (array_key_exists($key, $configuration_constant)) return $configuration_constant[$key];
        return null;
	}
}