<?php
namespace up;

/**
 * Core class
 *
 * @author     Kirill Kirhov
 * @copyright  Up framework (http://up.singleup.net). singleUp team (www.singleup.net)
 * @license    http://up.singleup.org/license/ (GNU)
 */

class core
{
	private static $storage = array();


	public static function init( $frameworkPath )
	{
		self::set( 'core', array(
			  'path' => $frameworkPath
			, 'init' => true
		));
	}

	/**
	 * Get data by namespace and item name
	 *
	 * @param string $namespace
	 * @param int|string $name
	 * @return mixed
	 */
	public static function get( $namespace, $name = null )
	{
		if ( !isset( self::$storage[$namespace] ) ) return null;
		if ( !isset( self::$storage[$namespace][$name] ) ) return self::$storage[$namespace];

		return self::$storage[$namespace][$name];
	}

	/**
	 * Set data by namespace and item name
	 *
	 * @param string $namespace
	 * @param string $name
	 * @param mixed $value
	 */
	public static function set( $namespace, $name, $value = null )
	{
		if ( is_array( $name ) ) {
			foreach( $name as $n => $value )
				self::set( $namespace, $n, $value );

			return;
		}

		if ( !isset( self::$storage[$namespace] ) ) self::$storage[$namespace] = array();

		self::$storage[$namespace][$name] = $value;
	}

	public static function notify( $namespace, $eventName, array $args = array(), $callback = null )
	{
		if ( class_exists( '\Up\Events' ) )
		{
			return \up\events::notify( $namespace, $eventName, $args, $callback );
		}

		return false;
	}

	/**
	 * @return request
	 */
	public static function request()
	{
		return request::instance();
	}
}