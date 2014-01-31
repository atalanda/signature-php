<?php

namespace Atalogics\Signature;

class Request {

  public function __construct($method, $path, $parameters, $time=null) {
    $this->method = strtoupper($method);
    $this->path = $path;
    $this->parameters = $parameters;
    $this->time = $time === null ? time() : $time;
  }

  public function sign(Token $token) {
    $parameter_string = $this->buildParameterString();
    $signature = $this->calculateSignature($token, $parameter_string, $this->time);

    return array_merge($this->parameters, array(
      "auth_hash" => array(
        "auth_timestamp" => $this->time,
        "auth_key" => $token->getKey(),
        "auth_signature" => $signature
      )
    ));
  }

  public function authenticate(Token $token, $timestampGrace=600) {
    if($this->getAuthHash() === null) {
      return array(
        "authenticated" => false,
        "reason" => "Auth hash is missing"
      );
    }

    if($this->time - (int)$this->getAuthHash()["auth_timestamp"] > $timestampGrace) {
      return array(
        "authenticated" => false,
        "reason" => "Auth timestamp is older than ".$timestampGrace." seconds"
      );
    }

    $recalculatedSignature = $this->calculateSignature($token, $this->buildParameterString(), $this->getAuthHash()["auth_timestamp"]);
    if($recalculatedSignature !== $this->getAuthHash()["auth_signature"]) {
      return array(
        "authenticated" => false,
        "reason" => "Signature does not match"
      );
    }

    return array(
      "authenticated" => true
    );
  }

  private function getAuthHash() {
    if(isset($this->parameters["auth_hash"])) {
      return $this->parameters["auth_hash"];
    }

    return null;
  }

  private function calculateSignature($token, $string, $time) {
    return hash("sha256", $string.$token->getKey().$token->getSecret().$time);
  }

  private function buildParameterString() {
    return $this->method.$this->path.$this->paramsToString($this->parameters);
  }

  private function paramsToString($value, $key=null) {
    $str = "";

    // do not include auth_hash in signing
    if($key == "auth_hash") {
      return "";
    }

    if(!is_array($value)) {
      if(is_bool($value)) {
        $value = $value ? "true" : "false";
      }
      $str .= ($key !== null) ? $key.$value : $value;
    } else {
      $str .= $key;
      if($this->isAssoc($value)) {
        $keys = array_keys($value);
        sort($keys);
        foreach($keys as $k) {
          $str .= $this->paramsToString($value[$k], $k);
        }
      } else {
        foreach($value as $v) {
          $str .= $this->paramsToString($v);
        }
      }

    }

    return $str;
  }

  private function isAssoc($array) {
    return ($array !== array_values($array));
  }
}

