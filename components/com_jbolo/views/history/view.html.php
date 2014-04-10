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

class JboloViewHistory extends JViewLegacy
{
	function display($tpl = null)
	{
		$user=JFactory::getUser();
		$app=JFactory::getApplication();
		$document=JFactory::getDocument();
		$document->setTitle( JText::_('COM_JBOLO_VIEW_HISTORY') . ' - ' . $app->getCfg( 'sitename' ) );
		if($user->id)
		{
			$this->nid=$app->input->get->get('nid','','INT');
			$nodesHelper=new nodesHelper();
			$this->isParticipant=$nodesHelper->isNodeParticipant($user->id,$this->nid);
			$this->history=$this->get('Data');
			$this->pagination=$this->get('Pagination');
		}
		else
		{
			$this->isParticipant=3;//not logged in
		}
		parent::display($tpl);
	}
}
?>