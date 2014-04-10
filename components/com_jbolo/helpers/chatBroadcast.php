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

class chatBroadcastHelper
{
	/*
	 * @param msgType
	 * @param nid
	 * @param msg
	 * @param particularUID
	 */
	function pushChat($msgType,$nid,$msg,$particularUID=0,$sendToActor=0)
	{
		$actorid=JFactory::getUser()->id;
		$db=JFactory::getDBO();

		//process text for urls & download links
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('jbolo','plg_jbolo_textprocessing');
		if($msgType=='file')
		{
			//process download link
			//note - another parameter passed here - particularUID
			$processedText=$dispatcher->trigger('processDownloadLink',array($msg,$particularUID));
		}
		else
		{
			//process urls
			$processedText=$dispatcher->trigger('processUrls',array($msg));
		}
		$msg=$processedText[0];
		//process smilies
		$processedText=$dispatcher->trigger('processSmilies',array($msg));
		$msg=$processedText[0];
		//process bad words
		$processedText=$dispatcher->trigger('processBadWords',array($msg));
		$msg=$processedText[0];

		//add msg to database
		$myobj=new stdclass;
		if($msgType=='gbc'){
			$myobj->from=0;//set userid to 0 for gbc messages
		}else{
			$myobj->from=$actorid;//set userid to 0 for gbc messages
		}
		$myobj->to_node_id=$nid;
		$myobj->msg=$msg;
		$myobj->msg_type=$msgType;
		$myobj->time=date("Y-m-d H:i:s");
		$myobj->sent=1;
		$db->insertObject('#__jbolo_chat_msgs',$myobj);
		//get last insert id
		$new_mid=$db->insertid();

		//update msg xref table
		if($new_mid)
		{
			if($particularUID)
			{
				$myobj= new stdclass;
				$myobj->msg_id=$new_mid;
				$myobj->node_id=$nid;
				$myobj->to_user_id=$particularUID;
				$myobj->delivered=0;
				$myobj->read=0;
				$db->insertObject('#__jbolo_chat_msgs_xref',$myobj);
			}
			else
			{
				$query="SELECT user_id
				FROM #__jbolo_node_users
				WHERE node_id = ".$nid."
				AND status=1";//status indicates of user is still part of node (only active users)
				if(!$sendToActor){
					$query.=" AND user_id <> ".$actorid;
				}
				$db->setQuery($query);
				$participant=$db->loadColumn();
				$count=count($participant);
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
return 1;
			//prepare json response //@TODO not used?
/*
			$query="SELECT chm.to_node_id AS nid, chm.from AS uid, chm.msg_id AS mid, chm.sent, chm.msg
			FROM #__jbolo_chat_msgs AS chm
			WHERE chm.msg_id=".$new_mid;
			$db->setQuery($query);
			$node_d=$db->loadObject();
			$pushChat_response['pushChat_response']=$node_d;
*/
			//add this msg to session
			$query ="SELECT m.msg_id AS mid, m.from AS fid, m.msg, m.time AS ts
			FROM #__jbolo_chat_msgs AS m
			LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
			WHERE m.msg_id=".$new_mid." AND m.sent=1";
			$db->setQuery($query);
			//$msg_dt=$db->loadAssocList();
			$msg_dt=$db->loadObject();

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

		}
	}
}