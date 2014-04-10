<?php
/**
 * ------------------------------------------------------------------------
 * JU Backend Toolkit for Joomla 2.5/3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2010-2013 JoomUltra. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: JoomUltra Co., Ltd
 * Websites: http://www.joomultra.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldK2Tags extends JFormField
{
	protected $type = 'K2Tags';

	function getInput()
	{
		$db		= JFactory::getDbo();
		
		//Check to make sure K2 has been installed
		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php')) {
			return "<div id='".$this->id."' class='".$this->element['class']."'>".JText::_('K2_HAS_NOT_BEEN_INSTALLED')."</div>";
		}
		
		$query	= $db->getQuery(true);

		$query->select('t.id, t.name');
		$query->from('#__k2_tags AS t');
		$query->where('t.published = 1');
		$query->order('t.name');

		$db->setQuery($query);
		$tags = $db->loadAssocList();

		// Add select all options
		array_unshift($tags, array('id'=>'','name'=>JText::_('K2_ALL_TAGS')));

		// Initialize some field attributes
		$attr  = $this->multiple ? ' multiple="multiple"' : '';
		$attr .= count($tags) >= 20 ? ' size="20"' : (count($tags) <= 5 ? ' size="5"' : ' size="10"');
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		return JHTML::_('select.genericlist', $tags, $this->name, $attr, 'id', 'name', $this->value, $this->id);
	}
}