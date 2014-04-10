<?php
/**
 * ------------------------------------------------------------------------
 * JU Backend Toolkit for Joomla 2.5/3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2010-2013 JoomUltra. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: JoomUltra Co., Ltd
 * Websites: http://www.joomultra.com
 * ------------------------------------------------------------------------
 */
 
defined('_JEXEC') or die( 'Restricted access' );

//Disable all error report that may break JSON result
error_reporting(0);

class juutilities{
	
	protected $params;
	protected $modulename;
	protected $activetemplate;
	
	function __construct($params){
		$this->params = $params;
		$this->modulename = self::getModuleName();
		$this->activetemplate = self::getActiveTemplate();
	}
	
	/**
	* Return module name
	*/
	protected function getModuleName() {
		$path = dirname(dirname(dirname(dirname(__FILE__))));
		return basename ($path);
	}
	
	 /**
	 * Get tamplate actived current
	 * @return string template name
	 */
	protected function getActiveTemplate() {
		$db = JFactory::getDBO();

		// Get the current default template
		$query = ' SELECT template '
				.' FROM #__template_styles '
				.' WHERE client_id = 0'
				.' AND home = 1 ';
		$db->setQuery($query);
		$template = $db->loadResult();

		return $template;
	}
	
	/**
	* Return ini file path in template
	*/
	protected function getIniFileTemplate($file_name = '', $template = ''){
		if(!$file_name){
			$file_name =  JRequest::getVar('file_name', '');
		}
		if(!$template){
			$template = self::getActiveTemplate(); 
		}
		$modulename = self::getModuleName();
		$path = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . $modulename;
		
		return $path . DS . $file_name . '.json';
	}
	
	/**
	* Return ini file path in module
	*/
	protected function getIniFileModule($file_name = ''){
		if(!$file_name){
			$file_name =  JRequest::getVar('file_name','');
		}
		$modulename = self::getModuleName();
		$path = JPATH_SITE . DS . 'modules' . DS . $modulename . DS . 'profiles';
		
		return $path . DS . $file_name . '.json';
	}
}
?>