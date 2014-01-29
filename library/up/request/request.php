<?php
namespace up;

use Up\Request\Cookie;
use Up\Request\Data;
use up\request\exception;
use Up\Request\Session;

final class request
{
	public $query;
	public $post;
	public $delete;
	public $put;
	public $header;

	/**
	 * @var \up\request\source\cookie
	 */
	public $cookie;
	public $files;

	/**
	 * @var \up\request\source\session
	 */
	public $session;

	private static $initVars = array(
		  'arg'
		, 'cookie'
		, 'delete'
		, 'env'
		, 'files'
		, 'get'
		, 'header'
		, 'put'
		, 'session'
	);

	private static $instance;

	private function __construct()
	{
		$this->initVars();
	}

	/**
	 * @return request
	 */
	public static function instance()
	{
		return self::$instance ? self::$instance : self::$instance = new self();
	}

	private function initVars()
	{
		foreach( self::$initVars as $var )
			unset( $this->$var );
	}

	public function __get( $var )
	{
		if ( isset( $this->$var ) )
			return $this->$var;

		if ( !in_array( $var, self::$initVars ) )
			throw new exception( 'Unknown request source ' . $var );

		$this->{'__init' . $var}();

		return $this->__get( $var );
	}

	public function __set( $var, $value )
	{
		$this->$var = $value;
	}

	/**
	 * \Up\Request\Data
	 */
	public function __initQuery()
	{
		return $this->query ? $this->query : $this->query = new data( $_GET );
	}

	public function __initPost()
	{
		return $this->post ? $this->post : $this->post = new data( $_POST );
	}

	public function __initDelete()
	{

	}

	public function __initPut()
	{

	}

	public function __initHeader()
	{
		
	}

	public function __initCookie()
	{
		return $this->cookie ? $this->cookie : $this->cookie = new \up\request\source\cookie( $_COOKIE );
	}

	public function __initFiles()
	{
		if ( $this->files )
			return $this->files;

		$files = array();
		foreach( $_FILES as $variable => $values ) {
			foreach( $values as $num => $value ) {
				if ( is_array( $value ) ) {
					foreach( $value as $name => $val )
						$files[$variable][$name][$num] = $val;
				} else {
					$files[$variable][$num] = $value;
				}
			}
		}

		return $this->files = new Data( $files );
	}

	public function __initSession()
	{
		return $this->session ? $this->session : $this->session = new \up\request\source\session();
	}
}