<?php
 /**
 * ------------------------------------------------------------------------
 * JU Slideshow Module for Joomla 2.5/3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2010-2013 JoomUltra. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: JoomUltra Co., Ltd
 * Websites: http://www.joomultra.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';
$ModJUSlideshowHelper = new ModJUSlideshowHelper($module);
//Load params that mixed with layout params, this params will be used instead default DB params in all the module
$_params = $ModJUSlideshowHelper->loadParams();

//If maxitem == 0 -> do nothing
if (!$_params->get('maxitems',15)) {
	return '';
}

require_once dirname(__FILE__).'/timthumb/timthumb.php';

$moduleclass_sfx = htmlspecialchars($_params->get('moduleclass_sfx'));

$layout			= $_params->get('layout', 'default');
$layout_theme	= $_params->get('layout_theme', 'simple');

$mainframe = JFactory::getApplication();
$document = JFactory::getDocument();

//Load main module styles
JHTML::stylesheet('modules/' . $module->module . '/assets/css/style.css');

//Caption format
JHTML::stylesheet('modules/' . $module->module . '/assets/css/caption.css');

//Load main module style in template if exist
if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'css' . DS . $module->module . ".css")) {
	JHTML::stylesheet( 'templates/'.$mainframe->getTemplate().'/css/'.$module->module.".css");
}

//Load layout style(File in template has higher priority)
if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . $layout . DS . 'style.css')) {
	JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/' . $layout . '/style.css');
} elseif (is_file(JPATH_SITE . DS . 'modules' . DS . $module->module . DS . 'tmpl' . DS . $layout . DS . 'style.css')) {
	JHTML::stylesheet('modules/' . $module->module . '/tmpl/' . $layout . '/style.css');
}

//Load layout theme style(File in template has higher priority)
if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . $layout . DS . 'themes' . DS . $layout_theme . DS . 'style.css')) {
	JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/' . $layout . '/themes/' . $layout_theme . '/style.css');
} elseif (is_file(JPATH_SITE . DS . 'modules' . DS . $module->module . DS . 'tmpl' . DS . $layout . DS . 'themes' . DS . $layout_theme . DS . 'style.css')) {
	JHTML::stylesheet('modules/' . $module->module . '/tmpl/' . $layout . '/themes/' . $layout_theme . '/style.css');
}

//Load responsive stlye of layout theme(File in template has higher priority)
if($_params->get('responsive', 'false') == 'true') {
	if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . $layout . DS . 'themes' . DS . $layout_theme . DS . 'responsive.css')) {
		JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/' . $layout . '/themes/' . $layout_theme . '/responsive.css');
	} elseif (is_file(JPATH_SITE . DS . 'modules' . DS . $module->module . DS . 'tmpl' . DS . $layout . DS . 'themes' . DS . $layout_theme . DS . 'responsive.css')) {
		JHTML::stylesheet('modules/' . $module->module . '/tmpl/' . $layout . '/themes/' . $layout_theme . '/responsive.css');
	}
}

//Load css animation(Template file can override module file)
if($_params->get('label_animation', 'default') == 'custom_animation') {
	if (is_file(JPATH_SITE . DS . 'modules' . DS . $module->module . DS . 'assets' . DS . 'css' . DS . 'animation.css')) {
		JHTML::stylesheet('modules/' . $module->module . '/assets/css/animation.css');
	}
	if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . 'animation.css')) {
		JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/animation.css');
	}
}

//Load css animation-responsive(Template file can override module file)
if($_params->get('responsive', 'false') == 'true') {
	if (is_file(JPATH_SITE . DS . 'modules' . DS . $module->module . DS . 'assets' . DS . 'css' . DS . 'animation-responsive.css')) {
		JHTML::stylesheet('modules/' . $module->module . '/assets/css/animation-responsive.css');
	}
	if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . 'animation-responsive.css')) {
		JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/animation-responsive.css');
	}
}

//Load Google fonts
$googlefonts = $_params->get('googlefonts','');
$googlefont_arr = explode("\n", $googlefonts);
if(count($googlefont_arr)) {
	foreach($googlefont_arr AS $googlefont) {
		$googlefont = str_replace(' ', '+', trim($googlefont));
		if($googlefont) {
			JHTML::stylesheet('http://fonts.googleapis.com/css?family=' . $googlefont);
		}
	}
}

$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.juslideshow.min.js");
$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.easing.1.3.min.js");

if ($_params->get('mousewheel','false') == 'true') {
	$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.mousewheel.min.js");
}

if ($_params->get('touch','true') == 'true') {
	$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.touchswipe.min.js");
}

if ($_params->get('focus_fullscreen','false') == 'true') {
	$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/screenfull.min.js");
}

$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.transform2d.min.js");
//$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.transform3d.min.js");

$document->addScript(JURI::Base(true)."/modules/".$module->module."/assets/js/jquery.effects.core.min.js");

$slide_param = array();

$slide_param['velocity'] 	= $_params->get('velocity','1');
$slide_param['interval'] 	= $_params->get('interval','4000');
$slide_param['animation'] 	= "'" . $_params->get('animation','') . "'";
$with_animations = $_params->get('with_animations');
if ( !empty($with_animations) ) {
	$with_animations_str 	= "['".implode("','", $_params->get('with_animations'))."']";
} else {
	$with_animations_str 	= "[]";
}
$slide_param['with_animations'] = $with_animations_str;
if ( $_params->get('navigation','2') == '3' ) {
	$slide_param['numbers'] = "true";
} else {
	$slide_param['numbers'] = "false";
}
$slide_param['nextprev_btn'] = $_params->get('nextprev_btn','true');
if ( $_params->get('navigation','2') ) {
	$slide_param['navigation'] = "true";
} else {
	$slide_param['navigation'] = "false";
}
if ($_params->get('label','1')=='1') {
	$label = "true";
	$label_when_mouseover = 'false';
} elseif ($_params->get('label','1')=='2') {
	$label = "true";
	$label_when_mouseover = "true";
} else {
	$label = "false";
	$label_when_mouseover = "false";
}
$slide_param['startimage'] 				= $_params->get('startimage','0');
$slide_param['responsive'] 				= $_params->get('responsive','false');
$slide_param['label'] 						= $label;
$slide_param['label_when_mouseover'] 		= $label_when_mouseover;
$slide_param['label_opacity'] 				= $_params->get('label_opacity','0.9');
$slide_param['label_animation'] 			= "'" . $_params->get('label_animation','default')."'";
$slide_param['label_animate_duration_in'] 	= $_params->get('label_animate_duration_in','400');
$slide_param['label_animate_duration_out'] = $_params->get('label_animate_duration_out','400');
$slide_param['label_delay'] 				= $_params->get('label_delay','0');
$slide_param['label_easing_in'] 			= "'" . $_params->get('label_easing_in','linear') . "'";
$slide_param['label_easing_out'] 			= "'" . $_params->get('label_easing_out','linear') . "'";
$slide_param['easing_default'] 			= "'" . $_params->get('easing_default','') . "'";
$slide_param['animateNumberOver'] 			= $_params->get('animateNumberOver',"{}");
$slide_param['animateNumberActive'] 		= $_params->get('animateNumberActive',"{}");
$slide_param['animateNumberOut'] 			= $_params->get('animateNumberOut',"{}");
if ( $_params->get('navigation','2') == '1' && $_params->get('fullscreen','false') != 'true' ) {
	$slide_param['thumbs'] = "true";
} else {
	$slide_param['thumbs'] = "false";
}
$slide_param['mousewheel'] 				= $_params->get('mousewheel','true');
$slide_param['touch'] 						= $_params->get('touch','true');
$slide_param['thumbs_mode'] 				= "'" . $_params->get('thumbs_mode','horizontal') . "'";
$slide_param['thumbs_horizontal_position'] = "'" . $_params->get('thumbs_horizontal_position','center') . "'";
$slide_param['thumbs_vertical_position'] 	= "'" . $_params->get('thumbs_vertical_position','center') . "'";
$slide_param['number_displayed_thumbs'] 	= $_params->get('number_displayed_thumbs','4');
$slide_param['thumbs_when_mouseover'] 		= $_params->get('thumbs_when_mouseover','false');
$slide_param['width_thumb'] 				= $_params->get('width_thumb','90');
$slide_param['height_thumb'] 				= $_params->get('height_thumb','50');
$slide_param['dotsPosition'] 				= "'" . $_params->get('dotsposition','center') . "'";
$slide_param['numbersPosition'] 			= "'" . $_params->get('numbersposition','center') . "'";
$slide_param['hideTools'] 					= $_params->get('hidetools','false');
$slide_param['opacity_elements'] 			= $_params->get('opacity_elements','0.75');
$slide_param['interval_in_elements'] 		= $_params->get('interval_in_elements','300');
$slide_param['interval_out_elements'] 		= $_params->get('interval_out_elements','500');
$slide_param['fullscreen'] = $_params->get('fullscreen','false');
if ( $_params->get('navigation','2') == '2' ) {
	$slide_param['dots'] = "true";
} else {
	$slide_param['dots'] = "false";
}
//$slide_param['width_label'] 				= $_params->get('width_label','null'); // Label width should be set by css
$slide_param['show_randomly'] 				= $_params->get('show_randomly','false');
$slide_param['onLoad'] 					= $_params->get('onLoad','null');
$slide_param['onImageSwitched'] 			= $_params->get('onImageSwitched','null');
$slide_param['onFinishAnimation'] 			= $_params->get('onFinishAnimation','null');
$slide_param['onSetLabel'] 				= $_params->get('onSetLabel','null');
$slide_param['numbers_align'] 				= "'" . $_params->get('numbers_align','left') . "'";
$slide_param['preview'] 					= $_params->get('preview','false');
$slide_param['preview_position'] 			= "'" . $_params->get('preview_position','bottom_right') . "'";
$slide_param['focus'] 						= $_params->get('focus','false');
$slide_param['focus_position'] 			= "'" . $_params->get('focus_position','center') . "'";
$slide_param['focus_fullscreen'] 			= $_params->get('focus_fullscreen','false');
$slide_param['controls'] 					= $_params->get('controls','false');
$slide_param['controls_position'] 			= "'" . $_params->get('controls_position','center') . "'";
$slide_param['auto_play'] 					= $_params->get('auto_play','false');
//Progressbar only works if autoplay or controls(play/pause button) on
if ( $_params->get('auto_play','false') == 'false' && $_params->get('controls','false') == 'false' ) {
	$_params->set('progressbar','false');
}
$slide_param['progressbar'] 				= $_params->get('progressbar','false');
$slide_param['stop_over'] 					= $_params->get('stop_over','true');
$slide_param['enable_navigation_keys'] 	= $_params->get('enable_navigation_keys','false');

$slide_param['video_autoplay'] 			= $_params->get('video_autoplay','false');

$slide_js_param = array();
foreach($slide_param AS $key=>$value) {
	$slide_js_param[] .= $key . ': ' . $value;
}

$slide_js_param_str = implode(', ', $slide_js_param);

$source = $_params->get('contentsource','content');
switch ($source) {
    case 'k2':
        $catid = $_params->get('k2catid', 0);
        break;
    case 'content':
    default:
        $catid = $_params->get('catid', 0);
        break;
}
if($source=='folder'){
	$_params->set('title', 0);
	$_params->set('linkedtitles', 0);
	$_params->set('readmore', 0);
}

if (!is_array($catid)) {
    $catid = (array) $catid;
}

$slide_javascript = "jQuery(document).ready(function($){
	$('.ju-slideshow-".$module->id."').juslideshow({".$slide_js_param_str."});
});";

$document->addScriptDeclaration($slide_javascript);

$listitems = $ModJUSlideshowHelper->getList();

require JModuleHelper::getLayoutPath($module->module, $layout.'/default');
?>
