<?php
require_once("OpenAi.php");
require_once("Tai.php");
$config = parse_ini_file("config.ini");
$statements = (new Tai)->run($config, $argv);