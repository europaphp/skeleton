<?php

namespace Europa\Dispatcher\HttpNegotiator;
use Europa\Request\Http;

interface HttpNegotiatorInterface
{
	public function negotiate(Http $request);
}
