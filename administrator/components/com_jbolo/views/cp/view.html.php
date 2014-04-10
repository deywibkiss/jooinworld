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
jimport('joomla.application.component.view');

class JboloViewcp extends JViewLegacy
{
	function display($tpl = null)
	{
		//get model
		$model=$this->getModel();
		//use model functions
		$this->nodeTypesArray=$model->getNodeTypesArray();
		$this->messageTypesArray=$model->getMessageTypesArray();
		$this->messagesPerDayArray=$model->getMessagesPerDayArray();

		//get installed version from xml file
		$xml=JFactory::getXML(JPATH_COMPONENT.DS.'jbolo.xml');
		$version=(string)$xml->version;
		$this->version=$version;

		//set toolbar
		$this->_setToolBar();
		parent::display($tpl);
	}

	function _setToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JBOLO_MENU_JBOLO').' - '.JText::_('COM_JBOLO_MENU_CP'),'jbolo.png');
		JToolBarHelper::preferences('com_jbolo', 550, 875);
	}
}
?>