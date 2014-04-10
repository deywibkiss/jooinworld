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

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');


class JFormFieldJUAnimation extends JFormField {
    protected $type = 'JUAnimation';
    
	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		return '';
	}
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
    protected function getInput() {
		$document = JFactory::getDocument();
		
		$document->addScript(JURI::root() . 'modules/' . $this->form->getValue('module') . '/admin/js/juanimation.min.js');
		$document->addStyleSheet(JURI::root() . 'modules/' . $this->form->getValue('module') . '/admin/css/juanimation.css');
		
		//Add language strings to use in .js files
		JText::script('UPDATE_AND_GO_TO_ANIMATION_CODE');
		JText::script('INVALID_HTML_TAG');
		JText::script('INVALID_CLASS');
		JText::script('INVALID_STYLE');
		JText::script('ANIMATION_CODE_CONTAINS_MORE_THAN_ONE_ELEMENT');
		
		$easing_selection = '<select class="easing">
			<option selected="selected" value="">'.JText::_('MOD_JUSLIDESHOW_SELECT_EASING').'</option>
			<option value="linear">Linear</option>
			<option value="swing">Swing</option>
			<option value="easeInQuad">easeInQuad</option>
			<option value="easeOutQuad">easeOutQuad</option>
			<option value="easeInOutQuad">easeInOutQuad</option>
			<option value="easeInCubic">easeInCubic</option>
			<option value="easeOutCubic">easeOutCubic</option>
			<option value="easeInOutCubic">easeInOutCubic</option>
			<option value="easeInQuart">easeInQuart</option>
			<option value="easeOutQuart">easeOutQuart</option>
			<option value="easeInOutQuart">easeInOutQuart</option>
			<option value="easeInQuint">easeInQuint</option>
			<option value="easeOutQuint">easeOutQuint</option>
			<option value="easeInOutQuint">easeInOutQuint</option>
			<option value="easeInSine">easeInSine</option>
			<option value="easeOutSine">easeOutSine</option>
			<option value="easeInOutSine">easeInOutSine</option>
			<option value="easeInExpo">easeInExpo</option>
			<option value="easeOutExpo">easeOutExpo</option>
			<option value="easeInOutExpo">easeInOutExpo</option>
			<option value="easeInCirc">easeInCirc</option>
			<option value="easeOutCirc">easeOutCirc</option>
			<option value="easeInOutCirc">easeInOutCirc</option>
			<option value="easeInElastic">easeInElastic</option>
			<option value="easeOutElastic">easeOutElastic</option>
			<option value="easeInOutElastic">easeInOutElastic</option>
			<option value="easeInBack">easeInBack</option>
			<option value="easeOutBack">easeOutBack</option>
			<option value="easeInOutBack">easeInOutBack</option>
			<option value="easeInBounce">easeInBounce</option>
			<option value="easeOutBounce">easeOutBounce</option>
			<option value="easeInOutBounce">easeInOutBounce</option>
		</select>';

		//Animation form
		$html  = '<div class="juanimation '.$this->element['class'].'" id="'.$this->id.'">';
		$html .= '<div class="el-tag"><div class="title">'.JText::_('MOD_JUSLIDESHOW_HTML_TAG').'</div><div class="config"><input class="el-tag" type="text" size="50" placeholder="'.JText::_('MOD_JUSLIDESHOW_HTML_TAG_DESC').'"/></div><span class="gotoanimationcode">'.JText::_('MOD_JUSLIDESHOW_GO_TO_ANIMATION_CODE').'</span></div>';
		$html .= '<div class="el-class"><div class="title">'.JText::_('MOD_JUSLIDESHOW_CLASS').'</div><div class="config"><input class="el-class" type="text" size="50" placeholder="'.JText::_('MOD_JUSLIDESHOW_CLASS_DESC').'"/></div></div>';
		$html .= '<div class="el-style"><div class="title">'.JText::_('MOD_JUSLIDESHOW_STYLE').'</div><div class="config"><textarea class="el-style" cols="30" rows="2" placeholder="'.JText::_('MOD_JUSLIDESHOW_STYLE_DESC').'"></textarea></div></div>';
		$html .= '<div class="el-html"><div class="title">'.JText::_('MOD_JUSLIDESHOW_INNER_HTML').'</div><div class="config"><textarea class="el-html" cols="30" rows="5" placeholder="'.JText::_('MOD_JUSLIDESHOW_INNER_HTML_DESC').'"></textarea></div></div>';
		$html .= '<div class="animation-from"><div class="title">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_FROM').'</div><div class="config"><textarea class="show-from" cols="30" rows="2" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_CSS_SHOWFROM').'"></textarea></div></div>';
		$html .= '<div class="animation-showto"><div class="title">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_SHOWTO').'</div><span class="clearshowto action" title="'.JText::_('MOD_JUSLIDESHOW_CLEAR').'">'.JText::_('MOD_JUSLIDESHOW_CLEAR').'</span><div class="config"><input class="delay validate-numeric" type="text" size="10" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_DELAY').'"/><input class="duration validate-numeric" type="text" size="10" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_DURATION').'"/>' . $easing_selection . '<textarea class="css" cols="30" rows="2" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_CSS_SHOWTO').'"></textarea></div></div>';
		$html .= '<div class="animation-timeline"><div class="title">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_TIMELINE').'</div><span class="addtimeline action" title="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_ADD_TIMELINE').'">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_ADD_TIMELINE').'</span>';
		$html .= '<div class="clone"><span class="insertbeforetimeline action" title="'.JText::_('MOD_JUSLIDESHOW_INSERT_BEFORE_TIMELINE').'">'.JText::_('MOD_JUSLIDESHOW_INSERT_BEFORE_TIMELINE').'</span><span class="insertaftertimeline action" title="'.JText::_('MOD_JUSLIDESHOW_INSERT_AFTER_TIMELINE').'">'.JText::_('MOD_JUSLIDESHOW_INSERT_AFTER_TIMELINE').'</span><span class="removetimeline action" title="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_REMOVE_TIMELINE').'">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_REMOVE_TIMELINE').'</span><input class="delay validate-numeric" type="text" size="10" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_TIME').'"/><input class="duration validate-numeric" type="text" size="10" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_DURATION').'"/>' . $easing_selection . '<textarea class="css" cols="30" rows="2" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_CSS_TIMELINE').'"></textarea></div>';
		$html .= '</div>';
		$html .= '<div class="animation-hideto"><div class="title">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_HIDETO').'</div><span class="clearhideto action" title="'.JText::_('MOD_JUSLIDESHOW_CLEAR').'">'.JText::_('MOD_JUSLIDESHOW_CLEAR').'</span><div class="config"><input class="delay validate-numeric" type="text" size="10" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_DELAY').'"/><input class="duration validate-numeric" type="text" size="10" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_DURATION').'"/>' . $easing_selection . '<textarea class="css" cols="30" rows="2" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_CSS_HIDETO').'"></textarea></div></div>';
		$html .= '<div class="animation-code"><div class="title">'.JText::_('MOD_JUSLIDESHOW_ANIMATION_CODE').'</div><div class="config"><textarea class="animation-code" cols="50" rows="5" placeholder="'.JText::_('MOD_JUSLIDESHOW_ANIMATION_CODE_DESC').'"></textarea></div></div>';
		$html .= '<div class="animation-action"><div class="config"><button class="generatecode">'.JText::_('MOD_JUSLIDESHOW_GENERATE_CODE').'</button><button class="parsecode">'.JText::_('MOD_JUSLIDESHOW_PARSE_CODE').'</button><button class="reset">'.JText::_('MOD_JUSLIDESHOW_RESET').'</button></div><span class="gototop">'.JText::_('MOD_JUSLIDESHOW_GO_TO_TOP').'</span><span class="gotogallery">'.JText::_('MOD_JUSLIDESHOW_GO_TO_GALLERY').'</span></div>';
		$html .= '</div>';
		
		return $html;
    }	
}