<?php
namespace up\template\view;

use up\events;
use up\template\exception;
use up\template\view;

class native extends view
{
	const EVENT_NAMESPACE     = 'up.template.native';
	
	const EVENT_BEFORE_VIEW   = 'beforeView';
	const EVENT_AFTER_VIEW    = 'afterView';
	
	const EVENT_BEFORE_LAYOUT = 'beforeLayout';
	const EVENT_AFTER_LAYOUT  = 'afterLayout';

	const EVENT_MAGIC  = 'magic';

	private $vars = array();

	public static $buffers = array();

	
	public function render( $__includeFile, $__vars = null )
	{
		if ( !$__vars )
			$__vars = array();

		$this->vars = $__vars;

		//$template = function( $__includeFile, $__vars ) {
			extract( $__vars );
			ob_start();
				require( $__includeFile );
				$__content = ob_get_contents();
			ob_end_clean();
			
			unset( $__vars, $__includeFile );

			//return get_defined_vars();
			$vars = get_defined_vars();
		//};
		
		//$vars = $template( $filename, $vars );

		$content = $vars['__content'];

		unset( $vars['__content'] );

		$this->vars = array_merge( $this->vars, $vars );
		
		return $content;
	}

	public function getVars()
	{
		return $this->vars;
	}

	public function bufferOn()
	{
		ob_start();
	}

	public function bufferOff()
	{
		$content = ob_get_contents();
		ob_end_clean();

		self::$buffers[] = $content;
	}

	public function __call( $method, $args )
	{
		if ( events::hasEventListner( self::EVENT_NAMESPACE, self::EVENT_MAGIC . $method ) ) {
			return events::notify( self::EVENT_NAMESPACE, self::EVENT_MAGIC . $method, array( $args, $this->vars, $this ) );
		} else {
			throw new exception( 'unknown call method ' . $method );
		}
	}
}