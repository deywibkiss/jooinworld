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
jimport('joomla.application.component.model');

class JboloModelTicket extends JModelLegacy
{
	function getChatlog($chatlog,$nid)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chatusertitle='username';
		}else{
			$chatusertitle='name';
		}

		$user=JFactory::getUser();
		$support_user_name=$user->name;
		$support_user_username=$user->username;

		$nodesHelper=new nodesHelper();
		$participants=$nodesHelper->getNodeParticipants($nid,$user->id,0);
		$participants=$participants['participants'];
		foreach($participants as $p)
		{
			if($p->uid	!=$user->id)
			{
				$ticket_user=JFactory::getUser($p->uid);
				$ticket_user_name=$ticket_user->name;
				$ticket_user_username=$ticket_user->username;
				break;
			}
		}
		//print_r($chatlog);
		$chatlog=strip_tags($chatlog);

		if($chatusertitle=='username'){
			$chatlog=str_replace(JText::_('COM_JBOLO_ME').' : ',"\n".$support_user_username.' : ',$chatlog);
		}else{
			$chatlog=str_replace(JText::_('COM_JBOLO_ME').' : ',"\n".$support_user_name.' : ',$chatlog);
		}

		if($chatusertitle=='username'){
			$chatlog=str_replace($ticket_user_username.' : ',"\n".$ticket_user_username.' : ',$chatlog);
		}else{
			$chatlog=str_replace($ticket_user_name.' : ',"\n".$ticket_user_name.' : ',$chatlog);
		}

		$pattern ='/{'.JText::_('COM_JBOLO_TICKED_ID_NO_SPACE').'=[0-9]*}/';
		preg_match_all($pattern,$chatlog,$matches);

		$return=array();
		$return['chatlog']=$chatlog;
		$return['ticketids']=$matches[0];

		return $return;
	}

	/*saves chatlog as a note to ticket*/
	function addActivityToTicket()
	{
		$input=JFactory::getApplication()->input;
		$ticketid=$input->post->get('ticketid','','INT');
		$chatlog=$input->post->get('chatlog','','STRING');

		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chatusertitle='username';
		}else{
			$chatusertitle='name';
		}

		$user=JFactory::getUser();
		$support_user_name=$user->name;
		$support_user_username=$user->username;

		$nid=JFactory::getApplication()->input->get('nid');
		$nodesHelper=new nodesHelper();
		$participants=$nodesHelper->getNodeParticipants($nid,$user->id,0);
		$participants=$participants['participants'];
		foreach($participants as $p)
		{
			if($p->uid	!=$user->id)
			{
				$ticket_user=JFactory::getUser($p->uid);
				$ticket_user_name=$ticket_user->name;
				$ticket_user_username=$ticket_user->username;
				break;
			}
		}
		//print_r($chatlog);die;

		if($chatusertitle=='username'){
			$chatlog=str_replace($support_user_username." : ","<br/><b>".$support_user_username." : </b>",$chatlog);
		}else{
			$chatlog=str_replace($support_user_name." : ","<br/><b>".$support_user_name." : </b>",$chatlog);
		}

		if($chatusertitle=='username'){
			$chatlog=str_replace($ticket_user_username." : ","<br/><b>".$ticket_user_username." : </b>",$chatlog);
		}else{
			$chatlog=str_replace($ticket_user_name." : ","<br/><b>".$ticket_user_name." : </b>",$chatlog);
		}

		if($chatlog && $ticketid)
		{
			$db=JFactory::getDBO();
			$sql="SELECT id FROM #__support_ticket WHERE ticketmask=".$ticketid;
			$db->setQuery( $sql );
			$id_ticket=$db->loadResult();

			$sql="INSERT INTO #__support_note(`id_ticket`, `id_user`, `date_time`, `note`, `show`)
				VALUES('".$id_ticket."', '".$user->id."', '".date("Y-m-d H:i:s")."', ".$db->quote( $chatlog ).", '1')";
			$db->setQuery( $sql );
			$db->execute();
			if($db->getErrorMsg()){
				return false;
			}else{
				return true;
			}
		}
		return false;
	}
}
?>