<?php

final class Constructor {

	// Paths
	protected $pathPackage;
	protected $pathDomain;
	protected $pathRepository;
	protected $pathService;

	// Properties
	protected $files;
	protected $name;
	protected $config;
	protected $properties;
	protected $additionalImports;

	// All files buildable
	public const FileEntity = 'entity';
	public const FileDto = 'dto';
	public const FileMapper = 'mapper';
	public const FileMapperImpl = 'mapperImpl';
	public const FileRepository = 'repository';
	public const FileService = 'service';
	public const FileServiceImpl = 'serviceImpl';



	/**
	 * Constructor
	 * 
	 * @param string $path:
	 * @param array $files:
	 * @param string $name:
	 * @param stdClass $config:
	 * @param stdClass $properties:
	 * @param array $additionalImports
	 * @access public
	 */ 
	public function __construct($path, $files, $name, $config, $properties, $additionalImports) {
		$this->pathPackage = $path;
		$this->pathDomain = $path.'/'.strtolower($properties->package).'/'.strtolower($name);

		// Repository
		if (isset($properties->repositories) &&
			$properties->repositories->generate == true) {
			$this->pathRepository = $path.'/'.strtolower($properties->repositories->package);
		}
		// Service
		if (isset($properties->services) &&
			$properties->services->generate == true) {
			$this->pathService = $path.'/'.strtolower($properties->services->package);
		}

		$this->files = $files;
		$this->name = $name;
		$this->config = $config;
		$this->properties = $properties;
		$this->additionalImports = $additionalImports;
	}

	/**
	 * Creates the files
	 * 
	 * @access public
	 */ 
	public function createFiles() {
		$path = rtrim($this->pathDomain, '/').'/';
		$this->createDirIfNotExists($path);

		touch($path.$this->files[self::FileEntity]);
		touch($path.$this->files[self::FileDto]);
		touch($path.$this->files[self::FileMapper]);
		if (!$this->properties->mapstruct) {
			touch($path.$this->files[self::FileMapperImpl]);
		}
	}

	/**
	 * Generate the Entity file contents
	 * 
	 * @access public
	 */
	public function constructEntity() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->package).'.'.strtolower($this->name).';';
		$c[] = '';

		// Imports
		if ($this->properties->lombok) {
			$c[] = 'import lombok.Data;';
			$c[] = 'import lombok.AllArgsContructor;';
			$c[] = 'import lombok.NoArgsConstructor;';
			$c[] = '';
		}
		if (count($this->additionalImports) > 0) {
			foreach ($this->additionalImports as $import) {
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
		if ($this->properties->lombok) {
			$c[] = '@Data';
			$c[] = '@NoArgsConstructor';
			$c[] = '@AllArgsContructor';
		}

		// Class declaration
		$c[] = 'public class '.$this->name.' {';

		// Class attributes
		$primaryKey = $this->config->primaryKey;
		foreach ($this->config->attributes as $field => $type) {
			if ($field == $primaryKey) {
				$c[] = $SP.'@Id';
				$c[] = $SP.'@GeneratedValue(strategy = AUTO)';
			}
			$c[] = $SP.'private '.ucfirst($type).' '.$field.';';
		}

		// Constructors if lombok is disabled
		if (!$this->properties->lombok) {
			// No Args constructor
			$c[] = '';
			$c[] = $SP.'public '.$this->name.'() {';
			foreach($this->config->attributes as $field => $type) {
				$c[] = $SP.$SP.'this.'.$field.' = null;';
			}
			$c[] = $SP.'}';
			$c[] = '';

			// All Args constructor
			$args = array();
			foreach($this->config->attributes as $field => $type) {	
				$args[] = ucfirst($type).' '.$field;
			}
			$args = implode(', ', $args);
			$c[] = $SP.'public '.$this->name.'('.$args.') {';
			foreach($this->config->attributes as $field => $type) {
				$c[] = $SP.$SP.'this.'.$field.' = '.$field.';';
			}
			$c[] = $SP.'}';
		}

		$c[] = '}';


		$finalContents = implode("\n", $c);
		file_put_contents(rtrim($this->pathDomain, '/').'/'.$this->files[self::FileEntity], $finalContents);
		return $finalContents;
	}

	/**
	 * Generate the DTO file contents
	 * 
	 * @access public
	 */
	public function constructDto() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->package).'.'.strtolower($this->name).';';
		$c[] = '';

		// Imports
		if ($this->properties->lombok) {
			$c[] = 'import lombok.Getter;';
			$c[] = 'import lombok.Setter;';
			$c[] = '';
		}
		if (count($this->additionalImports) > 0) {
			foreach ($this->additionalImports as $import) {
				$c[] = $import;
			}
			$c[] = '';
		}

		// Before class annotations
		if ($this->properties->lombok) {
			$c[] = '@Getter';
			$c[] = '@Setter';
		}

		// Class declaration
		$c[] = 'public class '.$this->name.'DTO {';

		// Class attributes
		$primaryKey = $this->config->primaryKey;
		foreach ($this->config->attributes as $field => $type) {
			$c[] = $SP.'private '.ucfirst($type).' '.$field.';';
		}

		// Getters / setter if no lombok
		if (!$this->properties->lombok) {
			foreach ($this->config->attributes as $field => $type) {
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
		file_put_contents(rtrim($this->pathDomain, '/').'/'.$this->files[self::FileDto], $finalContents);
		return $finalContents;
	}

	/**
	 * Generate the Mapper file contents
	 * 
	 * @access public
	 */
	public function constructMapper() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->package).'.'.strtolower($this->name).';';
		$c[] = '';

		// Imports
		if ($this->properties->mapstruct) {
			$c[] = 'import org.mapstruct.Mapper;';
			$c[] = '';
		}
		$c[] = 'import java.util.List;';
		$c[] = '';

		// Class declaration
		if ($this->properties->mapstruct) {
			$c[] = '@Mapper';
		}
		$c[] = 'public interface '.$this->name.'Mapper {';
		$c[] = $SP.$this->name.'DTO '.strtolower($this->name).'ToDTO('.$this->name.' '.strtolower($this->name).');';
		$c[] = $SP.'List<'.$this->name.'DTO> map(List<'.$this->name.'> '.strtolower($this->name).'s);';
		$c[] = '}';

		$finalContents = implode("\n", $c);
		file_put_contents(rtrim($this->pathDomain, '/').'/'.$this->files[self::FileMapper], $finalContents);
		return $finalContents;
	}

	/**
	 * Generate the Mapper implementation file contents
	 * 
	 * @access public
	 */
	public function constructMapperImpl() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->package).'.'.strtolower($this->name).';';
		$c[] = '';

		// Imports
		$c[] = 'import java.util.List;';
		$c[] = 'import java.util.ArrayList;';
		$c[] = 'org.springframework.stereotype.Component;';
		$c[] = '';

		// Class declaration
		$c[] = '@Component';
		$c[] = 'public class '.$this->name.'MapperImpl implements RoleMapper {';
		$c[] = '';

		// entityToDTO() methods
		$c[] = $SP.'@Override';
		$c[] = $SP.'public '.$this->name.'DTO '.strtolower($this->name).'ToDTO('.$this->name.' '.strtolower($this->name).') {';
		$c[] = $SP.$SP.'if ('.strtolower($this->name).' == null) {';
		$c[] = $SP.$SP.$SP.'return null;';
		$c[] = $SP.$SP.'}';
		$c[] = '';
		$c[] = $SP.$SP.$this->name.'DTO '.strtolower($this->name).'DTO = new '.$this->name.'DTO();';
		$c[] = '';
		foreach ($this->config->attributes as $field => $type) {
			$c[] = $SP.$SP.strtolower($this->name).'DTO.set'.ucfirst($field).'('.strtolower($this->name).'.get'.ucfirst($field).'());';
		}
		$c[] = '';
		$c[] = $SP.$SP.'return '.strtolower($this->name).'DTO;';
		$c[] = $SP.'}';
		$c[] = '';

		// Map method
		$c[] = $SP.'@Override';
		$c[] = $SP.'public List<'.$this->name.'DTO> map(List<'.$this->name.'> '.strtolower($this->name).'s) {';
		$c[] = $SP.$SP.'if ('.strtolower($this->name).'s == null) {';
		$c[] = $SP.$SP.$SP.'return null;';
		$c[] = $SP.$SP.'}';
		$c[] = '';
		$c[] = $SP.$SP.'List<'.$this->name.'DTO> list = new ArrayList<'.$this->name.'DTO>('.strtolower($this->name).'s.size());';
		$c[] = $SP.$SP.'for ('.$this->name.' '.strtolower($this->name).' : '.strtolower($this->name).'s) {';
		$c[] = $SP.$SP.$SP.'list.add('.strtolower($this->name).'ToDTO('.strtolower($this->name).'));';
		$c[] = $SP.$SP.'}';
		$c[] = '';
		$c[] = $SP.$SP.'return list;';
		$c[] = $SP.'}';
		$c[] = '}';

		$finalContents = implode("\n", $c);
		file_put_contents(rtrim($this->pathDomain, '/').'/'.$this->files[self::FileMapperImpl], $finalContents);
		return $finalContents;
	}

	/**
	 * Construct the repository file for an entity
	 * 
	 * @access public
	 */ 
	public function createAndConstructRepository() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Check for repositories directory, create if not exists 
		$this->createDirIfNotExists($this->pathRepository);

		// Creates repository file
		touch($this->pathRepository.'/'.$this->files[Constructor::FileRepository]);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->repositories->package).';';
		$c[] = '';

		// Imports
		$c[] = 'import '.$this->properties->rootPackage.'.'.$this->properties->package.'.'.strtolower($this->name).'.'.ucfirst($this->name).';';
		$c[] = '';
		$c[] = 'import org.springframework.data.jpa.repository.JpaRepository;';
		$c[] = '';

		// Interface
		$primaryKeyType = ((array)$this->config->attributes)[$this->config->primaryKey];
		$c[] = 'public interface '.$this->name.'Repository extends JpaRepository<'.$this->name.', '.ucfirst($primaryKeyType).'> {';
		$c[] = $SP.$this->name.' findBy'.ucfirst($this->config->primaryKey).'('.ucfirst($primaryKeyType).' '.$this->config->primaryKey.');';
		$c[] = '}';

		$finalContents = implode("\n", $c);
		file_put_contents(rtrim($this->pathRepository, '/').'/'.$this->files[self::FileRepository], $finalContents);
		return $finalContents;
	}

	/**
	 * Construct the service files for an entity
	 * 
	 * @access public
	 */ 
	public function createAndConstructService() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Check for service directory, create if not exists 
		$this->createDirIfNotExists($this->pathService);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->services->package).';';
		$c[] = '';

		// Imports
		$c[] = 'import '.$this->properties->rootPackage.'.'.$this->properties->package.'.'.strtolower($this->name).'.'.ucfirst($this->name).';';
		$c[] = '';
		$c[] = 'import java.util.List;';
		$c[] = '';

		// Interface
		$c[] = 'public interface '.$this->name.'Service {';
		$c[] = $SP.'List<'.$this->name.'> get'.$this->name.'s();';

		$primaryKeyType = ((array)$this->config->attributes)[$this->config->primaryKey];
		$c[] = $SP.$this->name.' get'.$this->name.'('.ucfirst($primaryKeyType).' '.strtolower($this->config->primaryKey).');';

		$c[] = $SP.$this->name.' save'.$this->name.'('.$this->name.' '.strtolower($this->name).');';

		$c[] = $SP.'flush();';
		
		$c[] = '}';

		$finalContents = implode("\n", $c);
		file_put_contents(rtrim($this->pathService, '/').'/'.$this->files[self::FileService], $finalContents);
		return $finalContents;
	}

	/**
	 * Construct the service files for an entity
	 * 
	 * @access public
	 */ 
	public function createAndConstructServiceImpl() {
		$SP = str_pad(' ', $this->properties->spaces);

		// Check for service directory, create if not exists 
		$this->createDirIfNotExists($this->pathService);

		// Package
		$c = array();
		$c[] = 'package '.$this->properties->rootPackage.'.'.strtolower($this->properties->services->package).';';
		$c[] = '';

		// Imports
		$c[] = 'import '.$this->properties->rootPackage.'.'.$this->properties->package.'.'.strtolower($this->name).'.'.ucfirst($this->name).';';
		$c[] = 'import '.$this->properties->rootPackage.'.'.$this->properties->repositories->package.'.'.ucfirst($this->name).'Repository;';
		$c[] = '';
		if ($this->properties->lombok) {
			$c[] = 'import lombok.RequiredArgsConstructor;';
		}
		$c[] = 'import javax.transaction.Transactional;';
		$c[] = 'import java.util.List;';
		$c[] = '';

		// Class
		$c[] = '@Service';
		$c[] = '@Transactional';
		if ($this->properties->lombok) {
			$c[] = '@RequiredArgsConstructor';
		}
		$c[] = 'public class '.$this->name.'ServiceImpl implements '.$this->name.'Service {';
		$repositoryName = strtolower($this->name).'Repository';
		$c[] = $SP.'private final '.$this->name.'Repository '.$repositoryName.';';
		$c[] = '';

		// No lombok => we generate injection constructor
		if (!$this->properties->lombok) {
			$c[] = $SP.'public '.$this->name.'ServiceImpl('.$this->name.'Repository '.strtolower($this->name).'Repository) {';
			$c[] = $SP.$SP.'this.'.strtolower($this->name).'Repository = '.strtolower($this->name).'Repository;';
			$c[] = $SP.'}';
			$c[] = '';
		}

		// Generate methods
		$primaryKeyType = ((array)$this->config->attributes)[$this->config->primaryKey];

		$c[] = $SP.'@Override';
		$c[] = $SP.'public List<'.$this->name.'> get'.$this->name.'s() {';
		$c[] = $SP.$SP.'return '.$repositoryName.'.findAll();';
		$c[] = $SP.'}';
		$c[] = '';

		$c[] = $SP.'@Override';
		$c[] = $SP.'public '.$this->name.' get'.$this->name.'('.ucfirst($primaryKeyType).' '.$this->config->primaryKey.') {';
		$c[] = $SP.$SP.'return '.$repositoryName.'.findBy'.ucfirst($this->config->primaryKey).'('.$this->config->primaryKey.');';
		$c[] = $SP.'}';
		$c[] = '';

		$c[] = $SP.'@Override';
		$c[] = $SP.'public '.$this->name.' save'.$this->name.'('.$this->name.' '.strtolower($this->name).') {';
		$c[] = $SP.$SP.'return '.$repositoryName.'.save('.strtolower($this->name).');';
		$c[] = $SP.'}';
		$c[] = '';

		$c[] = $SP.'@Override';
		$c[] = $SP.'public void flush() {';
		$c[] = $SP.$SP.$repositoryName.'.deleteAll();';
		$c[] = $SP.'}';

		$c[] = '}';
		

		$finalContents = implode("\n", $c);
		file_put_contents(rtrim($this->pathService, '/').'/'.$this->files[self::FileServiceImpl], $finalContents);
		return $finalContents;
	}

	/**
	 * Create a directory only if not existing
	 * 
	 * @param string $path: The path of dir to create
	 * @access public
	 */
	public function createDirIfNotExists($path) {
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
	}
}


?>