<?php
namespace up;

/**
 * Object classes
 * 
 * up\object
 * 
 * @package    $object
 * @relation   ArrayObject
 * @hook       object
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class object extends \ArrayObject implements \Serializable
{
	/**
	 * @param array|object $item
	 * @param mixed $value
	 */
	public function __construct( $item = array(), $value = null )
	{
		$this->set( $item, $value );
	}
	
	/**
	 * Unset item
	 *
	 * @param string $index
	 */
	public function __unset( $index )
	{
		static::offsetUnset( $index );
	}
	
	/**
	 * Return isset item
	 *
	 * @param name $index
	 * @return boolean
	 */
	public function __isset( $index )
	{
		return static::offsetExists( $index );
	}
	
	/**
	 * Set item by index
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function __set( $index, $value = null )
	{
		static::offsetSet( $index, $value );
	}
	
	/**
	 * Return item by index
	 *
	 * @param string $index
	 * @return mixed
	 */
	public function __get( $index )
	{
		return static::offsetGet( $index );
	}
	
	/**
	 * On var export
	 *
	 * @param array $params
	 * @return Up\Object
	 */
	public static function __set_state( array $params )
	{
		$Obj = new object( $params );
		
		return $Obj;
	}
	
	/**
	 * Call if print object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return __CLASS__;
	}

	public function isin( $index )
	{
		return $this->__isset( $index );
	}
	
	/**
	 * Set item
	 *
	 * @param mixed $index
	 * @param mixed $value
	 * @return Up\Object
	 */
	public function set( $index, $value = null )
	{
		if ( is_array( $index ) || is_object( $index ) ) {
			foreach ( $index as $item => $value )
				static::__set( $item, $value );
				
			return $this;
		}

		static::__set( $index, $value );
		
		return $this;
	}
	
	/**
	 * Return item by index
	 *
	 * @param string $index
	 * @return mixed
	 */
	public function get( $index = 0 )
	{
		return static::__get( $index );
	}
	
	/**
	 * Equivalent to append
	 *
	 * @param mixed $newval
	 * @return Up\Object
	 */
	public function add( $newval )
	{
		static::append( $newval );
		
		return $this;
	}
	
	/**
	 * Unserialize string
	 *
	 * @param string $serialized
	 * @return Up\Object
	 */
	public function unserialize( $serialized )
	{
		$items = unserialize( $serialized );

		$this->set( $items );

		return $items;
	}
	
	/**
	 * Return first item
	 *
	 * @return mixed
	 */
	public function getFirst()
	{
		reset( $this );
		
		return current( $this );
	}
	
	/**
	 * Return last item
	 *
	 * @return mixed
	 */
	public function getLast()
	{
		return end( $this );
	}

	public function serialize()
	{
		$return = array();
		foreach( $this as $property => $value )
			$return[$property] = $value;

		$return = serialize( $return );

		return $return;
	}
}