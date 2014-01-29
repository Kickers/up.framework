<?php
namespace up;

use up\template\exception;
use up\template\view;

class template
{
	private static $init = false;

	const EVENT_NAMESPACE     = __CLASS__;

	const EVENT_INIT_SELF     = 'init.self';
	const EVENT_INIT_TEMPLATE = 'init.template';

	const EVENT_BEFORE_RENDER = 'before.init';
	const EVENT_AFTER_RENDER  = 'after.init';

	private $template;
	private $vars = array();

	/**
	 * @param string $scheme
	 * @param array $templateParams
	 * @return Template\View\Abstracting
	 */
	public function __construct( view $Template, array $templateParams = array() )
	{
		self::init();

		\up::notify( self::EVENT_NAMESPACE, self::EVENT_INIT_TEMPLATE, array( $Template, $templateParams ) );

		$this->template = $Template;
	}

	public function render( $filename, $vars = null )
	{
		if ( !is_array( $vars ) ) $vars = $this->vars;

		$newFilename = \up::notify( self::EVENT_NAMESPACE, self::EVENT_BEFORE_RENDER, array( $this->template, $filename, $vars ) );

		if ( $newFilename ) $filename = $newFilename;

		$content = $this->template->render( $filename, $vars );
		$vars    = $this->template->getVars();

		\up::notify( self::EVENT_NAMESPACE, self::EVENT_AFTER_RENDER, array( $this->template, $filename, $vars ) );

		if ( is_array( $vars ) ) $this->vars = $vars;

		return $content;
	}

	public function getVars()
	{
		return $this->vars;
	}

	private static function init()
	{
		if( self::$init === true ) return;

		self::$init = true;

		\up::notify( self::EVENT_NAMESPACE, self::EVENT_INIT_SELF );
	}

	private static function exception( $msg, $code = 0 )
	{
		throw new exception( $msg, $code );
	}
}