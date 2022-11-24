<?php


$skeleton = array(
	'props' => array(
		'mapstruct' => false,
		'lombok' => false,
		'rootPackage' => 'io.ansermot.myassoc',
		'package' => 'domain',
		'spaces' => 4,
		'mapperSingleton' => true,
	),
	'entities' => array(
		'Role' => array(
			'primaryKey' => 'id',
			'attributes' => array(
				'id' => 'long',
				'name' => 'string',
			)
		)
	)
);

$json = json_encode($skeleton);
generate($json);







/* ----------------------------------------------------------------------------- */

$primitives = array(
	'int',
	'long',
	'string',
	'float',
);

function generate($skeleton) {

	$skeleton = json_decode($skeleton, false);

	$debug = true;

	printInfos($skeleton);

	if (!isset($skeleton->props->mapstruct) ||
		!isset($skeleton->props->lombok) ||
		!isset($skeleton->props->rootPackage) ||
		!isset($skeleton->props->package) ||
		!isset($skeleton->props->mapperSingleton) ||
		!isset($skeleton->entities)) {
		die('Missing mandatory fields');
	}

	e("Generating entities...");
	$props = $skeleton->props;
	$entities = $skeleton->entities;

	$pRoot = dirname(__FILE__).'/';
	$pOut = $pRoot.'output/'.time().'/';

	$rootPackage = $pOut.$props->rootPackage.'/'.strtolower($props->package);
	mkdir($rootPackage, 0777, true);
	e("- Output folder will be ".$pOut);

	e("Begin generation...");	
	foreach ($entities as $entityName => $entityConfig) {
		$dirPath = $rootPackage.'/'.strtolower($entityName);
		mkdir($dirPath, 0777, true);
		e("Generating '".$entityName."'");

		$fEntity = $entityName.'.java';
		$fDto = $entityName.'DTO.java';
		$fMapper = $entityName.'Mapper.java';

		touch($fEntity);
		touch($fDto);
		touch($fMapper);

		e("- File : ".$fEntity);
		$c = constructEntity($dirPath.'/'.$fEntity, $entityName, $entityConfig, $props);
		if ($debug) { e($c); e(""); }
		e("- File : ".$fDto);
		$c = constructDto($dirPath.'/'.$fDto, $entityName, $entityConfig, $props);
		if ($debug) { e($c); e(""); }
		e("- File : ".$fMapper);
		$c = constructMapper($dirPath.'/'.$fMapper, $entityName, $entityConfig, $props);
		if ($debug) { e($c); e(""); }
	}
}


function constructEntity($file, $name, $config, $properties) {
	$SP = str_pad(' ', $properties->spaces);

	// Package
	$c = array();
	$c[] = 'package '.$properties->rootPackage.'.'.strtolower($properties->package).'.'.strtolower($name).';';
	$c[] = '';

	// Imports
	if ($properties->lombok) {
		$c[] = 'import lombok.Data;';
		$c[] = 'import lombok.AllArgsContructor;';
		$c[] = 'import lombok.NoArgsConstructor;';
		$c[] = '';
	}
	$c[] = 'import javax.persistence.Entity;';
	$c[] = 'import javax.persistence.GeneratedValue;';
	$c[] = 'import javax.persistence.Id';
	$c[] = '';
	$c[] = 'import static javax.persistence.GenerationType.AUTO;';
	$c[] = '';

	// Before class annotations
	$c[] = '@Entity';
	if ($properties->lombok) {
		$c[] = '@Data';
		$c[] = '@NoArgsConstructor';
		$c[] = '@AllArgsContructor';
	}

	// Class declaration
	$c[] = 'public class '.$name.' {';

	// Class attributes
	$primaryKey = $config->primaryKey;
	foreach ($config->attributes as $field => $type) {
		if ($field == $primaryKey) {
			$c[] = $SP.'@Id';
			$c[] = $SP.'@GeneratedValue(strategy = AUTO)';
		}
		$c[] = $SP.'private '.ucfirst($type).' '.$field.';';
	}

	// Constructors if lombok is disabled
	if (!$properties->lombok) {
		// No Args constructor
		$c[] = '';
		$c[] = $SP.'public '.$name.'() {';
		foreach($config->attributes as $field => $type) {
			$c[] = $SP.$SP.'this.'.$field.' = null;';
		}
		$c[] = $SP.'}';
		$c[] = '';

		// All Args constructor
		$args = array();
		foreach($config->attributes as $field => $type) {	
			$args[] = ucfirst($type).' '.$field;
		}
		$args = implode(', ', $args);
		$c[] = $SP.'public '.$name.'('.$args.') {';
		foreach($config->attributes as $field => $type) {
			$c[] = $SP.$SP.'this.'.$field.' = '.$field.';';
		}
		$c[] = $SP.'}';
	}

	$c[] = '}';


	$finalContents = implode("\n", $c);
	file_put_contents($file, $finalContents);
	return $finalContents;
}

/**
 * 
 */
function constructDto($file, $name, $config, $properties) {
	$SP = str_pad(' ', $properties->spaces);

	// Package
	$c = array();
	$c[] = 'package '.$properties->rootPackage.'.'.strtolower($properties->package).'.'.strtolower($name).';';
	$c[] = '';

	// Imports
	if ($properties->lombok) {
		$c[] = 'import lombok.Getter;';
		$c[] = 'import lombok.Setter;';
		$c[] = '';
	}

	// Before class annotations
	if ($properties->lombok) {
		$c[] = '@Getter';
		$c[] = '@Setter';
	}

	// Class declaration
	$c[] = 'public class '.$name.'DTO {';

	// Class attributes
	$primaryKey = $config->primaryKey;
	foreach ($config->attributes as $field => $type) {
		$c[] = $SP.'private '.ucfirst($type).' '.$field.';';
	}

	// Getters / setter if no lombok
	if (!$properties->lombok) {
		foreach ($config->attributes as $field => $type) {
			$c[] = '';
			// Getter
			$c[] = $SP.'public function get'.ucfirst($field).'() {';
			$c[] = $SP.$SP.'return this.'.$field.';';
			$c[] = $SP.'}';

			// Setter
			$c[] = $SP.'public function set'.ucfirst($field).'('.ucfirst($type).' '.$field.') {';
			$c[] = $SP.$SP.'this.'.$field.' = '.$field.';';
			$c[] = $SP.'}';
		}
	}

	$c[] = '}';


	$finalContents = implode("\n", $c);
	file_put_contents($file, $finalContents);
	return $finalContents;
}

function constructMapper($file, $name, $config, $properties) {
	$SP = str_pad(' ', $properties->spaces);

	// Package
	$c = array();
	$c[] = 'package '.$properties->rootPackage.'.'.strtolower($properties->package).'.'.strtolower($name).';';
	$c[] = '';

	// Class declaration
	$c[] = 'public class '.$name.'Mapper {';

	$c[] = '}';

	$finalContents = implode("\n", $c);
	file_put_contents($file, $finalContents);
	return $finalContents;
}

function printInfos($skeleton) {
	e("============================================");
	e("- Use Mapstruct : ".($skeleton->props->mapstruct ? "true" : "false"));
	e("- Use Lombok    : ".($skeleton->props->lombok ? "true" : "false"));
	e("- RootPackage   : ".$skeleton->props->rootPackage);
	e("- Package       : ".$skeleton->props->package);
	e("============================================");
}

function e($message) {
	echo $message."\n";
}


?>