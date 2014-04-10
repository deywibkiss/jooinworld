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

// no direct access
defined('_JEXEC') or die ;
require_once dirname(__FILE__). '/juelementhelper.php';
JImport('joomla.form.formfield');
JImport('joomla.filesystem.folder');

class JFormFieldJUProfile extends JFormField {
	
	public $type = 'JUProfile';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$profiles = array();
		
		$document = JFactory::getDocument();
		
		//Add language strings to use in .js files
		JText::script('TEMPLATE');
		JText::script('ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_PROFILE');
		JText::script('NEW_PROFILE_NAME');
		JText::script('PLEASE_ENTER_PROFILE_NAME');
		JText::script('PLEASE_SELECT_PROFILE');
		JText::script('LAYOUT_HOLDER_DOES_NOT_EXIST');
		JText::script('INVALID_FORM');
		
  		$document->addScript(JURI::Root()."modules/".$this->form->getValue('module')."/admin/js/juprofile.min.js");
		$document->addScript(JURI::Root()."modules/".$this->form->getValue('module')."/admin/js/juutilities.min.js");
		
		//Define some profile varibles to call it in javascript files
		$script = "
		var profile_field_name 	= '".$this->name."';
		";
		$document->addScriptDeclaration($script);
		
		//Field class to format css depends J2.5 or J3.0
		if (version_compare(JVERSION, '3.0', 'ge')) {
			$field_class = 'joomla3ge';
		} else {
			$field_class = 'joomla25';
		}
		
		$html = '<div class="juprofile '.$field_class." ".$this->element['class'].'">';
		
        $template = JUElementHelper::getActiveTemplate();

		//Get all profiles in template
        $path = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . $this->form->getValue('module');
        if (JFolder::exists($path)) {
            $files = JFolder::files($path, '.json');
            if ($files) {
                foreach ($files as $fname) {
                    $fname = basename($fname, ".json");
                    $profiles[$fname] = $fname." [".JText::_("Template")."]";
                }
            }
        }
		
		//Get all profiles in module, profiles in module has higher priority, so it will overwrite layout in template if they have the same folder name
		//If profile in template is saved, it will not save to template profile but save to module with the same name, and new profile in module will overwrite template profile after it's saved
        $path = JPATH_SITE . DS . 'modules' . DS . $this->form->getValue('module') . DS . 'profiles';
		
		if (JFolder::exists($path)) {
			$files = JFolder::files($path, '.json');
			if ($files) {
				foreach ($files as $fname) {
					$fname = basename($fname, ".json");
					$profiles[$fname] = $fname;
				}
			}
		} else {
			//Profiles folder in module must be exist
			return JText::_('PROFILE_FOLDER_DOES_NOT_EXIST');
		}
		
		//Sort profiles by alphabet
		asort($profiles);
		
		$profiles = array_merge(array(""=>JText::_('SELECT_PROFILE')), $profiles);
		
		//Reload image gallery after loadProfile completed
		$javascript = 'function jureloadimages(){ jQuery(".jureloadimages").trigger("click"); }';
		$document->addScriptDeclaration($javascript);
		
		$html .= JHTML::_('select.genericlist', $profiles, $this->name, '', 'id', 'title', '', $this->id);
		$html .= "<a class='action-load' title='".JText::_('LOAD_THIS_PROFILE')."' href='#' onclick='loadProfile(this, jureloadimages); return false;'>".JText::_('Load')."</a>";
		$html .= "<a class='action-save' title='".JText::_('SAVE_TO_THIS_PROFILE')."' href='#' onclick='saveProfile(this); return false;'>".JText::_('Save')."</a>";
		$html .= "<a class='action-clone' title='".JText::_('CLONE_THIS_PROFILE')."' href='#' onclick='cloneProfile(this); return false;'>".JText::_('Clone')."</a>";
		$html .= "<a class='action-delete' title='".JText::_('DELETE_THIS_PROFILE')."' href='#' onclick='deleteProfile(this); return false;'>".JText::_('Delete')."</a>";
		$html .= '<span class="profile-loader"></span>';
		$html .= "</div>";
		return $html;
	}

}
?>