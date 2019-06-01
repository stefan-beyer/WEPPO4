<?php

namespace WEPPO\Utility\REST;

class RestResult {

  private $status, $body, $headers;

  function __construct($result) {
    $this->status = intval($result['http_code']);
    $this->body = $result['body'];

    // headers vorbereiten
    $headers = explode("\r\n", $result['header']);
    $headers = array_map('trim', $headers);
    array_shift($headers); // http status entfernen
    $this->headers = [];
    foreach ($headers as $h) {
      if (empty($h)) continue;
      $pos   = strpos($h, ':');
      $key   = strtolower(trim(substr($h, 0, $pos)));
      $value = trim(substr($h, $pos+1));
      $this->headers[$key] = $value;
    }
  }

  function getStatus() {
    return $this->status;
  }

  function isOK() {
    return $this->status >= 200 && $this->status <= 299;
  }

  function getBodyRaw() {
    return $this->body;
  }

  function getBodyJSON() {
    return json_decode($this->body);
  }

  function getHeader($key) {
    $key = strtolower($key);
    if (!isset($this->headers[$key])) return null;
    return $this->headers[$key];
  }

}
