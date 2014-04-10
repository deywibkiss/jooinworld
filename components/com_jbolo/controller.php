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

class JboloController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = false){
		parent::display();
	}

	/*
	 * startChatSession
	 *
	 * */
	function startChatSession()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - startChatSession
		$startChatSession=$nodesModel->startChatSession();
		//output json response
		header('Content-type: application/json');
		echo json_encode($startChatSession);
		jexit();
	}

	/*
	 * pushChatToNode function
	 *
	 * */
	function pushChatToNode()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - pushChatToNode
		$pushChatToNode=$nodesModel->pushChatToNode();
		//output json response
		header('Content-type: application/json');
		echo json_encode($pushChatToNode);
		jexit();
	}

	/*
	 * polling function
	 *
	 * */
	function polling()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - polling
		$polling=$nodesModel->polling();
		//output json response
		header('Content-type: application/json');
		echo json_encode($polling);
		jexit();
	}

	/*
	 * clearchat function
	 *
	 * */
	function clearchat()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - clearchat
		$clearchat=$nodesModel->clearchat();
		//output json response
		header('Content-type: application/json');
		echo json_encode($clearchat);
		jexit();
	}

	/*
	 * change_status function
	 *
	 * */
	function change_status()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - change_status
		$change_status=$nodesModel->change_status();
		//output json response
		header('Content-type: application/json');
		echo json_encode($change_status);
		jexit();
	}

	/*
	 * getAutoCompleteUserList function
	 *
	 * */
	function getAutoCompleteUserList()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - getAutoCompleteUserList
		$getAutoCompleteUserList=$nodesModel->getAutoCompleteUserList();
		//output json response
		header('Content-type: application/json');
		echo json_encode($getAutoCompleteUserList);
		jexit();
	}

	/*
	 * addNodeUser - group chat function
	 * This function =>
	 * - adds a new user to current node for group chat
	 *
	 * */
	function addNodeUser()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - addNodeUser
		$ini_node=$nodesModel->addNodeUser();
		//output json response
		header('Content-type: application/json');
		echo json_encode($ini_node);
		jexit();
	}

	/*
	 * getUserNodes function
	 * - needed to check when user goes offline
	 *
	 * */
	function getUserNodes()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - getUserNodes
		$node_id_found=$nodesModel->getUserNodes();
		//output json response
		header('Content-type: application/json');
		if(isset($node_id_found)){
			echo json_encode(array('nodes'=>$node_id_found));
		}else{
			echo json_encode(array('error'=>JText::_('COM_JBOLO_NO_NODES_FOUND')));
		}
		jexit();
	}

	/*
	 * purgeChats
	 *
	 * */
	function purgeChats()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - startChatSession
		$purgeChats=$nodesModel->purgeChats();
	}

	/*
	 * initiateNode
	 */
	function initiateNode()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - initiateNode
		$ini_node=$nodesModel->initiateNode();
		//output json response
		header('Content-type: application/json');
		echo json_encode($ini_node);
		jexit();
	}

	/*
	 * leavechat - group chat function
	 */
	function leavechat()
	{
		//get nodes model
		$nodesModel=$this->getModel('nodes');
		//call model function - leavechat
		$leavechat=$nodesModel->leavechat();
		//output json response
		header('Content-type: application/json');
		echo json_encode($leavechat);
		jexit();
	}
}//class ends
?>