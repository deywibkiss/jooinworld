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
jimport('joomla.filesystem.folder');

class integrationsHelper
{
	/**done
	 * Returns profile page url for given user
	 *
	 * @param integer $userid e.g. 777 user id
	 *
	 * @return string $link
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/nodes.php
	 * 	- functions getNodeParticipants()
	 *
	 * calls -
	 * - helpers/jbolo.php
	 * 	-- functions => getItemId()
	 */
	function getUserProfileUrl($userid)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$integration_option=$params->get('community');
		$link='';
		if($integration_option=='joomla'){
			$link='';
		}
		else if($integration_option=='cb')
		{
			//use jboloHelper
			$jboloHelper=new jboloHelper();
			$itemid=$jboloHelper->getItemId('option=com_comprofiler');
			$link=JURI::root().substr(JRoute::_('index.php?option=com_comprofiler&task=userprofile&user='.$userid.'&Itemid='.$itemid),strlen(JURI::base(true))+1);
		}
		else if($integration_option=='jomsocial')
		{
			$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
			if(JFolder::exists($jspath)){
				include_once($jspath.DS.'libraries'.DS.'core.php');
			}
			$link=JURI::root().substr(CRoute::_('index.php?option=com_community&view=profile&userid='.$userid),strlen(JURI::base(true))+1);
		}
		else if($integration_option=='jomwall')
		{
			if(!class_exists('AwdwallHelperUser')){
				require_once(JPATH_SITE.DS.'components'.DS.'com_awdwall'.DS.'helpers'.DS.'user.php');
			}
			$awduser=new AwdwallHelperUser();
			$Itemid=$awduser->getComItemId();
			$link=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.$Itemid);
		}
		return $link;
	}

	/**done
	 * Returns avatar for given user
	 *
	 * @param integer $userid e.g. 777 user id
	 *
	 * @return string $uimage
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/nodes.php
	 * 	- functions getNodeParticipants()
	 *
	 * calls -
	 * - helpers/integrations.php (this file)
	 * 	-- functions => getCBUserAvatar() getJomsocialUserAvatar() getJomwallUserAvatar()
	 */
	function getUserAvatar($userid)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$integration_option=$params->get('community');
		$gravatar=$params->get('gravatar');

		$uimage='';
		if($gravatar)
		{
			$user=JFactory::getUser($userid);
			$usermail=$user->get('email');
			//refer https://en.gravatar.com/site/implement/images/php/
			$hash=md5(strtolower(trim($usermail)));
			$uimage='http://www.gravatar.com/avatar/'.$hash.'?s=32';
			return $uimage;
		}
		if($integration_option=="joomla")
		{
			$template=$params->get('template');
			$uimage=JURI::root()."components/com_jbolo/jbolo/view/".$template."/images/avatar_default.png";//@TODO
		}
		else if($integration_option=="cb")
		{
			$uimage=$this->getCBUserAvatar($userid);
		}
		else if($integration_option=="jomsocial")
		{
			$uimage=$this->getJomsocialUserAvatar($userid);
		}
		else if($integration_option=="jomwall")
		{
			$uimage=$this->getJomwallUserAvatar($userid);
		}
		return $uimage;
	}

	/**done
	 * Returns CB avatar for given user
	 *
	 * @param integer $userid e.g. 777 user id
	 *
	 * @return string $uimage
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/integrations.php (this file)
	 * 	- functions getUserAvatar()
	 */
	function getCBUserAvatar($userid)
	{
		$db=JFactory::getDBO();
		$q="SELECT a.id,a.username,a.name, b.avatar, b.avatarapproved
		FROM #__users a, #__comprofiler b
		WHERE a.id=b.user_id AND a.id=".$userid;
		$db->setQuery($q);
		$user=$db->loadObject();
		$img_path=JURI::root()."images/comprofiler";
		//die;
		if(isset($user->avatar) && isset($user->avatarapproved))
		{
			if(substr_count($user->avatar, "/") == 0)
			{
				$uimage = $img_path . '/tn' . $user->avatar;
			}
			else
			{
				$uimage = $img_path . '/' . $user->avatar;
			}
		}
		else if (isset($user->avatar))
		{//avatar not approved
			$uimage = JURI::root()."/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		else
		{//no avatar
			$uimage = JURI::root()."/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		return $uimage;
	}

	/**done
	 * Returns jomsocial avatar for given user
	 *
	 * @param integer $userid e.g. 777 user id
	 *
	 * @return string $uimage
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/integrations.php (this file)
	 * 	- functions getUserAvatar()
	 */
	function getJomsocialUserAvatar($userid)
	{
		$mainframe=JFactory::getApplication();
		/*included to get jomsocial avatar*/
		$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
		if(JFolder::exists($jspath)){
			include_once($jspath.DS.'libraries'.DS.'core.php');
		}
		$user=CFactory::getUser($userid);
		$uimage=$user->getThumbAvatar();
		if(!$mainframe->isSite())
		{
			$uimage=str_replace('administrator/','',$uimage);
		}
		return $uimage;
		}

	/**done
	 * Returns jomwall avatar for given user
	 *
	 * @param integer $userid e.g. 777 user id
	 *
	 * @return string $uimage
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/integrations.php (this file)
	 * 	- functions getUserAvatar()
	 */
	function getJomwallUserAvatar($userid)
	{
		if(!class_exists('AwdwallHelperUser')){
			require_once(JPATH_SITE.DS.'components'.DS.'com_awdwall'.DS.'helpers'.DS.'user.php');
		}
		$awduser=new AwdwallHelperUser();
		$uimage=$awduser->getAvatar($userid);
		return $uimage;
	}

	function getOnlineUsersList($uid)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$integration_option=$params->get('community');
		$fonly=$params->get('fonly');
		$chattitle=$params->get('chatusertitle');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}

		$online_users='';
		if($integration_option=="joomla"){
			$online_users=$this->getJoomlaOnlineUsersList($uid,$fonly,$chattitle);
		}
		else if($integration_option=="cb"){
			$online_users=$this->getCBOnlineUsersList($uid,$fonly,$chattitle);
		}
		else if($integration_option=="jomsocial"){
			$online_users=$this->getJomsocialOnlineUsersList($uid,$fonly,$chattitle);
		}
		else if($integration_option=="jomwall"){
			$online_users=$this->getJomwallOnlineUsersList($uid,$fonly,$chattitle);
		}
		return $online_users;
	}

	function getJoomlaOnlineUsersList($uid,$fonly,$chattitle,$filterText='')
	{
		$db=JFactory::getDBO();
		$query="SELECT DISTINCT u.id AS uid, u.$chattitle AS uname, u.name, u.username,
		ju.chat_status AS sts,ju.status_msg AS stsm
		FROM #__users AS u, #__session AS s
		LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
		WHERE u.id IN (s.userid)
		AND s.client_id = 0";
		if($filterText){
			$query.=" AND u.username LIKE '%".$filterText."%'";
		}
		$query.=" AND u.id <>".$uid;
		$db->setQuery($query);
		$online_users=$db->loadObjectList();
		return $online_users;
	}

	function getCBOnlineUsersList($uid,$fonly,$chattitle,$filterText='')
	{
		$db=JFactory::getDBO();
		if($fonly)//friends only
		{
			$query="SELECT DISTINCT a.id AS uid, u.$chattitle AS uname, u.name, u.username,
			b.avatar,
			ju.chat_status AS sts, ju.status_msg AS stsm
			FROM
			(
				SELECT DISTINCT u.id
				FROM #__users u, #__session s, #__comprofiler_members a
				LEFT JOIN #__comprofiler b ON a.memberid = b.user_id
				WHERE a.referenceid=".$uid."
				AND u.id = a.memberid
				AND a.memberid IN ( s.userid )
				AND (a.accepted=1)
				AND s.client_id = 0
				AND u.block=0
				ORDER BY u.username
			)
			AS a
			LEFT JOIN #__comprofiler b ON b.user_id = a.id
			LEFT JOIN #__comprofiler_members AS c on c.referenceid=a.id
			LEFT JOIN #__users AS u ON u.id=a.id
			LEFT JOIN #__session AS s ON s.userid=a.id
			LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
			WHERE c.memberid=".$uid."
			AND c.accepted=1
			AND b.banned=0
			AND s.client_id=0";
			if($filterText){
				$query.=" AND u.username LIKE '%".$filterText."%'";
			}
			$query.=" ORDER BY u.".$chattitle;
		}
		else//show all
		{
			$query="SELECT DISTINCT u.id AS uid, u.$chattitle AS uname, u.name, u.username,
			cb.avatar,
			ju.chat_status AS sts, ju.status_msg AS stsm
			FROM #__users as u
			LEFT JOIN #__session AS s ON s.userid=u.id
			LEFT JOIN #__comprofiler cb ON cb.user_id=u.id
			LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
			WHERE u.id IN (s.userid)
			AND u.id<>".$uid."
			AND s.client_id=0";
			if($filterText){
				$query.=" AND u.username LIKE '%".$filterText."%'";
			}
			$query.=" ORDER BY u.".$chattitle ;

		}
		$db->setQuery($query);
		$online_users=$db->loadObjectList();

		return $online_users;
	}

	function getJomsocialOnlineUsersList($uid,$fonly,$chattitle,$filterText='')
	{
		$db=JFactory::getDBO();
		if($fonly)//friends only
		{
			$query="SELECT DISTINCT u.id AS uid, u.$chattitle AS uname, u.name, u.username,
			cu.thumb,
			ju.chat_status AS sts, ju.status_msg AS stsm
			FROM #__users AS u
			LEFT JOIN #__community_users AS cu ON cu.userid=u.id
			LEFT JOIN #__community_connection AS cc ON cc.connect_to=cu.userid
			LEFT JOIN #__session AS s ON s.userid=u.id
			LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
			WHERE cc.connect_from=".$uid."
			AND cc.status=1
			AND cc.connect_to=u.id
			AND u.id=s.userid
			AND s.client_id=0";
			if($filterText){
				$query.=" AND ( u.username LIKE '%".$filterText."%' OR u.name LIKE '%".$filterText."%' )";
			}
			$query.=" ORDER BY u.".$chattitle;
		}
		else//show all
		{
			$query="SELECT DISTINCT u.id AS uid,  u.$chattitle AS uname, u.name, u.username,
			cu.thumb,
			ju.chat_status AS sts, ju.status_msg AS stsm
			FROM #__users as u
			LEFT JOIN #__session AS s ON s.userid=u.id
			LEFT JOIN #__community_users cu ON cu.userid=u.id
			LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
			WHERE u.id IN (s.userid)
			AND s.client_id=0";
			if($filterText){
				$query.=" AND ( u.username LIKE '%".$filterText."%' OR u.name LIKE '%".$filterText."%' )";
			}
			$query.=" AND u.id<>".$uid
			." ORDER BY u.".$chattitle;
		}

		$db->setQuery($query);
		$online_users=$db->loadObjectList();

		return $online_users;
	}

	function getJomwallOnlineUsersList($uid,$fonly,$chattitle,$filterText='')
	{
		$db=JFactory::getDBO();
		if($fonly)//friends only
		{
			$query="SELECT DISTINCT a.id AS uid, u.$chattitle AS uname, u.name, u.username,
			b.avatar,
			ju.chat_status AS sts, ju.status_msg AS stsm
			FROM
			(
				SELECT DISTINCT u.id
				FROM #__users u, #__session s, #__awd_connection a
				LEFT JOIN #__awd_wall_users b ON a.connect_to = b.user_id
				WHERE a.connect_from=".$uid."
				AND u.id = a.connect_to
				AND a.connect_to IN (s.userid)
				AND a.pending=0
				AND s.client_id=0
				AND u.block=0
				ORDER BY u.username
			)
			AS a
			LEFT JOIN #__awd_wall_users b ON b.user_id = a.id
			LEFT JOIN #__awd_connection AS c on c.connect_from=a.id
			LEFT JOIN #__users AS u ON u.id=a.id
			LEFT JOIN #__session AS s ON s.userid=a.id
			LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
			WHERE c.connect_to=".$uid."
			AND c.pending=0
			AND s.client_id=0";
			if($filterText){
				$query.=" AND u.username LIKE '%".$filterText."%'";
			}
			$query.=" ORDER BY u.".$chattitle;
		}
		else//show all
		{
			$query="SELECT DISTINCT u.id AS uid,  u.$chattitle AS uname, u.name, u.username,
			awu.avatar,
			ju.chat_status AS sts, ju.status_msg AS stsm
			FROM #__users AS u
			LEFT JOIN #__session AS s ON s.userid=u.id
			LEFT JOIN #__awd_wall_users AS awu ON awu.user_id=u.id
			LEFT JOIN #__jbolo_users AS ju ON ju.user_id=s.userid
			WHERE u.id IN (s.userid)
			AND s.client_id=0";
			if($filterText){
				$query.=" AND u.username LIKE '%".$filterText."%'";
			}
			$query.=" AND u.id<>".$uid."
			ORDER BY u.".$chattitle;
		}

		$db->setQuery($query);
		$online_users=$db->loadObjectList();

		return $online_users;
	}

	function getAutoCompleteUserList($uid,$filterText)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$integration_option=$params->get('community');

		$fonly=$params->get('fonly');
		$chattitle=$params->get('chatusertitle');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}

		$online_users='';
		if($integration_option=="joomla"){
			$online_users=$this->getJoomlaOnlineUsersList($uid,$fonly,$chattitle,$filterText);
		}
		else if($integration_option=="cb"){
			$online_users=$this->getCBOnlineUsersList($uid,$fonly,$chattitle,$filterText);
		}
		else if($integration_option=="jomsocial"){
			$online_users=$this->getJomsocialOnlineUsersList($uid,$fonly,$chattitle,$filterText);
		}
		else if($integration_option=="jomwall"){
			$online_users=$this->getJomwallOnlineUsersList($uid,$fonly,$chattitle,$filterText);
		}
		return $online_users;
	}

	function getActivityStreamHTML()
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$integration_option=$params->get('community');
		$position=$params->get('activity_module_position');

		$html='';
		if($integration_option=="joomla")
		{
			$html='';
		}
		else if($integration_option=="cb")
		{
			$html=$this->getActivityHTMLFromModule($position);
		}
		else if($integration_option=="jomsocial")
		{
			$html=$this->getActivityHTMLFromModule($position);
		}
		else if($integration_option=="jomwall")
		{
			$html=$this->getActivityHTMLFromModule($position);
		}
		return $html;
	}

	function getActivityHTMLFromModule($position)
	{
		$document=JFactory::getDocument();
		$renderer=$document->loadRenderer('module');
		$modules=JModuleHelper::getModules($position);
		$params=array();
		$html='';
		foreach($modules as $module){
			$html.=$renderer->render($module,$params);
		}
		return $html;
	}
}
?>