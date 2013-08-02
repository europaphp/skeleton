<?php

namespace Europa\Request;

interface HttpInterface extends RequestInterface
{
  const OPTIONS = 'options';

  const GET = 'get';

  const HEAD = 'head';

  const POST = 'post';

  const PUT = 'put';

  const DELETE = 'delete';

  const TRACE = 'trace';

  const CONNECT = 'connect';

  const PATCH = 'patch';

  public function getMethod();

  public function getHeader($name);

  public function getUri();

  public function accepts($type);
}