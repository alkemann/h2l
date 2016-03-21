<?php

namespace alkemann\h2l;

class Request
{
	private $_request;
	private $_server;
	private $_get;
	private $_post;

	public function __construct(array $request = [], array $server = [], array $get = [], array $post = [])
	{
		$this->_request = $request;
		$this->_server 	= $server;
		$this->_get 	= $get;
		$this->_post 	= $post;
	}

	public function response()
	{
		return null;
	}
}
