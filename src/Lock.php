<?PHP

namespace Knight;

use Knight\armor\CustomException;

trait Lock
{
	final protected function  __construct()
	{
		throw new CustomException('developer/configuration/lock');
	}

	final public function  __clone()
	{
		throw new CustomException('developer/configuration/lock');
	}

	final public function  __sleep()
	{
		throw new CustomException('developer/configuration/lock');
	}

	final public function  __set_state()
	{
		throw new CustomException('developer/configuration/lock');
	}

	final public function  __call(string $name, $arguments)
	{
		throw new CustomException('developer/configuration/lock');
	}

	final public function  __set(string $name, $arguments)
	{
		throw new CustomException('developer/configuration/lock');
	}

	final public static function __callStatic(string $name, $arguments)
	{
		throw new CustomException('developer/configuration/lock');
	}
}