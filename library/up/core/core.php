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
		if ( class_exists( '\up\events' ) )
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

	public static function redirect( $url, $callback = null )
	{
		$Headers = new \up\header(array(
			  'location'      => $url
			, 'create-by'     => 'SingleUp team (http://singleup.net)'
			, 'time-render'   => sprintf( '%.5f sec', microtime( true ) - UP_START_TIME )
			, 'X-Powered-By'  => 'ha?'
			, 'Expires'       => 'Mon, 26 Jul 1997 05:00:00 GMT'
			, 'Last-Modified' => gmdate("D, d M Y H:i:s") . ' GMT'
			, 'Cache-Control' => 'no-store, no-cache, must-revalidate'
			, 'Pragma'        => 'no-cache'
		));

		$Headers->send( 301 );

		if ( $callback && is_callable( $callback ) ) {
			$callback();
		}

		exit;
	}
}