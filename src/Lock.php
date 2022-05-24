<?PHP

namespace Knight;

use Knight\armor\CustomException;

/* The `trait Lock` is used to prevent developers from accidentally changing the state of the
configuration object. */

trait Lock
{
	/**
	 * This is a protected constructor that throws an exception
	 */
	
	final protected function  __construct()
	{
		throw new CustomException('developer/configuration/lock');
	}

	/**
	 * This function is called when a developer attempts to clone a configuration object. 
	 * 
	 * The function throws an exception to prevent developers from accidentally cloning a configuration
	 * object
	 */
	
	final public function  __clone()
	{
		throw new CustomException('developer/configuration/lock');
	}

	/**
	 * Throw an exception if the developer tries to serialize the configuration
	 */
	
	final public function  __sleep()
	{
		throw new CustomException('developer/configuration/lock');
	}

	/**
	 * This function is used to prevent the developer from changing the state of the configuration object
	 */
	
	final public function  __set_state()
	{
		throw new CustomException('developer/configuration/lock');
	}

	/**
	 * *This function is called when a developer attempts to call a method that does not exist.*
	 * 
	 * The above function is called when a developer attempts to call a method that does not exist. 
	 * 
	 * @param string name The name of the method that was called.
	 * @param arguments The arguments passed to the method.
	 */
	
	final public function  __call(string $name, $arguments)
	{
		throw new CustomException('developer/configuration/lock');
	}

	/**
	 * *This function is called when a developer attempts to set a configuration value using the
	 * `->set()` method.*
	 * 
	 * @param string name The name of the property to set.
	 * @param arguments The arguments passed to the method.
	 */
	
	final public function  __set(string $name, $arguments)
	{
		throw new CustomException('developer/configuration/lock');
	}

	/* This is a function that is called when a developer attempts to call a static method that does not
	exist. */
	
	final public static function __callStatic(string $name, $arguments)
	{
		throw new CustomException('developer/configuration/lock');
	}
}
