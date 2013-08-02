<?php

namespace Europa\Response;

interface ResponseInterface
{
  public function send();

  public function setBody($body);

  public function getBody();

  public function setStatus($status);

  public function getStatus();
}