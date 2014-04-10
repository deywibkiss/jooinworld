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

jimport('joomla.form.formfield');

class JFormFieldJULayoutsettings extends JFormField {
		
	public $type = 'JULayoutsettings';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		return '';
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$document = JFactory::getDocument();
		//Define layoutsettings_holder varible to auto detect this layout field
		$script = "var layoutsettings_holder = '".$this->id."';";
		$document->addScriptDeclaration($script);
		
		$jversion = new JVersion();
		$jshortversion = $jversion->getShortVersion();
		if (version_compare($jshortversion, '3.0.0', 'ge')) {
			$field_class = 'joomla3ge';
		} else {
			$field_class = 'joomla25';
		}
		
		/*Return holder element for ajax result to put in it from layout choosing*/
		$html = "<div id=\"".$this->id."\" class=\"julayoutsettings ".$field_class."\"></div>";
		$html .= "<textarea name=\"".$this->name."\" class=\"julayoutsettings_json ".$this->element['class']."\" style=\"display: none;\">".$this->value."</textarea>";
		return $html;
	}

}
?>