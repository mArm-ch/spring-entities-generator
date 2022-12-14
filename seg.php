<?php
require_once(dirname(__FILE__).'/core/helper.php');
require_once(dirname(__FILE__).'/core/generator.php');
require_once(dirname(__FILE__).'/core/commenter.php');
require_once(dirname(__FILE__).'/core/constructor.php');

if ($argc < 2) {
	die("Missing definition file");
}
$definitionFile = $argv[1];
if (!file_exists($definitionFile)) {
	die("Cannot load definition file at path : ".$definitionFile);
}

$generator = new SpringGenerator($definitionFile, dirname(__FILE__).'/');
H::e("Use this configuration?  Type 'Y/y' to continue: ");
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(strtolower(trim($line)) != 'y'){
    die("Exiting...");
}
$generator->generate();

?>