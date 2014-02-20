<?php
namespace up;

class response
{
	/**
	 * @var header
	 */
	private $headers;


	public function __construct()
	{
		$this->headers = new header();
	}

	public function setHeaders( $headers )
	{
		if ( is_null( $headers ) || !is_array( $headers ) ) return;

		$this->headers->setByArray( $headers );
	}

	/**
	 * @return Header
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	public function send( $content, array $headers = array(), $httpStatus = 200 )
	{
		$this->setHeaders( $headers );

		$this->sendHeaders( $headers, $httpStatus );
		$this->sendContent( $content );

		return true;
	}

	public function sendHeaders( $httpStatus )
	{
		$this->headers->send( $httpStatus );
	}

	public function sendContent( $content )
	{
		echo $content;
	}
}