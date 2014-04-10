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

class JboloModelHistory extends JModelLegacy
{
	/**
	* Items total
	* @var integer
	*/
	var $_total = null;
	/**
	* Pagination object
	* @var object
	*/
	var $_pagination = null;

	function __construct()
	{
		parent::__construct();
		global $mainframe;
		$mainframe=JFactory::getApplication();
		//Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart=JFactory::getApplication()->input->get('limitstart','0','INT');
		//In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	function getData()
	{
		// if data hasn't already been obtained, load it
		if (empty($this->_data)) {
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

	function _buildQuery()
	{
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}
		$user=JFactory::getUser();
		$nodeid=JFactory::getApplication()->input->get->get('nid','','INT');

		$query ="SELECT DISTINCT(m.msg_id) AS mid, m.from AS fid, m.msg, m.time AS ts, u.$chattitle AS uname
		FROM #__jbolo_chat_msgs_xref AS mx
		LEFT JOIN #__jbolo_chat_msgs AS m ON m.msg_id=mx.msg_id
		LEFT JOIN #__users AS u ON u.id=m.from
		WHERE m.to_node_id=".$nodeid."
		AND (mx.to_user_id =".$user->id." OR m.from=".$user->id.")
		AND (mx.to_user_id =".$user->id." OR m.from=".$user->id." AND m.msg_type<>'file')
		ORDER BY m.msg_id DESC ";
		return $query;
	}

	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	function _loadData()
	{
		$user=JFactory::getUser();
		if (!$user->id) { return; }
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the pagination request variables
			$limitstart=JFactory::getApplication()->input->get('limitstart','0','INT');
			$limit=JFactory::getApplication()->input->get('limit','20','INT');
			$query=$this->_buildQuery();
			$Arows=$this->_getList($query, $limitstart, $limit);
			$this->_data=$Arows;
		}
		return true;
	}
}
?>