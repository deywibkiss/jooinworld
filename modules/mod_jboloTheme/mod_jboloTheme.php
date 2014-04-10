<?php
/**
 * @package		JBolo
 * @version		$versionID$
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 * @copyright	Copyright © 2009-2013 TechJoomla. All rights reserved.
 * @license		GNU General Public License version 2, or later
*/
//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$lang=JFactory::getLanguage();
$lang->load('com_jbolo');

$user=JFactory::getUser();
//run only if user is logged in
if(!$user->id){

	echo JText::_('COM_JBOLO_LOGIN_CHAT');
	return;
}
require( JModuleHelper::getLayoutPath( 'mod_jboloTheme' ) );
?>