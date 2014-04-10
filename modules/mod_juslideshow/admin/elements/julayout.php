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
jimport('joomla.form.formfield');

class JFormFieldJULayout extends JFormField {
		
	public $type = 'JULayout';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		
		$document = JFactory::getDocument();
		
		$document->addScript(JURI::root(true)."/modules/".$this->form->getValue('module')."/admin/js/julayout.min.js");
		$document->addScript(JURI::root(true)."/modules/".$this->form->getValue('module')."/admin/js/juutilities.min.js");
		
		//Define layout_field_name varible to auto detect this layout field
		$script = "var layout_field_name = '".$this->name."';
		
		//Load layout when page load
		JU_jQuery(document).ready(function($){
				loadLayout($('#".$this->id."'));
			}
		);
		";
		
		$document->addScriptDeclaration($script);
		
		if (version_compare(JVERSION, '3.0', 'ge')) {
			$field_class = 'joomla3ge';
		} else {
			$field_class = 'joomla25';
		}
		
		$html = '<div class="julayout '.$field_class.'">';
		$layouts_in_template_ori = $layouts_in_template_ext = $layouts_in_module_ori = $merged_layouts = array();
		
        $template = JUElementHelper::getActiveTemplate();
		
		$layouts = array();
		
		//Get all layouts in module
		$layout_path = JPATH_SITE . DS . 'modules' . DS . $this->form->getValue('module') . DS . 'tmpl';
		$folders = glob($layout_path.DS.'*', GLOB_ONLYDIR);
		if(count($folders)) {
			foreach ($folders AS $key=>$folder){
				$layout =  basename($folder);
				$layouts[$layout] = $layout;
			}
		}
		
		//Get all layouts in template, layouts in template has higher priority, so it will overwrite layout in module if they have the same folder name
        $layout_path = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . $this->form->getValue('module');
		$folders = glob($layout_path.DS.'*', GLOB_ONLYDIR);
		if(count($folders)) {
			foreach ($folders AS $key=>$folder){
				$layout =  basename($folder);
				$layouts[$layout] = $layout ." [".JText::_("Template")."]";
			}
		}
		
		//Sort layouts by alphabet
		asort($layouts);
		
		$html .= JHTML::_('select.genericlist', $layouts, $this->name, 'class="'.$this->element['class'].'" onchange="loadLayout(this);"', 'id', 'title', $this->value, $this->id);
		$html .= "<span class=\"layout-loader\"></span>";
		$html .= "</div>";
		return $html;
	}

}
?>