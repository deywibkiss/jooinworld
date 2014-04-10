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

class JboloModelCp extends JModelLegacy
{
	function __construct()
	{
		$mainframe=JFactory::getApplication();
		// Call the parents constructor
		parent::__construct();
	}

	/**
	 * Returns array of nodetypes and count of no. of nodes of each type
	 *
	 * @return array
	 *
	 * @since JBolo 3.0
	 */
	function getNodeTypesArray()
	{
		$db=JFactory::getDBO();
		$query="SELECT n.type, count(n.node_id) AS count
		FROM #__jbolo_nodes AS n
		GROUP BY n.type";
		$db->setQuery($query);
		$nodes=$db->loadObjectList();
		$count=count($nodes);
		//set default counts
		$nodes['one2oneChatsCount']=0;
		$nodes['groupChatsCount']=0;
		if($nodes)
		{
			for($i=0;$i<$count;$i++)
			{
				if($nodes[$i]->type==1)
					$nodes['one2oneChatsCount']=$nodes[$i]->count;
				if($nodes[$i]->type==2)
					$nodes['groupChatsCount']=$nodes[$i]->count;
			}
		}
		return $nodes;
	}

	/**
	 * Returns array of messages types and count of no. of messages of each type
	 *
	 * @return array
	 *
	 * @since JBolo 3.0
	 */
	function getMessageTypesArray()
	{
		$db=JFactory::getDBO();
		$query="SELECT cm.msg_type AS type, count(cm.msg_id) AS count
		FROM #__jbolo_chat_msgs AS cm
		GROUP BY cm.msg_type";
		$db->setQuery($query);
		$msgTypes=$db->loadObjectList();
		$count=count($msgTypes);
		//set default counts
		$msgTypes['txtMsgs']=0;
		$msgTypes['fileMsgs']=0;
		if($msgTypes)
		{
			for($i=0;$i<$count;$i++)
			{
				if($msgTypes[$i]->type=='txt')//text messages
					$msgTypes['txtMsgs']=$msgTypes[$i]->count;
				if($msgTypes[$i]->type=='file')//file transfer messages
					$msgTypes['fileMsgs']=$msgTypes[$i]->count;
			}
		}
		return $msgTypes;
	}

	/**
	 * Returns array of number of mesages exchanged per day for 7 applicable days
	 *
	 * @return array
	 *
	 * @since JBolo 3.0
	 */
	function getMessagesPerDayArray()
	{
		$db=JFactory::getDBO();
		$date_today=date('Y-m-d');//PHP date format Y-m-d to match sql date format is 2013-05-15

		//set dates for past 6 days in an array
		$msgsPerDay=array();
		for($i=6,$k=0;$i>0;$i--,$k++){
			$msgsPerDay[$k]=new stdClass();
			$msgsPerDay[$k]->date=date('Y-m-d', strtotime(date('Y-m-d').' - '.$i.' days'));
		}
		//get today's date
		$msgsPerDay[$k]=new stdClass();
		$msgsPerDay[$k]->date=date('Y-m-d');

		//find number of messages per day
		for($i=6;$i>=0;$i--){
			//date format here is 2013-05-15
			$query="SELECT count(cm.msg_id) AS count
			FROM #__jbolo_chat_msgs AS cm
			WHERE date(cm.time)='".$msgsPerDay[$i]->date."'";
			$db->setQuery($query);
			$count=$db->loadResult();
			if($count){
				$msgsPerDay[$i]->count=$count;
			}else{
				$msgsPerDay[$i]->count=0;
			}
		}
		return $msgsPerDay;
	}
}
?>