<?php

preg_match("#^\d.\d#", PHP_VERSION, $match);

$version = $match[0];

$pharPath = __DIR__ . "/generator/phars/ray_php_{$version}.phar";

if (file_exists($pharPath)) {
    require_once $pharPath;
}
