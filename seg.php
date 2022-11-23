<?php


$skeleton = array(
	'mapstruct' => false,
	'rootPackage' => 'io.ansermot.myassoc',
	'package' => 'domain',
	'entities' => array(
		'Role' => array(
			'primaryKey' => 'id',
			'attributs' => array(
				'id' => 'long',
				'name' => 'string',
			)
		)
	)
)

$json = json_encode($skeleton, false);

generate($json);















/* ----------------------------------------------------------------------------- */

function generate($skeleton) {

	if (!isset($skeleton->mapstruct) ||
		!isset($skeleton->rootPackage) ||
		!isset($skeleton->package) ||
		!isset($skeleton->entities)) {
		die('Missing mandatory fields');
	}

	$pRoot = dirname(__FILE__).'/';
	$pOut = $pRoot.'output/'.microtime().'/';

	$rootPackage = $pOut.$skeleton->rootPackage;
	mkdir($rootPackage);

	foreach ($skeleton->entities as $entityName => $entityConfig) {
		$dirPath = $rootPackage.strtolower($entityName);
		mkdir($dirPath);

		$fEntity = $entityName.'.java';
		$fDto = $entityName.'DTO.java';
		$fMapper = $entityName.'Mapper.java';

		touch($fEntity);
		touch($fDto);
		touch($fMapper);

		constructEntity($fEntity, $entityName, $entityConfig);
		constructDto($fDto, $entityName, $entityConfig);
		constructMapper($fMapper, $entityName, $entityConfig);
	}
}


function constructEntity($file, $rootPackage, $package, $name, $config) {
	$contents = array();
	$contents[] = 'package '.$rootPackage.'.'.strtolower($package).'.'.strtolower($name).';';
	$contents[] = '';
	$contents[] = 

	$finalContents = implode("/n", $contents);
	file_put_contents($file, $finalContents);
	return $finalContents;
}

function constructDto($file, $rootPackage, $package, $name, $config) {

}

function constructMapper($file, $rootPackage, $package, $name, $config) {

}

?>