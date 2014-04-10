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
jimport( 'joomla.application.component.model' );

class jboloModelNodes extends JModelLegacy
{
	function __construct(){
		parent::__construct();
	}

	/* This function =>
	 * - adds an entry for a user in jbolo users table if it's not there
	 * - returns array of info about logged in user & other logged in users
	 * - it also returns info about nodes as it is from session
	 * - echoes json reponse
	 *
	 * OUTPUT VARIABLE-@JSON
	 * $response - @json array
	 * "userlist":
			{
				"me":{
				},
				"users":[
				{
				}
				]
			},
			nodes:
			0: {
				nodeinfo:{
					nid:42,
					ts:2012-12-20 12:13:17,
					uid:777,
					tid:776,
					tname:admin
				},...]
				messages: [
				{
				mid:1,
				fid:777,
				msg:123,
				ts:2012-12-20 12:13:21
				},…]

				participants: {
					776:{
					uid:776,
					uname:admin,
					name:Super User,
					sts:1,
					stsm:Chatting here first time,
					…},
				}
			}…},
		}
	 *  */
	function startChatSession()
	{
		//validate user
		$response=$this->validateUserLogin();

		//$log="\n".'start chat session'."\n";
		$params=JComponentHelper::getParams('com_jbolo');
		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;

		//use nodes helper
		$nodesHelper=new nodesHelper();

		$query="SELECT user_id
		FROM #__jbolo_users
		WHERE user_id=".$uid;
		$db->setQuery($query);
		$data=$db->loadObject();
		//if there is no entry for logged in user in jbolo users table, add new entry
		//this is needed for chat sts and sts message
		if($data==NULL)
		{
			$myobj=new stdclass;
			$myobj->user_id=$uid;
			$myobj->chat_status=1;
			$myobj->status_msg=JText::_('COM_JBOLO_DEFAULT_STATUS_MSG');
			$db->insertObject('#__jbolo_users',$myobj);
		}

		//get logged in user information
		//use users helper
		$usersHelper=new usersHelper();
		$u_data=$usersHelper->getLoggedinUserInfo($uid);

		$response['userlist']['me']=$u_data;

		//print_r($_SESSION['jbolo']['nodes']);
		//if(isset($_SESSION['jbolo']))
		//	$log.= print_r($_SESSION['jbolo']['nodes'], true);

		//generate online user list
		//use users helper
		//$usersHelper=new usersHelper();
		$data=$usersHelper->getOnlineUsersInfo($uid);

		$response['userlist']['users']=$data;

		//check if nodes array is present in session
		if(isset($_SESSION['jbolo']['nodes']))
		{
			//lets fix message ordering by ordering them in ascending order by mid
			$_SESSION['jbolo']['nodes']=$nodesHelper->sortMessages($_SESSION['jbolo']['nodes'],'mid','asc');

			//lets fix group chat window titles with current looged in users names
			$_SESSION['jbolo']['nodes']=$nodesHelper->updateWindowTitles($_SESSION['jbolo']['nodes'],$uid);

			//lets fix group chat window status
			$_SESSION['jbolo']['nodes']=$nodesHelper->updateWindowStatus($_SESSION['jbolo']['nodes'],$uid);
			$response['nodes']=$_SESSION['jbolo']['nodes'];

			//use nodes helper
			$nodeStatusArray=$nodesHelper->getNodeStatusArray($_SESSION['jbolo']['nodes'],$uid);
			$response['nsts']=$nodeStatusArray;

			//lets fix chat participant list
			$_SESSION['jbolo']['nodes']=$nodesHelper->updateNodeParticipants($_SESSION['jbolo']['nodes'],$uid);
			$response['nodes']=$_SESSION['jbolo']['nodes'];
		}
		else{//if not, initialize it
			$response['nodes']=array();
			$response['nsts']=array();
		}
		$show_activity=$params->get('show_activity');
		if($show_activity)
		{
			$template=$params->get('template');
			//get current template from cookie if available
			if(isset($_COOKIE["jboloTheme"])){
				$template=$_COOKIE["jboloTheme"];
			}
			if($template=='facebook')//load activity sream only for FB template
			{
				//get activity stream
				$integrationsHelper=new integrationsHelper();
				$ashtml=$integrationsHelper->getActivityStreamHTML();
				if($ashtml=='')
					$response['ashtml']='<strong>'.JText::_('COM_JBOLO_ACTIVITY_INCORRECT_CONFIGURATION').'</strong>';
				else{
					$response['ashtml']=$ashtml;
				}
			}
		}

		/*
		//write to log
		$logf=JPATH_SITE.'/components/com_jbolo/'.$uid.'.php';
		$f = @fopen($logf, 'a+');
		if ($f)
		{
			if(isset($_SESSION['jbolo'])){
				$log.= print_r($_SESSION['jbolo'], true);
				$log.= print_r($response, true);
			}
			//@fputs($f,$log);
			@fclose($f);
		}
		*/

		return $response;

	}//end of startChatSession





	/**
	 *
	 * called from -
	 * - models/nodes.php (same file)
	 * 	- functions startChatSession(), initiateNode(), pushChatToNode(),
	 * 	- polling(), clearchat()
	 */
	function validateUserLogin()
	{
		$user=JFactory::getUser();
		$uid=$user->id;
		$response['validate']=new stdclass;
		if(!$uid)//user logged out
		{
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_UNAUTHORZIED_REQUEST');
			//output json response
			header('Content-type: application/json');
			echo json_encode($response);
			jexit();
		}
		return $response;
	}

	/**
	 *
	 * called from -
	 * - models/nodes.php
	 * 	- functions initiateNode()
	 */
	function validateNodeParticipant($uid,$nid)
	{
		$response['validate']=new stdclass;

		//use nodes helper
		$nodesHelper=new nodesHelper();
		$isNodeParticipant=$nodesHelper->isNodeParticipant($uid,$nid);

		if($isNodeParticipant==1)//active participant
		{
			return $response;
		}
		else if($isNodeParticipant==2)//inactive participant (who left chat)
		{
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG');
		}
		else if(!$isNodeParticipant)// 0 - not a valid group chat participant
		{
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_NON_MEMBER_MSG');
		}
		//echo errors
		json_encode($response);
		//output json response
		header('Content-type: application/json');
		echo json_encode($response);
		jexit();
	}

	/*
	 * This function ->
	 * -- pushes a chat mesages sent by user to a node(to server/database actually)
	 * -- it also updates chat xref table so that all participants can get this message when polling is done
	 * -- it also updates session by adding new message data into session
	 * -- and finally echoes json reponse
	 *
	 * INPUT VARIABLES-@POST
	 * uid:776
	 * nid:38
	 * msg:hiiiiii
	 * ts:1355822592
	 * type:1
	 *
	 * OUTPUT VARIABLE-@JSON
	 * @pushChat_response - @json array
	 {
		 "nid":"38",
		 "uid":"776",
		 "mid":"5",
		 "sent":"1",
		 "msg":"hiiiiii"
	 }
	 *  */
	function pushChatToNode()
	{
		//validate user
		$pushChat_response=$this->validateUserLogin();

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;

		//get inputs from posted data
		$input=JFactory::getApplication()->input;
		$post=$input->post;
		//print_r($post);
		//get nid and msg
		$nid=$input->post->get('nid','','INT');
		$msg=$input->post->get('msg','','STRING');
		//validate if nid is not specified
		if(!$nid){
			$pushChat_response['validate']->error=1;
			$pushChat_response['validate']->error_msg=JText::_('COM_JBOLO_INVALID_NODE_ID');
			return $pushChat_response;
		}
		//validate if message is not blank
		if(!$msg){
			$pushChat_response['validate']->error=1;
			$pushChat_response['validate']->error_msg=JText::_('COM_JBOLO_EMPTY_MSG');
			return $pushChat_response;
		}

		$nodesHelper=new nodesHelper();
		//validate if this user is participant of this node
		$isNodeParticipant=$nodesHelper->isNodeParticipant($uid,$nid);
		if($isNodeParticipant==2)//error handling for inactive user
		{
			$pushChat_response['validate']->error=1;
			$pushChat_response['validate']->error_msg=JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG');
			return $pushChat_response;
		}
		if(!$isNodeParticipant)//error handling for not member/unauthorized access to this group chat
		{
			$pushChat_response['validate']->error=1;
			$pushChat_response['validate']->error_msg=JText::_('COM_JBOLO_NON_MEMBER_MSG');
			return $pushChat_response;
		}

		//@TODO decide what's better? - store first and then process message
		//OR - process first and then display
		//trigger plugins to process message text
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('jbolo','plg_jbolo_textprocessing');
		//process urls
		$processedText=$dispatcher->trigger('processUrls',array($msg));
		$msg=$processedText[0];
		//process smilies
		$processedText=$dispatcher->trigger('processSmilies',array($msg));
		$msg=$processedText[0];
		//process bad words
		$processedText=$dispatcher->trigger('processBadWords',array($msg));
		$msg=$processedText[0];

		//add msg to database
		$myobj=new stdclass;
		$myobj->from=$uid;
		$myobj->to_node_id=$nid;
		$myobj->msg=$msg;
		$myobj->msg_type='txt';
		$myobj->time=date("Y-m-d H:i:s");//NOTE - date format
		$myobj->sent=1;//@TODO need to chk if this is really used or is it for future proofing
		$db->insertObject('#__jbolo_chat_msgs',$myobj);
		//get last insert id
		$new_mid=$db->insertid();

		//update msg xref table
		if($new_mid)
		{
			//get participants for this node
			$query="SELECT user_id
			FROM #__jbolo_node_users
			WHERE node_id = ".$nid."
			AND user_id <> ".$uid."
			AND status=1";//status indicates that user is still part of node
			$db->setQuery($query);
			$participant=$db->loadColumn();
			$count=count($participant);
			//add entry for all users against this msg
			for($i=0;$i<$count;$i++)
			{
				$myobj= new stdclass;
				$myobj->msg_id=$new_mid;
				$myobj->node_id=$nid;
				$myobj->to_user_id=$participant[$i];
				$myobj->delivered=0;
				$myobj->read=0;
				$db->insertObject('#__jbolo_chat_msgs_xref',$myobj);
			}
		}
		//prepare json response
	 	$query="SELECT chm.to_node_id AS nid, chm.from AS uid, chm.msg_id AS mid, chm.sent, chm.msg
	 	FROM #__jbolo_chat_msgs AS chm
	 	WHERE chm.msg_id=".$new_mid;
		$db->setQuery($query);
		$node_d=$db->loadObject();
		$pushChat_response['pushChat_response']=$node_d;

		//add this msg to session
	 	$query ="SELECT m.msg_id AS mid, m.from AS fid, m.msg, m.time AS ts
		FROM #__jbolo_chat_msgs AS m
		LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
		WHERE m.msg_id=".$new_mid." AND m.sent=1";
		$db->setQuery($query);
		//$msg_dt=$db->loadAssocList();
		$msg_dt=$db->loadObject();
		$msg_dt->ts=JFactory::getDate($msg_dt->ts)->Format(JText::_('COM_JBOLO_SENT_AT_FORMAT'));

		//print_r($_SESSION['jbolo']);

		//update session by adding this msg against corresponding node
		if(isset($_SESSION['jbolo']['nodes']))//if jbolo nodes array is set
		{
			//count nodes in session
			$nodecount=count($_SESSION['jbolo']['nodes']);
			for($k=0;$k<$nodecount;$k++)//loop through all nodes
			{
				if(isset($_SESSION['jbolo']['nodes'][$k]))//if k'th node is set
				{
					if(isset($_SESSION['jbolo']['nodes'][$k]['nodeinfo']))//if nodeinfo is set
					{
						//if the required node is found in session
						if($_SESSION['jbolo']['nodes'][$k]['nodeinfo']->nid==$nid)
						{
							$mcnt=0;//initialize mesasge count for node found to 0
							//check if the node found has messages stored in session
							if(isset($_SESSION['jbolo']['nodes'][$k]['messages']))
							{
								//if yes count msgs
								$mcnt=count($_SESSION['jbolo']['nodes'][$k]['messages']);
								//add new mesage at the end
								$_SESSION['jbolo']['nodes'][$k]['messages'][$mcnt]=$msg_dt;
							}else{//add new mesage at the start
								$_SESSION['jbolo']['nodes'][$k]['messages'][0]=$msg_dt;
							}
						}
					}
				}
				else//@TODO remaining...
				{
					//if node is not present in session
					//this situation is not expected ideally
				}
			}//end for
		}//end if

		//print_r($_SESSION['jbolo']);

		/*
		//log to file
		$logf=JPATH_SITE.'/components/com_jbolo/'.$uid.'.php';
		$f = @fopen($logf, 'a+');
		if ($f) {
			$log="\n".'PUSH CHAT'."\n";
			if(isset($_SESSION['jbolo'])){
			$log.= print_r($_SESSION['jbolo'], true);
			}
			$log.= print_r($pushChat_response, true);
			//@fputs($f,$log);
			@fclose($f);
		}
		*/

	  	return $pushChat_response;

	}//end pushChatToNode function


	/* This function =>
	 * - adds an entry for a user in jbolo users table if it's not there
	 * - returns array of info about logged in user & other logged in users
	 * - it also returns info about nodes as it is from session
	 * - echoes json reponse
	 *
	 * INPUT VARIABLES-@POST
	 * uid:777
	 * ts:1355985190
	 *
	 * OUTPUT VARIABLE-@JSON
	 * $polling - @json array
		nodes:
			0:
				messages:
				0: {mid:11, fid:776, msg:11, ts:2012-12-20 12:48:41}
				1: {mid:12, fid:776, msg:2, ts:2012-12-20 12:48:42}
				2: {mid:13, fid:776, msg:2, ts:2012-12-20 12:48:42}
				3: {mid:14, fid:776, msg:3, ts:2012-12-20 12:48:42}
				4: {mid:15, fid:776, msg:3, ts:2012-12-20 12:48:43}
				5: {mid:16, fid:776, msg:3, ts:2012-12-20 12:48:43}
				nodeinfo: {nid:42, uid:777, tid:776}
				participants:
					776: {uid:776, uname:admin, name:Super User, sts:1, stsm:Chatting here first time,…}
					777: {uid:777, uname:user1, name:user1, sts:1, stsm:Chatting here first time,…}
		userlist:
			me: {uid:777, uname:user1, name:user1, sts:1, stsm:Chatting here first time,…}
			users:
				0: {uid:776, uname:admin, name:Super User, sts:1, stsm:Chatting here first time,…}
	 * */
	function polling()
	{
		//$log.= print_r($_SESSION['jbolo'], true);
		//echo "jbolo session"."<br/>";print_r($_SESSION['jbolo']);

		//validate user
		$polling=$this->validateUserLogin();

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;

		//get logged in user information
		//use users helper
		$usersHelper=new usersHelper();
		$u_data=$usersHelper->getLoggedinUserInfo($uid);
		$polling['userlist']['me']=$u_data;

		//generate online user list
		//use users helper
		$data=$usersHelper->getOnlineUsersInfo($uid);
		$polling['userlist']['users']=$data;

		//get nodeinfo for all nodes for logged in user
		//use nodes helper
		$nodesHelper=new nodesHelper();
		$nodes=$nodesHelper->getActiveChatNodes($uid);

		//echo "nodes"."<br/>";print_r($nodes);
		/*
		nodes Array
		(
			[0] => stdClass Object
				(
					[nid] => 42
					[uid] => 777
				)

		)
		*/

		$polling['nodes']=array();
		$messages=array(); //to get msg data

		//for each node get participants and unread messages for this user
		for($nc=0;$nc<count($nodes);$nc++)
		{
			//get unread messaged for this node
			/*
			$query ="SELECT m.msg_id AS mid,m.from AS fid, m.msg, m.time AS ts
			FROM #__jbolo_chat_msgs AS m
			LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
			WHERE m.to_node_id=".$nodes[$nc]->nid."
			AND mx.to_user_id =".$uid."
			AND mx.read = 0
			ORDER BY m.msg_id ";
			$db->setQuery($query);
			//$messages = $db->loadAssocList();
			$messages = $db->loadObjectList();
			*/
			//@TODO testing needed
			//uncomment block above if messages are not polled in chats
			$messages=$this->getUnreadMessages($nodes[$nc]->nid,$uid);

			//print_r($messages);
			if(count($messages))
			{
				//get node participants info
				$participants=$nodesHelper->getNodeParticipants($nodes[$nc]->nid,$uid);
				//$participants=$this->getNodeParticipants($nodes[$nc]->nid,$uid,0);
				//$wt=$nodesHelper->getNodeTitle($nodes[$nc]->nid,$uid,$nodes[$nc]->ctyp);
				//$tid=$participants['tid'];//important//@TODO why needed?

				//prepare json output for nodeinfo

				//modify nodeinfo
				/*$nodes[$nc]->uid=$uid;*/ //@TODO not sure why this assignment is for?
				/*$nodes[$nc]->tid=$tid;*/ //@TODO check for group chat title//@TODO remove??

				//Title for chat window
				$nodes[$nc]->wt=$nodesHelper->getNodeTitle($nodes[$nc]->nid,$uid,$nodes[$nc]->ctyp);
				//get chatbox status
				$nodes[$nc]->ns=$nodesHelper->getNodeStatus($nodes[$nc]->nid,$uid,$nodes[$nc]->ctyp);


				//push modified node info into output array
				$polling['nodes'][$nc]['nodeinfo']=$nodes[$nc];

				//prepare json output for node participants
				$polling['nodes'][$nc]['participants']=$participants['participants'];

				//prepare json output for unread messages
				$polling['nodes'][$nc]['messages']=$messages;

				//set read to 1 afer pushing a unread msg to session
				//update chat xref table
				for($k=0;$k<count($messages);$k++)
				{
					$msg_id=$messages[$k]->mid;
					$messages[$k]->ts=JFactory::getDate($messages[$k]->ts)->format(JText::_('COM_JBOLO_SENT_AT_FORMAT'));
					$db=JFactory::getDBO();
					$query="UPDATE #__jbolo_chat_msgs_xref AS x SET x.read=1
					WHERE x.read=0
					AND x.to_user_id=".$uid.
					" AND x.msg_id=".$msg_id;
					$db->setQuery($query);
					if(!$db->execute()){
						echo $db->stderr();
					}
				}

				//add msgs to session for particular node
				if(isset($_SESSION['jbolo']['nodes']))//if nodes array is set
				{
					$node_ids= array();
					$nodecount=count($_SESSION['jbolo']['nodes']);

					//get all node ids for nodes which are present in session
					for($d=0;$d<$nodecount;$d++)
					{
						if(isset($_SESSION['jbolo']['nodes'][$d])){
							$node_ids[$d]=$_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid;
						}
					}

					//if current node is not in session, add nodeinfo & particpants in session
					if(!in_array($nodes[$nc]->nid,$node_ids))
					{
						if($nodecount)
						{
							//if node data not in session, push new nodedata at end	of array
							//push nodeinfo
							$_SESSION['jbolo']['nodes'][$nodecount]['nodeinfo']=$nodes[$nc];
							//push node participants
							$_SESSION['jbolo']['nodes'][$nodecount]['participants']=$participants['participants'];
						}
						else
						{
							//if no node is present in session
							//add a new node in session
							//push nodeinfo
							$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$nodes[$nc];
							//push node participants
							$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
						}
					}

					//loop through all nodes
					for($k=0;$k<count($_SESSION['jbolo']['nodes']);$k++)
					{
						//if node found
						if($_SESSION['jbolo']['nodes'][$k]['nodeinfo']->nid==$nodes[$nc]->nid)
						{
							//this is important
							//update node participants
							$_SESSION['jbolo']['nodes'][$k]['participants']=$participants['participants'];

							$mcnt=0;//initialize mesasge count for node found to 0
							//check if the node found has messages stored in session
							if(isset($_SESSION['jbolo']['nodes'][$k]['messages']))
							{
								//if yes count msgs
								$mcnt=count($_SESSION['jbolo']['nodes'][$k]['messages']);
							}
							for($m=0;$m<count($messages);$m++)
							{
								//add new mesage at the end
								$_SESSION['jbolo']['nodes'][$k]['messages'][$mcnt]=$messages[$m];//changed
								$mcnt++;//increasemesage count for messages in session for current node
							}
						}
					}

					/*
					for($k=0;$k<count($_SESSION['jbolo']['nodes']);$k++)//loop through all nodes
					{
						if(isset($_SESSION['jbolo']['nodes'][$k]))//if node is set
						{
							if(isset($_SESSION['jbolo']['nodes'][$k]['nodeinfo']))	//added 21may 2012
							{
								if($_SESSION['jbolo']['nodes'][$k]['nodeinfo']['nid']==$nodes[$nc]['nid'])
								{
									$mcnt=0;
									if(isset($_SESSION['jbolo']['nodes'][$k]['messages']))
									{
										$mcnt=count($_SESSION['jbolo']['nodes'][$k]['messages']);
										//$mcnt++;
									}
									for($m=0;$m<count($messages);$m++)
									{
										$_SESSION['jbolo']['nodes'][$k]['messages'][$mcnt] = $messages[$m]; //changed
										$mcnt++;
									}
								}
							}
						}
						else//@TODO remaining...
						{


						}
					}//end for
					*/
				}//end if
				else//if no nodes in session
				{
					//if no node is present in session
					//add a new node in session
					//push nodeinfo
					$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$nodes[$nc];
					//push node participants
					$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
					//push unread messages
					$mcnt=0;
					for($m=0;$m<count($messages);$m++)
					{
						$_SESSION['jbolo']['nodes'][0]['messages'][$mcnt] = $messages[$m]; //changed
						$mcnt++;
					}
				}
			}//if messages
		}//for loop for nodes

		//print_r($polling);
		//echo "updated jbolo session"."<br/>";print_r($_SESSION['jbolo']);
		/*
		//log to file
		$logf=JPATH_SITE.'/components/com_jbolo/'.$uid.'.php';
		$f = @fopen($logf, 'a+');
		if ($f)
		{
			$log="";
			if(isset($_SESSION['jbolo'])){
				$log.="\n".'session o/p in polling'."\n";
				$log.= print_r($_SESSION['jbolo'], true);
			}
			$log.="\n".'polling o/p in polling'."\n";
			$log.= print_r($polling, true);
			//@fputs($f,$log);
			@fclose($f);
		}
		*/

		//check if nodes array is present in session
		if(isset($_SESSION['jbolo']['nodes']))
		{
			//use nodes helper
			$nodeStatusArray=$nodesHelper->getNodeStatusArray($_SESSION['jbolo']['nodes'],$uid);
			$polling['nsts']=$nodeStatusArray;
		}
		else{
			$polling['nsts']=array();
		}

		return $polling;
	}

	//get all unread messages for given user against given node
	function getUnreadMessages($nid,$uid)
	{
		$db=JFactory::getDBO();
		//get all unread messages against current node for this user
		$query ="SELECT m.msg_id AS mid,m.from AS fid, m.msg, m.time AS ts
		FROM #__jbolo_chat_msgs AS m
		LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
		WHERE m.to_node_id=".$nid."
		AND mx.to_user_id =".$uid."
		AND mx.read = 0
		ORDER BY m.msg_id ";
		$db->setQuery($query);
		//$messages = $db->loadAssocList();
		$messages=$db->loadObjectList();
		//print_r($messages);
		return $messages;
	}

	/*
	 * This function -
	 * -- Clears all chat messages stored in session against given node id
	 *
	 * INPUT VARIABLES-@POST
	 * nid:38
	 *
	 * OUTPUT VARIABLE-@JSON
	 * flag - @json
	 * 0 indicates - messages not found / not deleted
	 * 1 indicates - messages deleted
	 *  */
	function clearchat()
	{
		//validate user
		$response=$this->validateUserLogin();

		//print_r($_SESSION['jbolo']['nodes']);
		$input=JFactory::getApplication()->input;
		$post=$input->post;
		$nid=$input->post->get('nid','','INT');
		//validate if nid is not specified
		if(!$nid){
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_INVALID_NODE_ID');
			return $response;
		}

		$nodecount=count($_SESSION['jbolo']['nodes']);
		$flag=0;
		for($d=0;$d<$nodecount;$d++)
		{
			if(!$flag)//loop till required node is found
			{
				if(isset($_SESSION['jbolo']['nodes'][$d]))
				{
					//if node found
					if($_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid==$nid)
					{
						//check if messages are present
						if(isset($_SESSION['jbolo']['nodes'][$d]['messages']))
						{
							//unset messages array from session
							unset($_SESSION['jbolo']['nodes'][$d]['messages']);
						}
						$flag=1;
					}
				}
			}
			else
			{
				break;
			}
		}
		$response['all_clear']=$flag;
		//print_r($_SESSION['jbolo']['nodes']);
		return $response;
	}


	////////////////////////////////////////////
	////////////////Group chat /////////////////
	////////////////////////////////////////////

	//group chat function

	/* This function =>
	 * - adds a new user to current node for group chat
	 * - echoes json reponse
	 *
	 * INPUT VARIABLES-@POST

		//when added from 1to1 chat
		nid:1_1
		pid:778

		//when added from group chat
		nid:2_2
		pid:779
	 *
	 * OUTPUT VARIABLE-@JSON
	 * $ini_node - @json array

	 * */
	function addNodeUser()
	{
		//validate user
		$ini_node=$this->validateUserLogin();

		//get iputs from posted data
		$input=JFactory::getApplication()->input;
		$post=$input->post;
		$nid=$input->post->get('nid','','INT');// 10_1 OR 15_2

		//get nid
		$pieces=explode("_",$nid);
		$nid=$pieces[0];//10 OR 15
		$pid=$input->post->get('pid','','INT');

		//validate if nid is not specified
		if(!$nid){
			$ini_node['validate']->error=1;
			$ini_node['validate']->error_msg=JText::_('COM_JBOLO_INVALID_NODE_ID');
			return $ini_node;
		}

		//validate if pid is not specified
		if(!$pid){
			$ini_node['validate']->error=1;
			$ini_node['validate']->error_msg=JText::_('COM_JBOLO_INVALID_PARTICIPANT');
			return $ini_node;
		}

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;
		$params=JComponentHelper::getParams('com_jbolo');
		$maxChatUsers=$params->get('maxChatUsers');

		//use nodes helper
		$nodesHelper=new nodesHelper();

		//validate max allowed users for group chat
		$activeNodeParticipantsCount=$nodesHelper->getActiveNodeParticipantsCount($nid);
		if($activeNodeParticipantsCount>=$maxChatUsers)
		{
			$ini_node['validate']->error=1;
			$ini_node['validate']->error_msg=JText::_('COM_JBOLO_GC_MAX_USERS_LIMIT');
			return $ini_node;
		}

		//validate if this user is participant of this node
		$isNodeParticipant=$nodesHelper->isNodeParticipant($uid,$nid);
		if($isNodeParticipant==2)//error handling for inactive user
		{
			$ini_node['validate']->error=1;
			$ini_node['validate']->error_msg=JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG');
			return $ini_node;
		}
		if(!$isNodeParticipant)//error handling for not member/unauthorized access to this group chat
		{
			$ini_node['validate']->error=1;
			$ini_node['validate']->error_msg=JText::_('COM_JBOLO_NON_MEMBER_MSG');
			return $ini_node;
		}

		//get node type
		$nodeType=$nodesHelper->getNodeType($nid);//important

		if($nodeType==1)//if adding a new user to 1to1 chat
		{
			//create a new node for this group chat
			$myobj=new stdclass;
			$myobj->title=NULL;
			$myobj->type=2;
			$myobj->owner=$uid;
			$myobj->time=date("Y-m-d H:i:s");
			$db->insertObject('#__jbolo_nodes',$myobj);
			$db->stderr();
			$new_node_id=$db->insertid();

			if($new_node_id)//when new node is created
			{
				//get old node users
				$query="SELECT user_id
				FROM #__jbolo_node_users AS nu
				WHERE node_id=".$nid."";
				$db->setQuery($query);
				$old_node_users=$db->loadColumn();
				//print_r($old_node_users);

				//add participants from old node(i.e. current 1to1 node) to newly created group chat node
				for($i=0;$i<count($old_node_users);$i++)
				{
					$myobj=new stdclass;
					$myobj->node_id=$new_node_id;
					$myobj->user_id=$old_node_users[$i];
					$myobj->status=1;
					$db->insertObject('#__jbolo_node_users',$myobj);
					if($uid!=$myobj->user_id){
						$first_one2one_chat_user=$myobj->user_id;
					}
				}

				//after adding existing users from 1to1 chat, add new user to new node
				$myobj=new stdclass;
				$myobj->node_id=$new_node_id;
				$myobj->user_id=$pid;
				$myobj->status=1;
				$db->insertObject('#__jbolo_node_users',$myobj);

				//push welcome messages for actor and others
				$this->pushWelcomeMsgBroadcast('gbc',$new_node_id,0,1,$uid);
				//push invited messages for actor and others
				//invited first user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$first_one2one_chat_user);
				//the added user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$pid);
				//push who has joined messages to actor and others
				$this->pushJoinedMsgBroadcast('gbc',$new_node_id,0,1,$uid);
			}
		}
		else if($nodeType==2)//called from group chat
		{
			//check if user being added is already participant
			$isNodeParticipant=$nodesHelper->isNodeParticipant($pid,$nid);
			$new_node_id=$nid;//@TODO chk /test
			if(!$isNodeParticipant)
			{
				//after adding existing users from 1to1 chat add new user
				$myobj=new stdclass;
				$myobj->node_id=$nid;
				$myobj->user_id=$pid;
				$myobj->status=1;
				$db->insertObject('#__jbolo_node_users',$myobj);

				//push welcome message only to newly added user
				$particularUID=$pid;
				$this->pushWelcomeMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);
				//push invited messages for actor and others
				//the added user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$pid);
				//push who has joined messages to actor and others
				$this->pushJoinedMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);

			}
			elseif($isNodeParticipant==2)//re adding user
			{
				//re-add existing user
				$query="UPDATE #__jbolo_node_users
				SET status=1
				WHERE node_id=".$nid."
				AND user_id=".$pid."
				AND status=0";
				$db->setQuery($query);
				$db->execute();

				//use broadcast helper
				$chatBroadcastHelper=new chatBroadcastHelper();

				//push welcome message only to newly added user
				$particularUID=$pid;
				$this->pushWelcomeMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);
				//push invited messages for actor and others
				//the added user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$pid);
				//push who has joined messages to actor and others
				$this->pushJoinedMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);
			}
		}

		//$query="SELECT node_id AS nid, time AS ts, type AS ctyp
		$query="SELECT node_id AS nid, type AS ctyp
		FROM #__jbolo_nodes
		WHERE node_id=".$new_node_id;
		$db->setQuery($query);
		$node_d=$db->loadObject();

		$ini_node['nodeinfo']=$node_d;
		$ini_node['nodeinfo']->wt=$nodesHelper->getNodeTitle($new_node_id,$pid,$ini_node['nodeinfo']->ctyp);
		//get chatbox status
		$ini_node['nodeinfo']->ns=$nodesHelper->getNodeStatus($ini_node['nodeinfo']->nid,$pid,$ini_node['nodeinfo']->ctyp);
		$user=JFactory::getUser($pid);

		//add node data to session
		$d=0;
		//get node participants info
		$participants=$nodesHelper->getNodeParticipants($new_node_id,$pid);

		//prepare json output for node participants
		//$polling['nodes'][$nc]['participants']=$participants['participants'];

		if(isset($_SESSION['jbolo']['nodes']))//check if node array is set
		{
			$node_ids=array();
			for($d=0;$d<count($_SESSION['jbolo']['nodes']);$d++)
			{
				$node_info['nodeinfo']=array();
				if(isset($_SESSION['jbolo']['nodes'][$d]))
				{
					//$node_info['nodeinfo']=$_SESSION['jbolo']['nodes'][$d]['nodeinfo'];
					//get all node ids set in session
					$node_ids[$d]=$_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid;
				}
			}
			//print_r($node_ids);

			//if entry for node found in session, update it
			if(in_array($ini_node['nodeinfo']->nid,$node_ids))
			{
				//@TODO blackhole as of 0.2 beta
				//might be needed when user is added from GC
				//echo 'here';
				//set session[jbolo][nodes][nid][nodeinfo]=ini_node[nodeinfo]
				//$_SESSION['jbolo']['nodes'][$ini_node['nodeinfo']->nid]['nodeinfo'] =$ini_node['nodeinfo'];//??
				//push node participants
				//$_SESSION['jbolo']['nodes'][$d]['participants']=$participants['participants'];//?not sure if this needed?//
			}
			//@TODO else part need to check
			else//if node data not session, push new nodedata at end
			{
				$_SESSION['jbolo']['nodes'][$d]['nodeinfo']=$ini_node['nodeinfo'];
				//push node participants
				$_SESSION['jbolo']['nodes'][$d]['participants']=$participants['participants'];//??//
			}
		}
		else//if nodes array is not set, push new node at start
		{
			//@TODO 0 or $d here?
			$_SESSION['jbolo']['nodes'][0]['nodeinfo'] = $ini_node['nodeinfo'];
			$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];//??//
		}

		//print_r($_SESSION['jbolo']['nodes']);
		//header('Content-type: application/json');
		//echo json_encode($ini_node);
		//jexit();
		return $ini_node;
	}

	function pushWelcomeMsgBroadcast($msgType,$nid,$particularUID=0,$sendToActor=0,$uid)
	{
		//use broadcast helper
		$chatBroadcastHelper=new chatBroadcastHelper();
		$msg=JText::_('COM_JBOLO_GC_BC_WELCOME_MSG');
		$chatBroadcastHelper->pushChat($msgType,$nid,$msg,$particularUID,$sendToActor);
		return true;
	}//end function

	function pushInvitedMsgBroadcast($msgType,$nid,$particularUID=0,$sendToActor=0,$uid,$pid)
	{
		//use broadcast helper
		$chatBroadcastHelper=new chatBroadcastHelper();
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			//$chattitle='username';
			$msg=$broadcast_msg=JFactory::getUser($uid)->username.' <i>'.JText::_('COM_JBOLO_GC_INVITED').' </i>'.JFactory::getUser($pid)->username;
		}else{
			//$chattitle='name';
			$msg=$broadcast_msg=JFactory::getUser($uid)->name.' <i>'.JText::_('COM_JBOLO_GC_INVITED').' </i>'.JFactory::getUser($pid)->name;
		}

		$chatBroadcastHelper->pushChat($msgType,$nid,$msg,$particularUID,$sendToActor);
		return true;
	}//end function

	function pushJoinedMsgBroadcast($msgType,$nid,$particularUID=0,$sendToActor=0,$uid)
	{
		//$db=JFactory::getDBO();
		//use nodes helper
		$nodesHelper=new nodesHelper();
		$participants=$nodesHelper->getActiveNodeParticipants($nid);
		//use broadcast helper
		$chatBroadcastHelper=new chatBroadcastHelper();
		$msg="";
		foreach($participants as $p)
		{
			$msg.=$p->name.' <i>'.JText::_('COM_JBOLO_GC_JOINED')."</i><br/>";
			//$particularUID=$pid;
		}
		$chatBroadcastHelper->pushChat($msgType,$nid,$msg,$particularUID,$sendToActor);
		return true;
	}//end function


	/*
	 * This function -
	 * -- changes chat status & chat status message
	 *
	 * INPUT VARIABLES-@POST
	 * chat_sts:2
	 * stsm:hola chica
	 *
	 * NOTE - chat message is expectd all times but sts is not
	 *
	 * OUTPUT VARIABLE-@JSON
	 * change_sts_response - @json
	 * 0 indicates - error / unauthorized
	 * 1 indicates - success
	 *  */
	function change_status()
	{
		//validate user
		$response=$this->validateUserLogin();

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;
		//get inputs from posted data
		$input=JFactory::getApplication()->input;
		$sts=$input->post->get('sts','','INT');
		$stsm=$input->post->get('stsm','','STRING');

		//validate inputs
		//validate status
		if( $sts>=5 || $sts<0 ){
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_INVALID_STATUS');
			return $response;
		}

		//validate status - check if it contains allowed values
		//@TODO

		//add slashes & strip tags from status message
		$stsm=addslashes(strip_tags($stsm));

		$query="UPDATE #__jbolo_users SET status_msg='".$stsm."'";
		if($sts){//update chat sts only if it is there in posted data
			$query.=" , chat_status=".$sts;
		}
		$query.=" WHERE user_id=".$uid;
		$db->setQuery($query);
		if(!$db->execute())//error updating
		{
			//echo $db->stderr();
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_DB_ERROR');
		}else{//updated successfully
			$change_sts_response['change_sts_response']=1;
		}


		return $response;

	}// end change sts function

	/* This function =>
	 * - adds a new user to current node for group chat
	 * - echoes json reponse
	 *
	 * INPUT VARIABLES-@POST
		nid:9
	 *
	 * OUTPUT VARIABLE-@JSON
	 * $ini_node - @json array

	 * */
	function leaveChat()
	{
		//validate user
		$leaveChat_response=$this->validateUserLogin();

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$actorid=$user->id;

		//get node id
		$input=JFactory::getApplication()->input;
		$nid=$input->post->get('nid','','INT');//9

		//validate participant id - check if pid is specified
		if(!$nid){
			$leaveChat_response['validate']->error=1;
			$leaveChat_response['validate']->error_msg=JText::_('COM_JBOLO_INVALID_NODE_ID');;
			return $leaveChat_response;
		}
		$leaveChat_response=$this->validateNodeParticipant($actorid,$nid);

		//use nodes helper
		$nodesHelper=new nodesHelper();
		$nodeType=$nodesHelper->getNodeType($nid);//important Coz only group chat can be left

		if($nodeType==2)//called from group chat
		{
			//mark user as inactive for this group chat
			$query="UPDATE #__jbolo_node_users
			SET status=0
			WHERE node_id=".$nid."
			AND user_id=".$actorid;
			$db->setQuery($query);
			if(!$db->query($query))
			{
				echo $db->stderr();
				return false;
			}

			//broadcast msg
			$params=JComponentHelper::getParams('com_jbolo');
			//show username OR name
			if($params->get('chatusertitle')){
				//$chattitle='username';
				$broadcast_msg=JFactory::getUser($actorid)->username.' <i>'.JText::_('COM_JBOLO_GC_LEFT_CHAT_MSG').'</i>';
			}else{
				//$chattitle='name';
				$broadcast_msg=JFactory::getUser($actorid)->name.' <i>'.JText::_('COM_JBOLO_GC_LEFT_CHAT_MSG').'</i>';
			}

			//use broadcast helper
			$chatBroadcastHelper=new chatBroadcastHelper();
			//send to one who left chat
			$chatBroadcastHelper->pushChat('gbc',$nid,$broadcast_msg,$actorid,0);
			//send to all
			$chatBroadcastHelper->pushChat('gbc',$nid,$broadcast_msg,0,0);

			//set message to be sent back to ajax request
			$leaveChat_response['lcresponse']->msg=JText::_('COM_JBOLO_YOU').' '.JText::_('COM_JBOLO_GC_LEFT_CHAT_MSG');

			return $leaveChat_response;
		}
	}

	function getAutoCompleteUserList()
	{
		//validate user
		$response=$this->validateUserLogin();

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;

		$input=JFactory::getApplication()->input;
		$filterText=$input->post->get('filterText','','STRING');//e.g. user
		//validate if filterText is not blank
		if(!$filterText){
			$response['validate']->error=1;
			$response['validate']->error_msg=JText::_('COM_JBOLO_EMPTY_SEARCH_STRING');
			return $response;
		}
		//addslashes, user might enter anything to search
		$filterText=addslashes($filterText);

		//generate online user list
		//use users helper
		$usersHelper=new usersHelper();
		$data=$usersHelper->getAutoCompleteUserList($uid,$filterText);
		$response['userlist']['users']=$data;
		return $response;

	}//end of getAutoCompleteUserList

	/*
	 * This function -
	 * -- returns a comma separated list of nodes between for given 2 users
	 *
	 * INPUT VARIABLES-@POST
	 * onuser:776
	 * offuser:778
	 *
	 * OUTPUT VARIABLE-@JSON
	 * {"nodes":"40,41"}
	 * OR
	 * {"error":"No nodes found"}
	 *  */

	function getUserNodes()
	{
		$input=JFactory::getApplication()->input;
		$onuser=$input->post->get('onuser','','INT');
		$offuser=$input->post->get('offuser','','INT');

		$db = JFactory::getDBO();
		//check if a node already exists for these 2 users
		$query="SELECT nu.node_id AS nid, GROUP_CONCAT(nu.user_id) AS users
		FROM `#__jbolo_node_users` AS nu
		LEFT JOIN `#__jbolo_nodes` AS nd ON nd.node_id = nu.node_id
		WHERE nu.node_id IN
		(
			SELECT nd.node_id
			FROM `#__jbolo_nodes` AS nd
			WHERE nd.TYPE =1
			AND (nd.owner=".$onuser." OR nd.owner=".$offuser.")
		)
		GROUP BY nu.node_id";
		$db->setQuery($query);
		$data=$db->loadObjectList();

		//print_r($data);

		$node_id_found=NULL;//important
		$users1=$onuser.",".$offuser;//776,778
		$users2=$offuser.",".$onuser;//778,776
		$flag=0;
		$count=count($data);
		for($i=0;$i<$count;$i++)
		{
			//print_r($data[$i]);
			//if node found for 2 users, append to string
			if($data[$i]->users==$users1 || $data[$i]->users==$users2)
			{
				if(!$flag)
				{
					$flag=1;
					$node_id_found.=$data[$i]->nid;
				}else{
					$node_id_found.=','.$data[$i]->nid;
				}
			}
		}
		return $node_id_found;
	}

	function purgeChats()
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$purge=$params->get('enable_purge');
		if($purge)
		{
			if($params->get('purge_key')==JRequest::getVar('purge_key'))
			{
				if(! (int)$params->get('purge_days') )
				{
					echo "<div class='alert alert-error'>".
					JText::_('COM_JBOLO_PURGE_NON_ZERO_DAYS')
					."</div>";
				}
				else
				{
					$db=JFactory::getDBO();
					//first delete xref table entries
					$query="DELETE FROM #__jbolo_chat_msgs_xref
					WHERE msg_id IN (
						SELECT msg_id
						FROM #__jbolo_chat_msgs
						WHERE DATEDIFF('".date('Y-m-d')."',time) >=".$params->get('purge_days').")";
					$db->setQuery($query);
					if(! $db->execute($query))
					{
						echo $db->stderr();
						return false;
					}
					//then delete main chat entires
					$query="DELETE FROM #__jbolo_chat_msgs
					WHERE DATEDIFF('".date('Y-m-d')."',time) >=".$params->get('purge_days');
					$db->setQuery($query);
					if(! $db->execute($query))
					{
						echo $db->stderr();
						return false;
					}

					echo "<div class='alert alert-success'>".
					JText::_('COM_JBOLO_PURGE_OLD_CHATS')."<br/>".
					"</div>";
					//call purge files function
					$this->purgefiles();
				}
			}
		}
		return 1;
	}//end purge function

	//purge script
	private function purgefiles()
	{
		$params=JComponentHelper::getParams('com_jbolo');
		$uploaddir=JPATH_COMPONENT.DS.'uploads';
		$exp_file_time=3600*24*$params->get('purge_days');
		$currts=time();
		echo "<div class='alert alert-success'>".
		JText::_('COM_JBOLO_PURGE_OLD_FILES');
		/*
		 * JFolder::files($path,$filter,$recurse=false,$full=false,$exclude=array('.svn'),$excludefilter=array('^\..*'))
		*/
		$current_files=JFolder::files($uploaddir,'',1,true,array('index.html'));
		//print_r($current_files);die;
		foreach($current_files as $file)
		{
			if( $file != ".." && $file != "." )
			{
				$diffts=$currts-filemtime($file);
				if($diffts > $exp_file_time )
				{
					echo '<br/>'.JText::_('COM_JBOLO_PURGE_DELETING_FILE').$file;
					if(!JFile::delete($file)){
						echo '<br/>'.JText::_('COM_JBOLO_PURGE_ERROR_DELETING_FILE').'-'.$file;
					}
				}
			}
		}
		echo "</div>";
	}

	/*done2
	 * This function ->
	 * -- creates a new chat node or opens an existing node for given set of users
	 * -- if a new node is created it adds participants against this node
	 * -- it also updates session by adding new node data into session
	 * -- and finally echoes json reponse
	 *
	 * OUTPUT VARIABLE-@JSON
	 * @nodeinfo - json array
		nodeinfo: {
			ctyp: "1"
			nid: "1"
			wt: "user1"
		}
	 *
	 * called from -
	 * - contoller.php
	 * 	-- functions => initiateNode()
	 *
	 * calls -
	 * - models/nodes.php [same file]
	 * 	-- functions => validateUser()
	 * - helpers/nodes.php
	 * 	-- functions => checkNodeExists() getNodeTitle() getNodeStatus() getNodeParticipants()
	 */
	function initiateNode()
	{
		//validate user
		$ini_node=$this->validateUserLogin();

		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$uid=$user->id;

		//get participant from post
		$input=JFactory::getApplication()->input;
		$post=$input->post;
		$pid=$input->post->get('pid','','INT');//participant id e.g. 778

		//validate inputs
		//validate participant id - check if pid is specified
		if(!$pid){
			$ini_node['validate']->error=1;
			$ini_node['validate']->error_msg=JText::_('COM_JBOLO_INVALID_PARTICIPANT');
			return $ini_node;
		}

		//check if node exists
		//use nodesHelper
		$nodesHelper=new nodesHelper();
		$node_id_found=$nodesHelper->checkNodeExists($uid,$pid);

		//if no existing node found
		if(!$node_id_found)
		{
			//create new node
			$myobj=new stdclass;
			$myobj->title=NULL;
			$myobj->type=1;
			$myobj->owner=$uid;
			$myobj->time=date("Y-m-d H:i:s");//note
			$db->insertObject('#__jbolo_nodes',$myobj);
			//get last insert id
			$new_node_id=$db->insertid();
			if($db->insertid())
			{
				//add participants against newly created node
				for($i=0;$i<2;$i++)
				{
					$myobj=new stdclass;
					$myobj->node_id=$new_node_id;
					$myobj->user_id=($i==0) ? $uid:$pid;//add entry for both users one after other
					$myobj->status=1;
					$db->insertObject('#__jbolo_node_users',$myobj);
			   }
			}
		}
		else{//node already exists
			$new_node_id=$node_id_found;
		}

		//prepare json response
		$query="SELECT node_id AS nid, type AS ctyp
		FROM #__jbolo_nodes
		WHERE node_id=".$new_node_id;
		$db->setQuery($query);
		$node_d=$db->loadObject();
		//print_r($node_d);

		$ini_node['nodeinfo']=$node_d;
		//get chat window title(wt)
		$ini_node['nodeinfo']->wt=$nodesHelper->getNodeTitle($new_node_id,$uid,$ini_node['nodeinfo']->ctyp);
		//get chatbox status (node status - ns)
		$ini_node['nodeinfo']->ns=$nodesHelper->getNodeStatus($new_node_id,$uid,$ini_node['nodeinfo']->ctyp);

		//use nodesHelper
		//get participants list
		$participants=$nodesHelper->getNodeParticipants($new_node_id,$uid);

		//update this node info in session
		$d=0;
		if(!isset($_SESSION['jbolo']['nodes']))//check if 'nodes' array is set
		{
			//if nodes array is not set, push new node at the start in nodes array
			$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$ini_node['nodeinfo'];
			$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
		}
		else//if nodes array is set
		{
			$node_ids=array();
			$nodecount=count($_SESSION['jbolo']['nodes']);
			for($d=0;$d<$nodecount;$d++)
			{
				if(isset($_SESSION['jbolo']['nodes'][$d]))
				{
					//get all node ids set in session
					$node_ids[$d]=$_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid;
				}
			}
			//if the current node is not present in session,
			//we add it into session
			if(!in_array($ini_node['nodeinfo']->nid,$node_ids))
			{
				if($nodecount)//if nodecount is >0, push new node data at the end of array
				{
					$_SESSION['jbolo']['nodes'][$nodecount]['nodeinfo']=$ini_node['nodeinfo'];
					$_SESSION['jbolo']['nodes'][$nodecount]['participants']=$participants['participants'];
				}
				else//if nodecount is 0, push this at start i.e. 0th position in array
				{
					$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$ini_node['nodeinfo'];
					$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
				}
			}
		}
		/*
		//log to file
		$logf=JPATH_SITE.'/components/com_jbolo/'.$uid.'.php';
		$f = @fopen($logf, 'a+');
		if ($f) {
			$log="\n".'initiate node'."\n";
			if(isset($_SESSION['jbolo']))$log.= print_r($_SESSION['jbolo'], true);
			$log= print_r($ini_node, true);
			//@fputs($f,$log);
			@fclose($f);
		}
		*/
		return $ini_node;
	}
}
?>