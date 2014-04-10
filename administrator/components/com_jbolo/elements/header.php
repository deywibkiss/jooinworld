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

class JFormFieldHeader extends JFormField
{
	var	$type='Header';
	function getInput()
	{
		$document=JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_jbolo/css/jbolo.css');
		$return='
		<div class="jbolo_div_outer">
			<div class="jbolo_div_inner">
				'.JText::_($this->value).'
			</div>
		</div>';
		return $return;
	}
}
?>