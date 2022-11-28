<?php

define('C', '*');

final class Commenter {

	public const APUBLIC = 'public';
	public const APROTECTED = 'protected';
	public const APRIVATE = 'private';

	protected const LINE_LENGTH = 24;

	//
	// Utils
	//

	public static function functionBlock($functionDescription, 
								  		 $parameters,
								  		 $return,
								  		 $access,
								  		 $linePrefix = '') {
		$comment = array(
			$linePrefix.self::openTag(),
			$linePrefix.self::header($functionDescription),
			$linePrefix.self::emptyLine()
		);
		if (isset($parameters) && is_array($parameters)) {
			foreach ($parameters as $param => $desc) {
				$comment[] = $linePrefix.self::param($param, $desc);
			}
		}
		if (isset($return) && !empty($return)) {
			$comment[] = $linePrefix.self::return($return);
		}
		$comment[] = $linePrefix.self::access($access);
		$comment[] = $linePrefix.self::closeTag();
		return implode("\n", $comment);
	}

	public static function printFunctionBlock($functionDescription, 
								  	   		  $parameters,
								 	   		  $return,
								  	   		  $access,
								  	   		  $linePrefix = '') {
		echo self::functionBlock($functionDescription,
								 $parameters,
								 $return,
								 $access,
								 $linePrefix);
	}

	public static function classBlock($classDescription,
									  $implements = null,
									  $extends = null,
									  $linePrefix = '') {
		$comment = array(
			$linePrefix.self::openTag(),
			$linePrefix.self::header($classDescription),
			$linePrefix.self::emptyLine()
		);
		if (isset($implements) && $implements != '') {
			$comment[] = self::emptyLine().'@implements '.$implements;
		}
		if (isset($extends) && $extends != '') {
			$comment[] = self::emptyLine().'@extends '.$extends;
		}
		$comment[] = self::closeTag();

		return implode("\n", $comment);
	}

	public static function printClassBlock($classDescription,
										   $implements = null,
										   $extends = null,
										   $linePrefix = '') {
		echo self::classBlock($classDescription,
							  $implements,
							  $extends,
							  $linePrefix);
	}


	//
	// Generics
	//


	public static function openTag() {
		return '/'.C.C;
	}

	public static function closeTag() {
		return ' '.C.'/';
	}

	public static function emptyLine() {
		return ' '.C.' ';
	}

	public static function fullLine() {
		return ' '.str_pad(C, self::LINE_LENGTH);
	}



	//
	// Elements
	//

	public static function header($message) {
		return self::emptyLine().$message;
	}

	public static function param($type, $description) {
		return self::emptyLine().'@param '.$type.' '.$description;
	}

	public static function return($description) {
		return self::emptyLine().'@return '.$description;
	}

	public static function access($access) {
		return self::emptyLine().'@access '.$access;
	}

	public static function accessPublic() {
		return self::access(self::APUBLIC);
	}
	public static function accessProtected() {
		return self::access(self::APROTECTED);
	}
	public static function accessPrivate() {
		return self::access(self::APRIVATE);
	}

}


?>