<?php
namespace up\pattern;

/**
 * Singleton pattern
 * 
 * up\instance
 * 
 * @package    $instance
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class instance
{
	static private $__instance = array();

	/**
	 * Singleton pattern
	 *
	 * @return class object
	 */
	public static function getInstance()
	{
		$class = get_called_class();
		
		if ( self::$__instance[$class] ) return self::$__instance[$class];
		
		return self::$__instance[$class] = new $class();
	}
}