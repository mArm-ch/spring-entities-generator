<?php

final class Constructor {

	protected $path;
	protected $files;
	protected $name;
	protected $config;
	protected $properties;
	protected $additionalImports;

	public const FileEntity = 'entity';
	public const FileDto = 'dto';
	public const FileMapper = 'mapper';
	public const FileImpl = 'impl';

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
		$this->path = $path;
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
		$path = rtrim($this->path, '/').'/';
		touch($path.$this->files[self::FileEntity]);
		touch($path.$this->files[self::FileDto]);
		touch($path.$this->files[self::FileMapper]);
		if (!$this->properties->mapstruct) {
			touch($path.$this->files[self::FileImpl]);
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
			$c[] = $SP.'public '.$name.'() {';
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
		file_put_contents(rtrim($this->path, '/').'/'.$this->files[self::FileEntity], $finalContents);
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
		file_put_contents(rtrim($this->path, '/').'/'.$this->files[self::FileDto], $finalContents);
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
		file_put_contents(rtrim($this->path, '/').'/'.$this->files[self::FileMapper], $finalContents);
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
		$c[] = $SP.$SP.$name.'DTO '.strtolower($this->name).'DTO = new '.$this->name.'DTO();';
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
		file_put_contents(rtrim($this->path, '/').'/'.$this->files[self::FileImpl], $finalContents);
		return $finalContents;
	}
}


?>