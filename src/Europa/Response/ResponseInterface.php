<?php

namespace Europa\Response;

interface ResponseInterface
{
  public function __invoke();

  public function __toString();

  public function setBody($body);

  public function getBody();

  public function setStatus($status);

  public function getStatus();
}