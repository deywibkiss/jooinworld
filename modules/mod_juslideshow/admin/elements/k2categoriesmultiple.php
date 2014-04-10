<?php
/**
 * @version		$Id: categoriesmultiple.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

if (file_exists(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php')) {
	require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

	class K2ElementCategoriesMultiple extends K2Element
	{

		function fetchElement($name, $value, &$node, $control_name)
		{
			$params = JComponentHelper::getParams('com_k2');
			$document = JFactory::getDocument();
			if (version_compare(JVERSION, '1.6.0', 'ge'))
			{
				JHtml::_('behavior.framework');
			}
			else
			{
				JHTML::_('behavior.mootools');
			}
			//K2HelperHTML::loadjQuery();

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
			
			$mitems[] = JHTML::_('select.option', '', JText::_('K2_ALL_CATS'));

			foreach ($list as $item)
			{
				$item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
				$mitems[] = JHTML::_('select.option', $item->id, '   '.$item->treename);
			}

			$doc = JFactory::getDocument();
			if (K2_JVERSION != '15')
			{
				$js = "
				JU_jQuery(document).ready(function($){
					
					\$('#jform_params_catfilter0').click(function(){
						\$('#jformparamscategory_id').attr('disabled', 'disabled');
						\$('#jformparamscategory_id option').each(function() {
							\$(this).attr('selected', 'selected');
						});
						\$('#jformparamscategory_id').trigger('liszt:updated');
					});
					
					\$('#jform_params_catfilter1').click(function(){
						\$('#jformparamscategory_id').removeAttr('disabled');
						\$('#jformparamscategory_id option').each(function() {
							\$(this).removeAttr('selected');
						});
						\$('#jformparamscategory_id').trigger('liszt:updated');
					});
					
					if (\$('#jform_params_catfilter0').attr('checked')) {
						\$('#jformparamscategory_id').attr('disabled', 'disabled');
						\$('#jformparamscategory_id option').each(function() {
							\$(this).attr('selected', 'selected');
						});
						\$('#jformparamscategory_id').trigger('liszt:updated');
					}
					
					if (\$('#jform_params_catfilter1').attr('checked')) {
						\$('#jformparamscategory_id').removeAttr('disabled');
						\$('#jformparamscategory_id').trigger('liszt:updated');
					}
					
				});
				";

			}
			else
			{
				$js = "
				JU_jQuery(document).ready(function($){
					
					\$('#paramscatfilter0').click(function(){
						\$('#paramscategory_id').attr('disabled', 'disabled');
						\$('#paramscategory_id option').each(function() {
							\$(this).attr('selected', 'selected');
						});
					});
					
					\$('#paramscatfilter1').click(function(){
						\$('#paramscategory_id').removeAttr('disabled');
						\$('#paramscategory_id option').each(function() {
							\$(this).removeAttr('selected');
						});
		
					});
					
					if (\$('#paramscatfilter0').attr('checked')) {
						\$('#paramscategory_id').attr('disabled', 'disabled');
						\$('#paramscategory_id option').each(function() {
							\$(this).attr('selected', 'selected');
						});
					}
					
					if (\$('#paramscatfilter1').attr('checked')) {
						\$('#paramscategory_id').removeAttr('disabled');
					}
					
				});
				";

			}

			if (K2_JVERSION != '15')
			{
				$fieldName = $name.'[]';
			}
			else
			{
				$fieldName = $control_name.'['.$name.'][]';
			}

			$doc->addScriptDeclaration($js);
			
			$output = JHTML::_('select.genericlist', $mitems, $fieldName, 'class="inputbox '.$this->element['class'].'" multiple="multiple" size="10"', 'value', 'text', $value, $this->id);
			return $output;
		}

	}

	class JFormFieldK2CategoriesMultiple extends K2ElementCategoriesMultiple
	{
		var $type = 'k2categoriesmultiple';
	}

	class JElementK2CategoriesMultiple extends K2ElementCategoriesMultiple
	{
		var $_name = 'k2categoriesmultiple';
	}
} else {
	class JFormFieldK2CategoriesMultiple extends JFormField
	{
		protected $type = 'K2CategoriesMultiple';

		function getInput()
		{
			return "<div id='".$this->id."' class='".$this->element['class']."'>".JText::_('K2_HAS_NOT_BEEN_INSTALLED')."</div>";
		}
	}
}