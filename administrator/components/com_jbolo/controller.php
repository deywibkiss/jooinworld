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
jimport('joomla.application.component.controller');

class JboloController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = false)
	{
		$mainframe =JFactory::getApplication();
		$input=JFactory::getApplication()->input;

		$vName=$input->get('view','cp');
		$controllerName=$input->get('controller','cp');
		$cp='';

		switch($vName)
		{
			case 'cp':
				$cp=true;
			break;
		}
		//JSubMenuHelper::addEntry(JText::_('COM_JBOLO_MENU_CP'), 'index.php?option=com_jbolo',$cp);

		switch ($vName)
		{
			case 'cp':
			default:
				$vName='cp';
				$vLayout=$input->get('layout','default');
				$mName='cp';
			break;
		}

		$mName='cp';
		$document=JFactory::getDocument();
		$vType=$document->getType();

		//Get/Create the view
		$view=$this->getView($vName,$vType);
		// Get/Create the model
		if($model=$this->getModel($mName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		//Set the layout
		$view->setLayout($vLayout);

		// Display the view
		$view->display();
	}

	/*
	 * This returns the latest version number from version checker
	 * */
	function getVersion()
	{
		echo $recdata = @file_get_contents('http://techjoomla.com/vc/index.php?key=abcd1234&product=jbolo');
		jexit();
	}
}
?>