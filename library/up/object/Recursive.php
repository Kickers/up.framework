<?php
namespace Up\Object;

class Recursive extends \Up\Object
{
	public function __construct( $item = array(), $value = null )
	{
		if ( $value && !is_array( $item ) || !is_object( $item ) ) return parent::__construct( $item, $value );

		foreach( $item as $objName => $obj ) {
			if ( is_array( $obj ) || is_object( $obj ) ) $obj = new self( $obj );

			$this->set( $objName, $obj );
		}
	}
}