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
// Component Helper
jimport('joomla.application.component.helper');
class usersHelper
{
	/**done
	 * Checks if user has active session
	 *
	 * @param integer uid e.g. 777 user id
	 *
	 * @return integer 0 or 1
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/nodes.php
	 * 	- functions getNodeParticipants()
	 */
	function checkOnlineStatus($uid)
	{
		$db=JFactory::getDBO();
		$query="SELECT userid
		FROM #__session
		WHERE userid=".$uid;
		$db->setQuery($query);
		$userid=$db->loadResult();
		if($userid)
			return 1;
		else
			return 0;
	}

	/* - done mvc
	 * This function returns info about loggedin ser
	 *
	 * @param integer $uid - useid
	 * @return array $u_data OR NULL
	 * */
	function getLoggedinUserInfo($uid)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$chattitle=$params->get('chatusertitle');//$chat_config['fonly'];
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}

		$db=JFactory::getDBO();
		$query="SELECT DISTINCT u.id AS uid, u.$chattitle AS uname, u.name, u.username,
		ju.chat_status AS sts, ju.status_msg As stsm
		FROM #__users AS u, #__session AS s
		LEFT JOIN #__jbolo_users AS ju ON ju.user_id=".$uid."
		WHERE u.id IN (s.userid) AND s.client_id = 0 AND u.id =".$uid;
		$db->setQuery($query);
		$u_data=$db->loadObject();
		//print_r($u_data);
		if(isset($u_data))
		{
			//use integrationsHelper
			$integrationsHelper=new integrationsHelper();
			$u_data->avtr=$integrationsHelper->getUserAvatar($u_data->uid);
			$u_data->purl=$integrationsHelper->getUserProfileUrl($u_data->uid);
			return $u_data;
		}
		else{
			return NULL;
		}
	}

	/* - done - mvc
	 * This function returns info about all of the currently logged in users' info execpt the
	 * uid passed to this function
	 *
	 * @param integer $uid - useid
	 * @return array $online_users
	 * */
	function getOnlineUsersInfo($uid)
	{
		$db=JFactory::getDBO();
		//use integrationsHelper
		$integrationsHelper=new integrationsHelper();
		$online_users=$integrationsHelper->getOnlineUsersList($uid);
		$count=count($online_users);
		for($i=0;$i<$count;$i++)
		{
			$online_users[$i]->avtr=$integrationsHelper->getUserAvatar($online_users[$i]->uid);
			$online_users[$i]->purl=$integrationsHelper->getUserProfileUrl($online_users[$i]->uid);
		}
		//print_r($online_users);die;
		return $online_users;
	}

	function getAutoCompleteUserList($uid,$filterText)
	{
		$db=JFactory::getDBO();
		//use integrationsHelper
		$integrationsHelper=new integrationsHelper();
		$online_users=$integrationsHelper->getAutoCompleteUserList($uid,$filterText);
		/*
		$count=count($online_users);
		for($i=0;$i<$count;$i++)
		{
			$online_users[$i]->avtr=$integrationsHelper->getUserAvatar($online_users[$i]->uid);
			$online_users[$i]->purl=$integrationsHelper->getUserProfileUrl($online_users[$i]->uid);
		}
		*/
		//print_r($online_users);die;
		return $online_users;
	}
}
?>