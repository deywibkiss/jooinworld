<?php
/**
 * @version		$Id: categories.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;
if (file_exists(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php')) {

	require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

	class K2ElementCategories extends K2Element
	{

		function fetchElement($name, $value, &$node, $control_name)
		{
			$db = JFactory::getDBO();

			$query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
			$db->setQuery($query);
			$mitems = $db->loadObjectList();
			$children = array();
			if ($mitems)
			{
				foreach ($mitems as $v)
				{
					if (K2_JVERSION != '15')
					{
						$v->title = $v->name;
						$v->parent_id = $v->parent;
					}
					$pt = $v->parent;
					$list = @$children[$pt] ? $children[$pt] : array();
					array_push($list, $v);
					$children[$pt] = $list;
				}
			}
			$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
			$mitems = array();
			$mitems[] = JHTML::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS'));

			foreach ($list as $item)
			{
				$item->treename = JString::str_ireplace('&#160;', ' -', $item->treename);
				$mitems[] = JHTML::_('select.option', $item->id, $item->treename);
			}

			$attributes = 'class="inputbox '.$this->element['class'].'"';
			if (K2_JVERSION != '15')
			{
				$attribute = K2_JVERSION == '25' ? $node->getAttribute('multiple') : $node->attributes()->multiple;
				if ($attribute)
				{
					$attributes .= ' multiple="multiple" size="10"';
				}
			}
			else
			{
				if ($node->attributes('multiple'))
				{
					$attributes .= ' multiple="multiple" size="10"';
				}
			}

			if (K2_JVERSION != '15')
			{
				$fieldName = $name;
			}
			else
			{
				$fieldName = $control_name.'['.$name.']';
				if ($node->attributes('multiple'))
				{
					$fieldName .= '[]';
				}
			}

			return JHTML::_('select.genericlist', $mitems, $fieldName, $attributes, 'value', 'text', $value, $this->id);
		}

	}

	class JFormFieldK2Categories extends K2ElementCategories
	{
		var $type = 'k2categories';
	}

	class JElementK2Categories extends K2ElementCategories
	{
		var $_name = 'k2categories';
	}
} else {
	class JFormFieldK2Categories extends JFormField
	{
		protected $type = 'K2Categories';

		function getInput()
		{
			return "<div id='".$this->id."' class='".$this->element['class']."'>".JText::_('K2_HAS_NOT_BEEN_INSTALLED')."</div>";
		}
	}
}