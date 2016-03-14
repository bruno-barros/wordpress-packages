<?php namespace WpPack\Exceptions;

use Exception;

/**
 * WpException
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2015 Bruno Barros
 */
class WpException extends Exception
{

	/**
	 * @var null|mixed
	 */
	private $data = null;

	/**
	 * @var string
	 */
	private $wp_code = '';

	/**
	 * @param string|\WP_Error $messageOrWpError
	 * @param int $code
	 * @param Exception $previous
	 */
	public function __construct($messageOrWpError = "", $code = 0, Exception $previous = null)
	{
		if (is_wp_error($messageOrWpError))
		{
			$wpError = $messageOrWpError;

			$message = $this->cleanMessage($wpError->get_error_message());

			$this->wp_code = $wpError->get_error_code();

			$this->data = $wpError->get_error_data();
		}
		else
		{
			$message = $messageOrWpError;
		}

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Retrieve error data for error code. Returns mixed or null, if no errors.
	 * @return mixed|null
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Retrieve the WordPress WP_Error code
	 * @return string
	 */
	public function getWpCode()
	{
		return $this->wp_code;
	}


	/**
	 * Remove this string from the beginning: '<strong>ERROR</strong>:'
	 * ... of course it is an error! ;-)
	 *
	 * @param string $wpMessage
	 * @return mixed
	 */
	private function cleanMessage($wpMessage = '')
	{
		return preg_replace("/^\s?<strong>.*<\/strong>:\s?/m", "", $wpMessage);
	}

}