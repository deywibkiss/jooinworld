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
class jboloHelper
{
	function getItemId($link,$skipIfNoMenu=0)
	{
		$itemid=0;
		$mainframe=JFactory::getApplication();
		if($mainframe->issite())
		{
			//$menu=JSite::getMenu();
			$menu=JFactory::getApplication()->getMenu();
			$items=$menu->getItems('link',$link);
			if(isset($items[0])){
				$itemid=$items[0]->id;
			}
		}
		if(!$itemid)
		{
			$db=JFactory::getDBO();
			$query="SELECT id FROM ".$db->quoteName('#__menu')."
			WHERE link LIKE '%".$link."%'
			AND published =1";
			if(JVERSION<'3.0.0'){
				$query.=" ORDER BY ordering";
			}
			$query.=" LIMIT 1";
			$db->setQuery($query);
			$itemid=$db->loadResult();
		}
		if(!$itemid)
		{
			if($skipIfNoMenu)
				$itemid=0;
			else
			{
				$input=JFactory::getApplication()->input;
				$itemid=$input->get('Itemid','0','INT');
			}
		}
		return $itemid;
	}
}
?>