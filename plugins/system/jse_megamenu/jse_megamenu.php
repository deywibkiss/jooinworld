<?php
/**
 * @version		$Id$
 * @author		Joomseller!
 * @package		Joomla!
 * @subpackage	JSE_MegaMenu_Framework
 * @copyright	Copyright (C) 2008 - 2013 by Joomseller Solutions. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL version 3, SEE LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport ( 'joomla.plugin.plugin' );

class plgSystemJSE_MegaMenu extends JPlugin {	
	
	function __construct(&$subject, $config) {
		parent::__construct ( $subject, $config );
	}
	
	//Add Megamenu Extended menu parameter
	function onContentPrepareForm($form, $data)
	{
		if ($form->getName()=='com_menus.item')
		{
			$this->loadLanguage ( null, JPATH_ADMINISTRATOR);
			JForm::addFormPath(JPATH_SITE.'/plugins/system/jse_megamenu/params');
			$form->loadFile('params', false);
		}
	}
}