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

class JboloControllerTicket extends JboloController
{
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	function addActivityToTicket()
	{
		//Get the model
		$model=$this->getModel('ticket');
		$success=$model->addActivityToTicket();
		if($success){
			 $msg=JText::_('COM_JBOLO_CHATLOG_SAVE_SUCEESS_MSG');
			 $this->setRedirect("index.php?option=com_jbolo&view=ticket&success=1&tmpl=component",$msg);
		}else{
			 $msg=JText::_('COM_JBOLO_CHATLOG_SAVE_FAIL_MSG');
			 $this->setRedirect("index.php?option=com_jbolo&view=ticket&success=0&tmpl=component",$msg);
		}
	}
}
?>