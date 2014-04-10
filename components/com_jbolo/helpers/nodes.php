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
class nodesHelper
{
	/*
	 * done - mvc
	 * This function returns count of all active chat node participants node
	 *
	 * @param integer $nid - nid
	 *
	 * @return array $count
	 * */
	function getActiveNodeParticipantsCount($nid)
	{
		$db=JFactory::getDBO();
		//get node participants info
		$query="SELECT DISTINCT u.id AS uid
		FROM #__users AS u
		LEFT JOIN #__jbolo_node_users AS nu ON nu.user_id=u.id
		LEFT JOIN #__jbolo_users AS ju ON ju.user_id=nu.user_id
		WHERE nu.node_id=".$nid." AND nu.status=1";
		$db->setQuery($query);
		$participants=$db->loadObjectList();
		$count=count($participants);
		return $count;
	}

	function getActiveNodeParticipants($nid)
	{
		$db=JFactory::getDBO();
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}
		//get node participants info
		$query="SELECT DISTINCT u.id AS uid, u.".$chattitle." AS name
		FROM #__users AS u
		LEFT JOIN #__jbolo_node_users AS nu ON nu.user_id=u.id
		LEFT JOIN #__jbolo_users AS ju ON ju.user_id=nu.user_id
		WHERE nu.node_id=".$nid." AND nu.status=1";
		$db->setQuery($query);
		$participants=$db->loadObjectList();
		return $participants;
	}

	/* - done mvc
	 * This function returns info about all nodes where user is participant
	 *
	 * @param integer $uid - useid
	 * @return array $nodes
	 * */
	function getActiveChatNodes($uid)
	{
		$db=JFactory::getDBO();
		/*$query="SELECT n.node_id AS nid, n.owner AS uid, n.type AS ctyp*/
		$query="SELECT n.node_id AS nid, n.type AS ctyp
		FROM #__jbolo_nodes AS n
		LEFT JOIN #__jbolo_node_users AS nu ON nu.node_id=n.node_id
		WHERE nu.user_id=".$uid;
		$db->setQuery($query);
		$nodes=$db->loadObjectList();
		//print_r($nodes);
		return $nodes;
	}

	//to sort messages in each node
	/*
	 * INPUT
	nodesArray:
		0:
			messages: null
			nodeinfo:
			participants:

		1:
			messages:
				0: {mid:2, fid:776, msg:11, ts:2012-12-28 12:44:43}
				1: {mid:3, fid:777, msg:22, ts:2012-12-28 12:44:48}
				2: {mid:5, fid:779, msg:44, ts:2012-12-28 12:44:59}
				3: {mid:4, fid:778, msg:33, ts:2012-12-28 12:44:55}
			nodeinfo:
			participants:

	* OUTPUT
	nodesArray:
		0:
			messages: null
			nodeinfo:
			participants:

		1:
			messages:
				0: {mid:2, fid:776, msg:11, ts:2012-12-28 12:44:43}
				1: {mid:3, fid:777, msg:22, ts:2012-12-28 12:44:48}
				3: {mid:4, fid:778, msg:33, ts:2012-12-28 12:44:55}
				3: {mid:5, fid:779, msg:44, ts:2012-12-28 12:44:59}
			nodeinfo:
			participants:
	 * */
	function sortMessages($nodesArray,$column,$order)
	{
		//print_r($nodesArray);
		foreach($nodesArray as &$node)
		{
			$array=&$node['messages'];//pointer
			$array=$this->multi_d_sort($array,$column,$order);
		}
		//print_r($nodesArray);die;
		return $nodesArray;
	}

	function multi_d_sort($array,$column,$order)
	{
		if(isset($array) && count($array))
		{
			foreach($array as $key=>$row)
			{
				$orderby[$key]=$row->$column;
			}
			if($order=='asc')
			{
				array_multisort($orderby,SORT_ASC,$array);
			}
			else
			{
				array_multisort($orderby,SORT_DESC,$array);
			}
		}
		return $array;
	}

	function updateNodeParticipants($nodesArray,$uid)
	{
		foreach($nodesArray as $index=>$node)
		{
			$pdata=$this->getNodeParticipants($node['nodeinfo']->nid,$uid);
			$participants=$pdata['participants'];
			$nodesArray[$index]['participants']=$participants;
		}
		//print_r($nodesArray); die;
		return $nodesArray;
	}

	function updateWindowTitles($nodesArray,$uid)
	{
		foreach($nodesArray as $index=>$node)
		{
			$nodesArray[$index]['nodeinfo']->wt=$this->getNodeTitle($node['nodeinfo']->nid,$uid,$node['nodeinfo']->ctyp);
		}
		//print_r($nodesArray);die;
		return $nodesArray;
	}

	function updateWindowStatus($nodesArray,$uid)
	{
		foreach($nodesArray as $index=>$node)
		{
			$nodesArray[$index]['nodeinfo']->ns=$this->getNodeStatus($node['nodeinfo']->nid,$uid,$node['nodeinfo']->ctyp);
		}
		//print_r($nodesArray);die;
		return $nodesArray;
	}

	function getNodeStatusArray($nodesArray,$uid)
	{
		$nodeStatusArray=array();
		foreach($nodesArray as $node)
		{
			$nodeStatusArray[$node['nodeinfo']->nid]=$this->getNodeStatus($node['nodeinfo']->nid,$uid,$node['nodeinfo']->ctyp);
		}
		//print_r($nodesArray);die;
		return $nodeStatusArray;
	}


	function isNodeParticipant($uid,$nid)
	{
		$db=JFactory::getDBO();
		$query="SELECT user_id, status
		FROM #__jbolo_node_users
		WHERE node_id=".$nid.
		" AND user_id=".$uid;
		$db->setQuery($query);
		$p=$db->loadObject();
		if(!$p)//not a valid group chat participant
			$isParticipant=0;
		elseif($p->status==1)
			$isParticipant=1;//active participant
		elseif($p->status==0)
			$isParticipant=2;//inactive participant (who left chat)
		return $isParticipant;
	}

	/**done2
	 * Checks if a 1to1 node exist for given pair of users
	 *
	 * @param integer uid e.g. 776 who initiates chat
	 * @param integer pid e.g. 777 participant
	 *
	 * @return integer node_id_found e.g. 3 OR 0 (no node found)
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - models/nodes.php
	 * 	- functions initiateNode()
	 */
	function checkNodeExists($uid,$pid)
	{
		$db=JFactory::getDBO();
		//check if a node already exists for these 2 users
		//WHERE - subquery => get all such nodes where uid or participant is a owner
		$query="SELECT nu.node_id AS nid, GROUP_CONCAT(nu.user_id) AS users
		FROM `#__jbolo_node_users` AS nu
		LEFT JOIN `#__jbolo_nodes` AS nd ON nd.node_id = nu.node_id
		WHERE nu.node_id IN
		(
			SELECT nd.node_id
			FROM `#__jbolo_nodes` AS nd
			WHERE nd.type=1
			AND (nd.owner=".$uid." OR nd.owner=".$pid.")
		)
		GROUP BY nu.node_id";
		$db->setQuery($query);
		$nodes=$db->loadObjectList();
		//print_r($nodes);

		//check if 2 users have shared a node already
		$users1=$uid.",".$pid;//776,778
		$users2=$pid.",".$uid;//778,776
		$node_id_found=0;
		$count=count($nodes);
		for($i=0;$i<$count;$i++){
			//if node found for 2 users, exit loop
			if($nodes[$i]->users==$users1 || $nodes[$i]->users==$users2){
				$node_id_found=$nodes[$i]->nid;
				break;
			}
		}
		return $node_id_found;
	}

	/**done2
	 * Returns node title to be shown as chat window title
	 * - for group chat shows logged in user name at the end
	 *
	 * @param integer nid e.g. 2 node id
	 * @param integer uid e.g. 777 logged in user id
	 * @param integer ctyp e.g. 1 OR 2 chat node type (1-1to OR 2-group chat)
	 *
	 * @return string windowTitle e.g. (3) manoj, dipti, me
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - models/nodes.php
	 * 	-- functions initiateNode()
	 *
	 * calls -
	 * - helpers/nodes.php [same file]
	 * 	-- functions => getNodeParticipants()
	 */
	function getNodeTitle($nid,$uid,$ctyp)
	{
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}
		$pdata=$this->getNodeParticipants($nid,$uid);
		$participants=$pdata['participants'];
		$count=count($participants);

		$windowTitle='';
		if($ctyp==1)//for 1to1 chat
		{
			foreach($participants as $p)
			{
				if($p->uid != $uid){//for 1to1 chat, use other user's name as title
					$windowTitle=JFactory::getUser($p->uid)->$chattitle;
				}
			}
		}
		else//group chat
		{
			foreach($participants as $p)
			{
				if($p->active==1)//if participant is active in node, use his name
				{
					if($p->uid != $uid)
						$windowTitle.=JFactory::getUser($p->uid)->$chattitle.', ';
				}
				else{
					$count--;//we are showing count for active users as well, so modify it
				}
			}
			//append 'me' at the end
			$flag=0;
			foreach($participants as $p)
			{
				if(!$flag && $p->active==1)
				{
					if($p->uid == $uid){
						$windowTitle.=JText::_('COM_JBOLO_ME');
						$flag=1;
					}
				}
				elseif($flag)
					break;
			}
			//remove trailing comma
			if(!$flag){
				$windowTitle=trim($windowTitle,', ');
			}
			//make it look like - (3) manoj, dipti, me
			$windowTitle='('.$count.') '.$windowTitle;
		}
		//@TODO might need to change this - done to trim down chat window title
		//ideally should be done from CSS
		if(strlen($windowTitle) > 17){
			$windowTitle=substr($windowTitle,0,17).' ...';
		}
		return $windowTitle;
	}

	/**done2
	 * Returns node status to be shown as chat window status
	 *
	 * @param integer nid e.g. 2 node id
	 * @param integer uid e.g. 777 logged in user id
	 * @param integer ctyp e.g. 1 OR 2 chat node type (1-1to OR 2-group chat)
	 *
	 * @return integer windowTitle e.g. (3) manoj, dipti, me
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - models/nodes.php
	 * 	-- functions initiateNode()
	 *
	 * calls -
	 * - helpers/nodes.php [same file]
	 * 	-- functions => getNodeParticipants()
	 */
	function getNodeStatus($nid,$uid,$ctyp)
	{
		$pdata=$this->getNodeParticipants($nid,$uid);
		$participants=$pdata['participants'];
		$nodeStatus=1;//default online
		if($ctyp==1)//for 1to1 chat
		{
			foreach($participants as $p)
			{
				if($p->uid != $uid){
					$nodeStatus=$p->sts;
				}
			}
		}
		else//group chat
		{
			$flag=1;
			$nodeStatus=4;//default offline
			foreach($participants as $p)
			{
				if($flag)
				{
					if($p->uid != $uid)
					{
						if($p->sts){//if any of the participant is online/active, group chat is active
							$nodeStatus=1;//online
							$flag=0;
						}
					}
				}
			}
		}
		return $nodeStatus;
	}

	/**
	 * This function returns info about all active chat node participants for given user and node
	 *
	 * @param integer nid e.g. 2 node id
	 * @param integer uid e.g. 777 logged in user id
	 *
	 * @return array $return_data
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - helpers/nodes.php (this file)
	 * 	- functions getNodeTitle() getNodeStatus()
	 *
	 * calls -
	 * - helpers/users.php
	 * 	-- functions => checkOnlineStatus()
	 * - helpers/integrationsHelper.php
	 * 	-- functions => getUserAvatar() getUserProfileUrl()
	 */
	function getNodeParticipants($nid,$uid)
	{
		//echo $nid.'-'.$uid.'-'.$returnOnlyActive."<br/>";
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}
		$db=JFactory::getDBO();
		//get node participants info
		$query="SELECT DISTINCT u.id AS uid, u.$chattitle AS uname, u.name, u.username,
		ju.chat_status AS sts, ju.status_msg AS stsm, nu.status AS active
		FROM #__users AS u
		LEFT JOIN #__jbolo_node_users AS nu ON nu.user_id=u.id
		LEFT JOIN #__jbolo_users AS ju ON ju.user_id=nu.user_id
		WHERE nu.node_id=".$nid."
		ORDER BY u.username";
		$db->setQuery($query);
		$participants=$db->loadObjectList();
		//print_r($participants);
		$count=count($participants);

		//use integrationsHelper
		$integrationsHelper=new integrationsHelper();
		//use users helper
		$usersHelper=new usersHelper();
		for($i=0;$i<$count;$i++)
		{
			$participants[$participants[$i]->uid]=new stdClass();
			$participants[$participants[$i]->uid]->uid=$participants[$i]->uid;
			$participants[$participants[$i]->uid]->uname=$participants[$i]->uname;
			$participants[$participants[$i]->uid]->name=$participants[$i]->name;
			$participants[$participants[$i]->uid]->stsm=$participants[$i]->stsm;//status message
			$participants[$participants[$i]->uid]->active=$participants[$i]->active;
			//get online status
			$onlineStatus=$usersHelper->checkOnlineStatus($participants[$i]->uid);
			if($onlineStatus)
				$participants[$participants[$i]->uid]->sts=$participants[$i]->sts;//online
			else
				$participants[$participants[$i]->uid]->sts=4;//offline
			//get avatar
			$participants[$participants[$i]->uid]->avtr=$integrationsHelper->getUserAvatar($participants[$i]->uid);
			//get profile url
			$participants[$participants[$i]->uid]->purl=$integrationsHelper->getUserProfileUrl($participants[$i]->uid);
			unset($participants[$i]);//imp to unset old indexes as we are setting new indexes
		}
		$return_data['participants']=$participants;
		//print_r($return_data);
		return $return_data;
	}

	/**done2
	 * This function returns chat node type
	 *
	 * @param integer nid e.g. 2 node id
	 *
	 * @return integer $nodeType e.g. 1 OR 2
	 * @since JBolo 3.0
	 *
	 * called from -
	 * - models/nodes.php (this file)
	 * 	- functions leaveChat() getNodeStatus()
	 */
	function getNodeType($nid)
	{
		$db=JFactory::getDBO();
		$query="SELECT type
		FROM #__jbolo_nodes
		WHERE node_id=".$nid;
		$db->setQuery($query);
		$nodeType=$db->loadResult();
		return $nodeType;//1 OR 2 i.e. 1to1 or group chat
	}

}
?>