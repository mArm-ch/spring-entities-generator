<?php
require_once(dirname(__FILE__).'/core/helper.php');
require_once(dirname(__FILE__).'/core/generator.php');
require_once(dirname(__FILE__).'/core/constructor.php');

if ($argc < 2) {
	die("Missing definition file");
}
$definitionFile = $argv[1];
if (!file_exists($definitionFile)) {
	die("Cannot load definition file at path : ".$definitionFile);
}

$generator = new SpringGenerator($definitionFile);
$generator->generate();

?>