<?php
use Symfony\Component\Yaml\Yaml;

class Fixture
{
    public static function load($file_name_prefix)
    {
        $file_path = __DIR__."/tests/fixtures/$file_name_prefix.yml";
        return Yaml::parse($file_path);
    }
}

