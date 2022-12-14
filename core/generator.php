<?php

final class SpringGenerator {
	
	protected $rootFolder;
	protected $definitionFile;
	protected $definition;
	protected $properties;
	protected $entities;
	protected $entitiesClasses;
	protected $packagePath;

	/**
	 * Constructor
	 * 
	 * @param string $definitionFile: The path to the definition file
	 * @access publicentitiesClasses
	 */ 
	public function __construct($definitionFile, $rootFolder) {
		$this->rootFolder = $rootFolder;
		$this->definitionFile = $definitionFile;
		$this->printHeader();
		$this->loadDefinitionFile();
		$this->checkDefinition();
		$this->printInfos();
	}

	public function printHeader() {
		H::e("============================================");
		H::e("= Spring Boot entities generator");
		H::e("= ");
		H::e("= Author  : David Ansermot");
		H::e("= Version : 1.2.0");
		H::e("=");
		H::e("= Github  : https://github.com/mArm-ch/spring-entities-generator");
		H::e("============================================");
	}

	/**
	 * Print some infos in the console
	 */
	public function printInfos() {
		H::e("- Use Mapstruct         : ".($this->definition->props->mapstruct == true ? "true" : "false"));
		H::e("- Use Lombok            : ".($this->definition->props->lombok == true ? "true" : "false"));
		H::e("- Root package          : ".$this->definition->props->rootPackage);
		H::e("- Domain package        : ".$this->definition->props->package);

		if (isset($this->definition->props->repositories) && $this->definition->props->repositories->generate) {
			H::e("- Generate repositories : true");
			H::e("- Repository package    : ".$this->definition->props->repositories->package);
		} else {
			H::e("- Generate repositories : false");
		}

		if (isset($this->definition->props->services) && $this->definition->props->services->generate) {
			H::e("- Generate services     : true");
			H::e("- Service package       : ".$this->definition->props->services->package);
		} else {
			H::e("- Generate sersvice     : false");
		}
		H::e("============================================");
	}


	/**
	 * Generates the files/contents 
	 * 
	 * @access public
	 */
	public function generate() {

		H::e("Begin generation...");	
		foreach ($this->entities as $entityName => $entityConfig) {
						
			H::e("Generating '".$entityName."'");

			$additionalImports = $this->scanForEntitiesUse($this->entitiesClasses, $entityName, $entityConfig, $this->properties);

			// Creates files for entity
			$files = array(
				Constructor::FileEntity => $entityName.'.java',
				Constructor::FileDto => $entityName.'DTO.java',				
				Constructor::FileMapper => $entityName.'Mapper.java',
				Constructor::FileMapperImpl => $entityName.'MapperImpl.java',
				Constructor::FileRepository => $entityName.'Repository.java',
				Constructor::FileService => $entityName.'Service.java',
				Constructor::FileServiceImpl => $entityName.'ServiceImpl.java'
			);

			// Create files
			$constructor = new Constructor($this->packagePath, $files, $entityName, $entityConfig, $this->properties, $additionalImports);
			$constructor->createFiles();

			// Create contents and update files
			H::e("- Package : ".$this->properties->package);
			H::e("-- File : ".$files[Constructor::FileEntity]);
			$constructor->constructEntity();
			H::e("-- File : ".$files[Constructor::FileDto]);
			$constructor->constructDto();
			H::e("-- File : ".$files[Constructor::FileMapper]);
			$constructor->constructMapper();
			if (!$this->properties->mapstruct) {
				H::e("-- File : ".$files[Constructor::FileMapperImpl]);
				$constructor->constructMapperImpl();
			}

			// Repositories generation
			if (isset($this->properties->repositories) &&
				$this->properties->repositories->generate == true) {
				if (($this->properties->repositories->all == false && $entityConfig->repository == true) ||
					$this->properties->repositories->all == true) {
					H::e("- Package : ".$this->properties->repositories->package);
					H::e("-- File : ".$files[Constructor::FileRepository]);
					$constructor->createAndConstructRepository();
				}
			}

			// Services generation
			if (isset($this->properties->services) &&
				$this->properties->services->generate == true) {
				if (($this->properties->services->all == false && $entityConfig->service == true) ||
					$this->properties->services->all == true) {
					H::e("- Package : ".$this->properties->services->package);
					H::e("-- File : ".$files[Constructor::FileService]);
					$constructor->createAndConstructService();
					H::e("-- File : ".$files[Constructor::FileServiceImpl]);
					$constructor->createAndConstructServiceImpl();
				}
			}

			H::e("");
		}


		H::e("Generation done. Thanks for using this tool.");
	}

	/**
	 * Loads the definition file
	 * 
	 * @access protected
	 */ 
	protected function loadDefinitionFile() {
		$definition = file_get_contents($this->definitionFile);
		if (substr($this->definitionFile, -5) == '.json') {
			$this->definition = json_decode($definition, false);
		} else if (substr($this->definitionFile, -5) == '.yaml') {
			$yaml = yaml_parse($definition);
			$this->definition = json_decode(json_encode($yaml), false);
		} else if (substr($this->definitionFile, -4) == '.xml') {
			$xml = simplexml_load_file($this->definitionFile);
			$this->definition = json_decode(json_encode($xml), false);
		}
		if ($this->definition === null) {
			throw new Exception('Unable to load definition file :'.$this->definitionFile);
		}
	}

	/**
	 * Do some check and parsing on definition contents
	 * 
	 * @access protected
	 */ 
	protected function checkDefinition() {
		if (!isset($this->definition->props->mapstruct) ||
			!isset($this->definition->props->lombok) ||
			!isset($this->definition->props->rootPackage) ||
			!isset($this->definition->props->package) ||
			!isset($this->definition->entities)) {
			throw new Exception('Missing mandatory fields in definition file :'.$this->definitionFile);
		}

		H::e("Initialization...");
		$this->properties = $this->definition->props;
		$this->entities = $this->definition->entities;

		// Final package path
		$pRoot = $this->rootFolder;
		$pOut = $pRoot.'output/'.time().'/';
		$this->packagePath = $pOut.$this->properties->rootPackage;
		mkdir($this->packagePath, 0777, true);
		H::e("- Output folder will be ".$pOut);

		// Scan entities to get their classnames
		$this->entitiesClasses = array_map('strtolower', array_keys((array)$this->entities));
		H::e("- Found entities : ".implode(', ', $this->entitiesClasses));
	}

	/**
	 * Scan the entity config to find use of custom entities
	 * 
	 * @param array $existingEntities: A list of existing entities
	 * @param string $name: The name of the entity to find
	 * @param stdClass $config: The configuration of the entity
	 * @param stdClass $properties: The properties of the generator
	 * @access protected
	 */
	protected function scanForEntitiesUse($existingEntities, $name, $config, $properties) {
		$imports = array();
		foreach($config->attributes as $field => $type) {
			if (in_array($type, $existingEntities) && $type != strtolower($name)) {
				$imports[] = 'import '.$properties->rootPackage.'.'.$properties->package.'.'.strtolower($type).'.'.ucfirst($type).';';
			}
		}
		return $imports;
	}
}

?>