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

class JFormFieldEasyBlogCategories extends JFormField
{
	protected $type = 'EasyBlogCategories';

	function getInput()
	{
		//Check to make sure EasyBlog has been installed
		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_easyblog/easyblog.php')) {
			return "<div id='".$this->id."' class='".$this->element['class']."'>".JText::_('EASYBLOG_HAS_NOT_BEEN_INSTALLED')."</div>";
		}
		
		require_once( JPATH_ROOT . '/components/com_easyblog/models/categories.php' );
		
		$EasyBlogModelCategories = new EasyBlogModelCategories();
		$categorytree = $EasyBlogModelCategories->getCategoryTree();
		
		$cats = $cat = array();
		foreach($categorytree AS $category) {
			$cat['id'] = $category->id;
			$cat['name'] = str_repeat('- ', $category->depth).$category->title;
			$cats[] = $cat;
		}

		// Add select all options
		array_unshift($cats, array('id'=>'', 'name'=>JText::_('EASYBLOG_ALL_CATS')));

		// Initialize some field attributes
		$attr  = $this->multiple ? ' multiple="multiple"' : '';
		$attr .= count($cats) >= 20 ? ' size="20"' : (count($cats) <= 5 ? ' size="5"' : ' size="10"');
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		return JHTML::_('select.genericlist', $cats, $this->name, $attr, 'id', 'name', $this->value, $this->id);
	}
}