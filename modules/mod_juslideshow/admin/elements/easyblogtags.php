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

class JFormFieldEasyBlogTags extends JFormField
{
	protected $type = 'EasyBlogTags';

	function getInput()
	{
		$db		= JFactory::getDbo();
		
		//Check to make sure EasyBlog has been installed
		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_easyblog/easyblog.php')) {
			return "<div id='".$this->id."' class='".$this->element['class']."'>".JText::_('EASYBLOG_HAS_NOT_BEEN_INSTALLED')."</div>";
		}
		
		$query	= $db->getQuery(true);

		$query->select('t.id, t.title AS name');
		$query->from('#__easyblog_tag AS t');
		$query->where('t.published = 1');
		$query->order('t.title');

		$db->setQuery($query);
		$tags = $db->loadAssocList();

		// Add select all options
		array_unshift($tags, array('id'=>'','name'=>JText::_('EASYBLOG_ALL_TAGS')));

		// Initialize some field attributes
		$attr  = $this->multiple ? ' multiple="multiple"' : '';
		$attr .= count($tags) >= 20 ? ' size="20"' : (count($tags) <= 5 ? ' size="5"' : ' size="10"');
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		return JHTML::_('select.genericlist', $tags, $this->name, $attr, 'id', 'name', $this->value, $this->id);
	}
}