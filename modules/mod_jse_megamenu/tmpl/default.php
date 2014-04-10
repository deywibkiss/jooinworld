<?php
/**
 * @version		$Id$
 * @author		Joomseller
 * @package		Site
 * @subpackage	mod_jse_megamenu
 * @copyright	Copyright (C) 2008 - 2013 by Joomseller Solutions. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL version 3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="js-mainnav" class="clearfix <?php echo $menuStyle; ?>" <?php if(DEMO_MODE && $hozorver == 'vertical') echo 'style="width:250px;"'; ?>>
	<?php $dropdownmenu->genMenu (0, -1); ?>
</div>

<style type="text/css">
	<?php echo '#js-mainnav.'.$layout; ?> ul.level1 .childcontent { margin: -20px 0 0 <?php echo $params->get('mega-colwidth',200) - 30 ;?>px; }
</style>

<?php

if (!$params->get('css3_noJS', 0)) {
	//If rtl, not allow slide and fading effect
	$rtl		= $params->get('rtl');
	$animation	= $params->get('js_menu_mega_animation', 'slide');
	$hozorver	= $params->get('hozorver', 'horizontal');

	if($hozorver == 'horizontal') {
		$direction	= $params->get('horizontal_submenu_direction', 'down');
	} else {
		$direction	= $params->get('vertical_submenu_direction', 'lefttoright');
	}

	// PHP 5.3
	// Disable slide & fade effect for IE8 or above
	/*
	preg_match('/MSIE ([0-9]\.[0-9])/',$_SERVER['HTTP_USER_AGENT'],$reg);
	if(isset($reg[1])) {
		if ($reg[1] <= 8) {
			$animation = 'none';
		}
	}
	*/

	$duration = $params->get('js_menu_mega_duration', 300);
	$delayHide = $params->get('js_menu_mega_delayhide', 300);
	$fade = 0;
	$slide = 0;

	if (!$rtl) {
		if (preg_match ('/slide/', $animation)) $slide = 1;
		if (preg_match ('/fade/', $animation)) $fade = 1;
	}
	?>
	<script type="text/javascript">
		var megamenu = new jsMegaMenuMoo ('<?php echo $params->get('special_id') ?>', {
			'bgopacity': 0,
			'delayHide': <?php echo $delayHide ?>,
			'slide': <?php echo $slide ?>,
			'fading': <?php echo $fade ?>,
			'menutype':'<?php echo $hozorver;?>',
			'direction':'<?php echo $direction;?>',
			'action':'<?php echo $params->get('js_menu_mouse_action', 'mouseenter');?>',
			'tips': false,
			'duration': <?php echo $duration ?>,
			'hover_delay': <?php echo $params->get('js_menu_mouse_hover_delay', 0); ?>
		});
	</script>
	<?php
}