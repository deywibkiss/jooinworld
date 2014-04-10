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

jimport('joomla.form.formfield');


class JFormFieldJUGallery extends JFormField {
    protected $type = 'JUGallery';
    
    protected function getInput() {
	
		Jhtml::_('behavior.modal');
		
		$document = JFactory::getDocument();
		
		$document->addScript(JURI::root(true) . '/modules/' . $this->form->getValue('module') . '/admin/js/jquery.dragsort.min.js');
		$document->addScript(JURI::root(true) . '/modules/' . $this->form->getValue('module') . '/admin/js/juupload.min.js');
		$document->addScript(JURI::root(true) . '/modules/' . $this->form->getValue('module') . '/admin/js/jugallery.min.js');
		
		JText::script('TITLE');
		JText::script('LINK');
		JText::script('CLASS');
		JText::script('DESCRIPTION');
		JText::script('PUBLISHED');
		JText::script('UPDATE');
		JText::script('CANCEL');
		JText::script('DELETE_IMAGE_CONFIRM');
		JText::script('FOLDER_PATH_REQUIRED');
		JText::script('FOLDER_EMPTY');
		JText::script('PLEASE_SELECT_FILE');
		JText::script('SELECT_FILE');
		
		$params = new JRegistry($this->form->getValue('params'));
		
		$folderfield = (string)$this->element['folderfield'];
		
		$foldername = $params->get($folderfield, 'images/');
		
		if(substr($foldername, 0, 1) == '/'){
			$params->set($folderfield, substr($foldername, 1));
		}
		if(substr(trim($foldername), -1) != '/'){
			$params->set($folderfield, trim($foldername) . '/');
		}
	
		//Create element
		$html = '<div class="juuploader"><div class="file-display">'.JText::_("SELECT_FILE").'</div><input class="upload-file" type="file" name="'.$this->id.'_img_upload" id="'.$this->id.'_img_upload" multiple  accept="image/*" /><button type="button" class="btn-upload">'.JText::_("UPLOAD").'</button></div>';
		$html .= '<input type="button" class="jureloadimages" title="'.JText::_("RELOAD_IMAGES").'" value="'.JText::_("RELOAD_IMAGES").'" style="display: block;" />';
		$html .= '<div class="upload-message"></div>';
		$html .= '<div class="jugallery-holder"></div>';
		$html .= '<textarea rows="6" cols="60" class="jugallery-description '.$this->element['class'].'" name="' . $this->name . '" id="' . $this->id . '" style="display: none;">'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .'</textarea>';
		
		$js = '
		var module_url = "'.JURI::root(true).'/modules/'.$this->form->getValue('module').'/";
		JU_jQuery(document).ready(function($){
			$(\'#'.$this->id.'\').jugallery({ju_folderfield: \'jform_params_'.$folderfield.'\', description_field_id:\''.$this->id.'\'});
			$(\'#'.$this->id.'\').parent().find(\'.juuploader\').juupload({ju_folderfield: \'jform_params_'.$folderfield.'\', description_field_id:\''.$this->id.'\'});
		});';

		$document->addScriptDeclaration($js);
		
		return $html;
    }	
}