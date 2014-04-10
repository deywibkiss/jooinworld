<?php
/**
 * @version		$Id$
 * @author		Joomseller!
 * @package		Joomla.Site
 * @subpackage	mod_jse_megamenu
 * @copyright	Copyright (C) 2008 - 2013 by Joomseller. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL version 3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldLayout extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'layout';

	/**
	 * fetch Element
	 */
	function getInput(){
		$db = &JFactory::getDBO();

		$files	= JFolder::files(JPATH_ROOT.'/modules/mod_jse_megamenu/assets/css/style');
		$options = array ();
		foreach ($files as $file) {
			// check if is a CSS file
			if (substr($file, -4) == '.css') {
				$filename	= substr($file, 0, -4);
				$options[] = JHTML::_('select.option', $filename, $filename);
			}
		}

		return JHTML::_('select.genericlist', $options, $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id);
	}
}