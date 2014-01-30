<?php

namespace Atalogics\Signature;

class Token {

  private $key;
  private $secret;

  public function __construct($key, $secret) {
    $this->key = $key;
    $this->secret = $secret;
  }

  public function sign(Request $request) {
    return $request->sign(this);
  }

  public function getKey() {
    return $this->key;
  }

  public function getSecret() {
    return $this->secret;
  }
}