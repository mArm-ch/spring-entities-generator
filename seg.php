<?php
if ($argc < 2) {
	die("Missing definition file");
}
$definitionFile = $argv[1];
if (!file_exists($definitionFile)) {
	die("Cannot load definition file at path : ".$definitionFile);
}
generate($definitionFile);

/* ----------------------------------------------------------------------------- */

/**
 * Generate all the stuff requested
 */
function generate($definitionFile) {

	$debug = false;
	$skeleton = null;

	$definition = file_get_contents($definitionFile);
	if (substr($definitionFile, -5) == '.json') {
		$skeleton = json_decode($definition, false);
	} else if (substr($definitionFile, -5) == '.yaml') {
		$yaml = yaml_parse($definition);
		$skeleton = json_decode(json_encode($yaml), false);
	} else if (substr($definitionFile, -4) == '.xml') {
		$xml = simplexml_load_file($definitionFile);
		$skeleton = json_decode(json_encode($xml), false);
	}
	if ($skeleton === null) {
		die('Error reading definiton file '.$definitionFile);
	}

	if (!isset($skeleton->props->mapstruct) ||
		!isset($skeleton->props->lombok) ||
		!isset($skeleton->props->rootPackage) ||
		!isset($skeleton->props->package) ||
		!isset($skeleton->entities)) {
		die('Missing mandatory fields');
	}

	e("Initializion...");
	$props = $skeleton->props;
	$entities = $skeleton->entities;

	$pRoot = dirname(__FILE__).'/';
	$pOut = $pRoot.'output/'.time().'/';
	$packagePath = $pOut.$props->rootPackage.'/'.strtolower($props->package);
	mkdir($packagePath, 0777, true);
	e("- Output folder will be ".$pOut);

	$existingEntities = array_map('strtolower', array_keys((array)$entities));
	e("- Found entities : ".implode(', ', $existingEntities));

	e("Begin generation...");	
	foreach ($entities as $entityName => $entityConfig) {
		$dirPath = $packagePath.'/'.strtolower($entityName);
		mkdir($dirPath, 0777, true);
		e("Generating '".$entityName."'");


		$additionalImports = scanForEntitiesUse($existingEntities, $entityName, $entityConfig, $props);


		// Creates files for entity
		$fEntity = $entityName.'.java';
		$fDto = $entityName.'DTO.java';
		$fMapper = $entityName.'Mapper.java';
		$fMapperImpl = $entityName.'MapperImpl.java';

		touch($dirPath.'/'.$fEntity);
		touch($dirPath.'/'.$fDto);
		touch($dirPath.'/'.$fMapper);
		if (!$props->mapstruct) {
			touch($dirPath.'/'.$fMapperImpl);
		}

		// Construct contents and save
		e("- File : ".$fEntity);
		$c = constructEntity($dirPath.'/'.$fEntity, $entityName, $entityConfig, $props, $additionalImports);
		if ($debug) { e($c); e(""); }
		e("- File : ".$fDto);
		$c = constructDto($dirPath.'/'.$fDto, $entityName, $entityConfig, $props, $additionalImports);
		if ($debug) { e($c); e(""); }
		e("- File : ".$fMapper);
		$c = constructMapper($dirPath.'/'.$fMapper, $entityName, $entityConfig, $props);
		if ($debug) { e($c); e(""); }
		if (!$props->mapstruct) {
			e("- File : ".$fMapperImpl);
			$c = constructMapperImpl($dirPath.'/'.$fMapperImpl, $entityName, $entityConfig, $props);
			if ($debug) { e($c); e(""); }
		}
	}
}


/**
 * Generate the Entity file contents
 */
function constructEntity($file, $name, $config, $properties, $additionalImports) {
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
	if (count($additionalImports) > 0) {
		foreach ($additionalImports as $import) {
			$c[] = $import;
		}
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
 * Generate the DTO file contents
 */
function constructDto($file, $name, $config, $properties, $additionalImports) {
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
	if (count($additionalImports) > 0) {
		foreach ($additionalImports as $import) {
			$c[] = $import;
		}
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

/**
 * Generate the Mapper file contents
 */
function constructMapper($file, $name, $config, $properties) {
	$SP = str_pad(' ', $properties->spaces);

	// Package
	$c = array();
	$c[] = 'package '.$properties->rootPackage.'.'.strtolower($properties->package).'.'.strtolower($name).';';
	$c[] = '';

	// Imports
	if ($properties->mapstruct) {
		$c[] = 'import org.mapstruct.Mapper;';
		$c[] = '';
	}
	$c[] = 'import java.util.List;';
	$c[] = '';

	// Class declaration
	if ($properties->mapstruct) {
		$c[] = '@Mapper';
	}
	$c[] = 'public interface '.$name.'Mapper {';
	$c[] = $SP.$name.'DTO '.strtolower($name).'ToDTO('.$name.' '.strtolower($name).');';
	$c[] = $SP.'List<'.$name.'DTO> map(List<'.$name.'> '.strtolower($name).'s);';
	$c[] = '}';

	$finalContents = implode("\n", $c);
	file_put_contents($file, $finalContents);
	return $finalContents;
}

/**
 * Generate the Mapper implementation file contents
 */
function constructMapperImpl($file, $name, $config, $properties) {
	$SP = str_pad(' ', $properties->spaces);

	// Package
	$c = array();
	$c[] = 'package '.$properties->rootPackage.'.'.strtolower($properties->package).'.'.strtolower($name).';';
	$c[] = '';

	// Imports
	$c[] = 'import java.util.List;';
	$c[] = 'import java.util.ArrayList;';
	$c[] = 'org.springframework.stereotype.Component;';
	$c[] = '';

	// Class declaration
	$c[] = '@Component';
	$c[] = 'public class '.$name.'MapperImpl implements RoleMapper {';
	$c[] = '';

	// entityToDTO() methods
	$c[] = $SP.'@Override';
	$c[] = $SP.'public '.$name.'DTO '.strtolower($name).'ToDTO('.$name.' '.strtolower($name).') {';
	$c[] = $SP.$SP.'if ('.strtolower($name).' == null) {';
	$c[] = $SP.$SP.$SP.'return null;';
	$c[] = $SP.$SP.'}';
	$c[] = '';
	$c[] = $SP.$SP.$name.'DTO '.strtolower($name).'DTO = new '.$name.'DTO();';
	$c[] = '';
	foreach ($config->attributes as $field => $type) {
		$c[] = $SP.$SP.strtolower($name).'DTO.set'.ucfirst($field).'('.strtolower($name).'.get'.ucfirst($field).'());';
	}
	$c[] = '';
	$c[] = $SP.$SP.'return '.strtolower($name).'DTO;';
	$c[] = $SP.'}';
	$c[] = '';

	// Map method
	$c[] = $SP.'@Override';
	$c[] = $SP.'public List<'.$name.'DTO> map(List<'.$name.'> '.strtolower($name).'s) {';
	$c[] = $SP.$SP.'if ('.strtolower($name).'s == null) {';
	$c[] = $SP.$SP.$SP.'return null;';
	$c[] = $SP.$SP.'}';
	$c[] = '';
	$c[] = $SP.$SP.'List<'.$name.'DTO> list = new ArrayList<'.$name.'DTO>('.strtolower($name).'s.size());';
	$c[] = $SP.$SP.'for ('.$name.' '.strtolower($name).' : '.strtolower($name).'s) {';
	$c[] = $SP.$SP.$SP.'list.add('.strtolower($name).'ToDTO('.strtolower($name).'));';
	$c[] = $SP.$SP.'}';
	$c[] = '';
	$c[] = $SP.$SP.'return list;';
	$c[] = $SP.'}';
	$c[] = '}';

	$finalContents = implode("\n", $c);
	file_put_contents($file, $finalContents);
	return $finalContents;
}

/**
 * Scan the entity config to find use of custom entities
 */
function scanForEntitiesUse($existingEntities, $name, $config, $properties) {
	$imports = array();
	foreach($config->attributes as $field => $type) {
		if (in_array($type, $existingEntities) && $type != strtolower($name)) {
			$imports[] = 'import '.$properties->rootPackage.'.'.$properties->package.'.'.strtolower($type).';';
		}
	}
	return $imports;
}



/**
 * Print some infos in the console
 */
function printInfos($skeleton) {
	e("============================================");
	e("= Spring Boot entities generator")
	e("= ");
	e("= Author  : David Ansermot");
	e("= Version : 1.1.0");
	e("=");
	e("= Github  : https://github.com/mArm-ch/spring-entities-generator");
	e("============================================");
	e("- Use Mapstruct : ".($skeleton->props->mapstruct ? "true" : "false"));
	e("- Use Lombok    : ".($skeleton->props->lombok ? "true" : "false"));
	e("- RootPackage   : ".$skeleton->props->rootPackage);
	e("- Package       : ".$skeleton->props->package);
	e("============================================");
}

/**
 * Print a line in the console
 */
function e($message) {
	echo $message."\n";
}


?>