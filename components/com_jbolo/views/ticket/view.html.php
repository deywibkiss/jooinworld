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
jimport( 'joomla.application.component.view');

class JboloViewTicket extends JViewLegacy
{
	function display($tpl = null)
	{
		$input=JFactory::getApplication()->input;
		$this->success=$input->get->get('success','','INT');
		if(!$this->success)//generate data from chatlog to show it for editing
		{
			$chatlog=$input->get->get('chatlog','','STRING');
			$this->nid=$input->get->get('nid','','INT');
			if($chatlog)
			{
				$model=$this->getModel('ticket');
				$chatlog_ticketids=$model->getChatlog($chatlog,$this->nid);
				$this->chatlog=$chatlog_ticketids['chatlog'];
				$this->ticketids=$chatlog_ticketids['ticketids'];
			}
		}
		parent::display($tpl);
	}
}
?>