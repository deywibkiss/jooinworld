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
jimport('joomla.form.formfield');

class JFormFieldCronjoburl extends JFormField
{
	var	$type='Cronjoburl';
	function getInput()
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$this->purge_key=$params->get('purge_key');
		$cronjoburl=JRoute::_(JURI::root().'index.php?option=com_jbolo&action=purgeChats&purge_key='.$this->purge_key);
		$return='<input type="text" name="cronjoburl" disabled="disabled" value="'.$cronjoburl.'" size="120" style="border:none">';
		return $return;
	}
}
?>