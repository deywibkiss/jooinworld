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

require_once( dirname(__FILE__) . '/juutilities.php' );

class juprofile extends juutilities {
	
	/**
	* Return json of profile data and layout settings html
	*/
	public function getProfileData(){
		
		require_once( dirname(__FILE__) . '/julayout.php' );
		$julayout = new julayout($this->params);
		
		$ini_data = '';
		$file_in_module = self::getIniFileModule();
		$file_in_template =	self::getIniFileTemplate();
		//If both module and tempale have the same profile, profile in module has higher priority
		if(JFile::exists($file_in_module)){
			$ini_data =  JFile::read($file_in_module); 
			$data['success'] = 1;
		} elseif (JFile::exists($file_in_template)){
			$ini_data =  JFile::read($file_in_template);
			$data['success'] = 1;
		}else {
			//Profile does not exist
			$data['success'] = 0;
			$data['reports'] = JText::_('PROFILE_DOES_NOT_EXIST');
			return $data;
		}
		
		$ini_data = json_decode($ini_data);
		if($ini_data===NULL){
			//Wrong JSON string, can not decode
			$data['success'] = 0;
			$data['reports'] = JText::_('PROFILE_DATA_IS_INVALID');
			return $data;
		}
		
		$ini_layout_field_name = JRequest::getVar('julayout','layout');
		$layout = $ini_data->$ini_layout_field_name;
		
		if($data['success']) {
			$data['reports'] = JText::_('LOAD_PROFILE_SUCCESSFULLY');
			//Profile data
			$data['ini_data'] = $ini_data;
			//Layout setting html
			$layoutsetting_arr = $julayout->generateLayoutSettings($layout);
			$data['layoutsetting'] = $layoutsetting_arr['layoutsetting'];
		}
		return $data;
	}
	
	/**
	* Save Profile to module folder
	*/
	public function saveProfile(){
		
		//Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		
		//Do NOT filter, HTML can be saved in profile
		$data_json = JRequest::getVar('data_json', '', 'post', 'string', JREQUEST_ALLOWRAW);
		
		//Create profile folder if does not exist
		$moduleprofilefolder = JPATH_SITE . DS . 'modules' . DS . $this->modulename . DS . 'profiles';
		if (!is_dir($moduleprofilefolder)) {
			mkdir($moduleprofilefolder);      
			//Create blank index.html file
			$fp = fopen($moduleprofilefolder . DS . 'index.html', 'w');
			fwrite($fp, '<!DOCTYPE html><title></title>');
			fclose($fp);
		}
		
		$file = self::getIniFileModule();
		
		if (JFile::exists($file)) {
			chmod($file, 0777);
		}
		
		$data = array();
		if (JFile::write($file, $data_json)){
			$data['success'] = 1;
			$data['reports'] = JText::_('SAVE_PROFILE_SUCCESSFULLY');
		} else {
			$data['success'] = 0;
			$data['reports'] = JText::_('FILE_UNWRITEABLE');
		}
		
		// Try to make the params file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			$data['success'] = 0;
			$data['reports'] = JText::_('FILE_UNWRITEABLE');
		}
		return $data;
	}
	
	/**
	* Clone Profile
	*/
	public function cloneProfile(){
		$new_file_name = JRequest::getVar('new_file_name');
		//New file will be created in module
		$new_file_in_module = self::getIniFileModule($new_file_name); 
		
		$ori_file_in_module = self::getIniFileModule(); 
		
		$new_file_in_template = self::getIniFileTemplate($new_file_name);
		$ori_file_in_template = self::getIniFileTemplate();

		//If new file is already exist in module OR template => File already exist
		if (JFile::exists($new_file_in_module) || JFile::exists($new_file_in_template)){
			$data['success'] = 0;
			$data['reports'] = JText::_('THIS_PROFILE_NAME_ALREADY_EXIST');
		//If new file is already exist in module => Copy ori file from module folder to new file
		}elseif(JFile::exists($ori_file_in_module)){
			if (JFile::copy($ori_file_in_module, $new_file_in_module)){
				$data['success'] = 1;
				$data['reports'] = JText::_('CLONE_PROFILE_SUCCESSFULLY');
			}else{
				$data['success'] = 0;
				$data['reports'] = JText::_('FAILED_TO_CLONE_PROFILE');
			}
		//If new file is already exist in template => Copy ori file from template folder to new file
		}elseif(JFile::exists($ori_file_in_template)){
			 if (JFile::copy($ori_file_in_template, $new_file_in_module)){
				$data['success'] = 1;
				$data['reports'] = JText::_('CLONE_PROFILE_SUCCESSFULLY');
			}else{
				$data['success'] = 0;
				$data['reports'] = JText::_('FAILED_TO_CLONE_PROFILE');
			}
		//Don't have original file
		}else{
			$data['success'] = 0;
			$data['reports'] = JText::_('FAILED_TO_CLONE_PROFILE');
		}
		return $data;
	}
	
	/**
	* Delete Profile
	*/
	public function deleteProfile() {
		$file_name = JRequest::getVar('file_name', '');
		$file_in_module = self::getIniFileModule();
		$file_in_template = self::getIniFileTemplate();
		
		//File is exist in both template and module => Delete file in module then ajax add [Template] to current profile select option
		if (JFile::exists($file_in_template) && JFile::exists($file_in_module)){ 
			if (JFile::delete($file_in_module)){
				$data['success'] = 1;
				$data['reports'] = JText::_('DELETE_PROFILE_SUCCESSFULLY_FROM_MODULE');
				$data['file_name'] = $file_name.' ['.JText::_('TEMPLATE').']';
			}else{
				$data['success'] = 0;
				$data['reports'] = JText::_('CAN_NOT_DELETE_PROFILE');
			}
		//File is exist in module but not template => Delete file in module then ajax delete current profile select option
		}elseif(!JFile::exists($file_in_template) && JFile::exists($file_in_module)) {	
			if(JFile::delete($file_in_module)){
				$data['success'] = 1;
				$data['reports'] = JText::_('DELETE_PROFILE_SUCCESSFULLY_FROM_MODULE');
				$data['file_name'] = '';
			}else{
				$data['success'] = 0;
				$data['reports'] = JText::_('CAN_NOT_DELETE_PROFILE');
			}
		//Do NOT delete profile in template
		}else{
			$data['success'] = 0;
			$data['reports'] = JText::_('TEMPLATE_PROFILE_CAN_NOT_BE_DETETED');
		}
		return $data;
	}
}
?>