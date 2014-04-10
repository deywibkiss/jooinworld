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

$slideshow_class = array();
$slideshow_class[] = 'ju-slideshow-' . $module->id;
$slideshow_class[] = $_params->get('layout', 'default') . '-' . $_params->get('layout_theme', 'simple');
$slideshow_class[] = $_params->get('boxshadow', '');
$slideshow_class_str = implode(' ', $slideshow_class);
?>

<div class="ju-slideshow <?php echo $slideshow_class_str; ?>" style="width: <?php echo $_params->get('width_main', 800);?>px; height: <?php echo $_params->get('height_main', 300);?>px;">
	<ul class="ju-slideshow-data">
	<?php
		foreach ($listitems AS $item) {
			if ($item->mainimage) {
				if ( $_params->get('imagelinked', 0) ) {
					$link = $item->link;
				} else {
					$link = '#';
				}
				
				if(preg_match('/^https?:\/\/[^\/]+/i', $item->mainimage)) {
					$mainimage = $item->mainimage;
				} else {
					$mainimage = JURI::Base().$item->mainimage;
				}
				
				if(preg_match('/^https?:\/\/[^\/]+/i', $item->thumb)) {
					$thumb = $item->thumb;
				} else {
					$thumb = JURI::Base().$item->thumb;
				}

				//Class use to specification animation for each slideshow
				$class = $item->class == '' ? '' : 'class="'.$item->class.'" ';
				
				$animation = $item->animation;
				
				$label_text  = $item->title ? '<h3 class="label-title">'.$item->title.'</h3>' : '';
				$label_text .= $item->text ? '<div class="label-desc">'.$item->text.'</div>' : '';
				$label_text .= $item->readmore ? '<div class="label-readmore">'.$item->readmore.'</div>' : '';
				
				echo '<li class="ju-slideshow-item"><a href="'.$link.'" target="'.$_params->get('target', '_self').'"><img alt="'.$item->image_alt.'" src="'.$mainimage.'" data-animation="'.$animation.'" '.$class.'/></a><div class="label_text" style="opacity: 0.5">'.$label_text.'</div><div class="thumb_img">'.$thumb.'</div></li>';
			}
		}
	?>
	</ul>
</div>