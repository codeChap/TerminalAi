<?php
require_once("OpenAi.php");
require_once("Tai.php");
$statements = (new Tai)->run($argv);