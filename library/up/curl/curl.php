<?php
namespace up;

use up\curl\exception;

/**
 * cURL curl
 * 
 * \Up\Curl
 * 
 * @package    $Curl
 * @relationUp $Exception, $Header\Parse
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class curl
{
	private $curl;
	private $url;
	
	private $options = array();
	private $init    = false;
	
	private static $defaultOptions = array(
		CURLOPT_RETURNTRANSFER => true
	);
		
	/**
	 * Init object
	 *
	 * @param string|null $url
	 */
	public function __construct( $url = null )
	{
		$this->url     = $url;
		$this->options = self::$defaultOptions;
	}
	
	/**
	 * Close resource
	 *
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Clone object. Copy handle
	 *
	 */
	public function __clone()
	{
		if ( is_resource( $this->curl ) )
			$this->curl = curl_copy_handle( $this->curl );
	}
	
	/**
	 * Set default cURL options
	 *
	 * @param array $options
	 */
	public static function setDefaultOptions( array $options )
	{
		self::$defaultOptions = $options;
	}
	
	/**
	 * Exec cURL
	 *
	 * @return mixed
	 */
	public function exec()
	{
		if ( $this->init === false ) $this->init();
		
		$result = curl_exec( $this->curl );
		
		if ( $result === true ) return true;
		if ( $result === false ) $this->error( $this->getError(), $this->getErrno() );
		
		return $result;
	}
	
	/**
	 * Init cURL. Set options
	 * 
	 * @return \curlup\object
	 */
	public function init()
	{
		if ( !is_resource( $this->curl ) ) {
			$this->curl = curl_init( $this->url );
			
			if ( $this->curl === false ) $this->error( 'Error init curl!' );
		}
		
		if ( curl_setopt_array( $this->curl, $this->options ) === false ) $this->error( 'Can not set curl options!' );
		
		$this->init = true;
		
		return $this;
	}
	
	/**
	 * Set cURL option as option name and value
	 *
	 * @param int $option
	 * @param mixed $value
	 * 
	 * @return \curlup\object
	 */
	public function setOption( $option, $value )
	{
		$this->options[$option] = $value;
		$this->init             = false;
		
		return $this;
	}
	
	/**
	 * Set cURL options as array. If clear is true replace all prev options
	 *
	 * @param array $options
	 * @param boolean $clear
	 * @return \curlup\object
	 */
	public function setOptions( array $options, $clear = false )
	{
		if ( $clear === true )
		{
			$this->options = $options;
		}
		else
		{
			foreach ( $options as $option => $value )
			{
				$this->setoption( $option, $value );
			}
		}
		
		$this->init = false;
		
		return $this;
	}
	
	public function getOption( $option )
	{
		return $this->options[$option];
	}
	
	/**
	 * Close cURL resource
	 * 
	 * @return \curlup\object
	 */
	public function close()
	{
		if ( is_resource( $this->curl ) ) curl_close( $this->curl );
		
		$this->curl = null;
		$this->init = false;
		
		return $this;
	}
	
	/**
	 * Get information regarding a specific transfer 
	 *
	 * @param int $option
	 * @return mixed
	 */
	public function getInfo( $option = 0 )
	{
		return curl_getinfo( $this->curl, $option );
	}
	
	/**
	 * Return a string containing the last error for the current session
	 *
	 * @return string|null
	 */
	public function getError()
	{
		if ( is_resource( $this->curl ) ) return curl_error( $this->curl );
		
		return null;
	}
	
	/**
	 * Return the last error number
	 *
	 * @return int|null
	 */
	public function getErrno()
	{
		if ( is_resource( $this->curl ) ) return curl_errno( $this->curl );
		
		return null;
	}
	
	/**
	 * Gets cURL version information
	 *
	 * @param int $age
	 * @return array
	 */
	public function getVersion( $age = \CURLVERSION_NOW )
	{
		return curl_version( $age );
	}
	
	/**
	 * Return cURL resource
	 *
	 * @return resource
	 */
	public function getCurlResource()
	{
		return $this->curl;
	}
	
	public function parseHeader( $headerStr = null )
	{
		if ( !is_string( $headerStr ) ) $headerStr = $this->getInfo( CURLINFO_HEADER_OUT );
		if ( !$headerStr ) return false;
		
		return header\parse::parse( $headerStr );
	}
	
	
	private function error( $msg, $code = 0 )
	{
		throw new exception( $msg, $code );
	}
}