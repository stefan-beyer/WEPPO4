<?php


namespace WEPPO\Helpers;

interface Texter {
    public function text(string $key, $context = null) : string;
}