<?php
/**
 * @version		$Id: mod_carousel_banner.php 2.0
 * @Joomla 1.7  by Rony S Y Zebua
 * @Official site http://www.templateplazza.com
 * @package		Joomla 1.7.x
 * @subpackage	mod_minifrontpage
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_banners/helpers/banner.php';

$count				= (int) $params->get('count', 2);
$image_width 		= (int) $params->get('width');
$image_height 		= (int) $params->get('height');
$thumb_width 		= (int) $params->get('thumb_width', 73 );
$thumb_height 		= (int) $params->get('thumb_height', 42 );
$thumb_option		= $params->get('thumb_option', 'exact');

$baseurl        = ''.JURI::base();
$modulebase		= ''.JURI::base().'modules/mod_carousel_banner/';

?>

	<ul id="mycarousel<?php echo '-'.$module->id; ?>">
		
		<?php 
		
		foreach($list as $item) 
		{ 
			$link = JRoute::_('index.php?option=com_banners&task=click&id='. $item->id);
			$imageurl = $item->params->get('imageurl');
			?>

			<li>

			<?php
			if (BannerHelper::isImage($imageurl)) 
			{
					// Image based banner
					$alt = $item->params->get('alt');
					$alt = $alt ? $alt : $item->name;
					$alt = $alt ? $alt : JText::_('MOD_SUPERCAROUSEL');
					?>
						
					<?php 
					if ($item->clickurl) 
					{ 
						// Wrap the banner in a link
						$target = $params->get('target', 1);
						if ($target == 1) { 
						?>	
							<!-- open in a new window -->
							<a href="<?php echo $link; ?>" target="_blank" title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>">
								
						<?php } elseif ($target == 2) { ?>
							
							<!--open in a popup window -->
							<a href="javascript:void window.open('<?php echo $link;?>', '',
									'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550');
									return false"
								title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>">
								
						<?php } else { ?>
							<!-- open in parent window -->
							<a href="<?php echo $link; ?>"
								title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>">
						
						<?php } ?>

					<?php } ?>	

						<img
							src="<?php echo $baseurl . $imageurl; ?>"
							alt="<?php echo $alt;?>" title="<?php if ($show_caption) { echo '<span>'.htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') .'</span><br /><i>'. $alt.'</i>'; } ?>" 
						/>	

					<?php if ($item->clickurl) { ?></a><?php } ?>						
			</li>
										
			<?php 
			} 
		
		} /*end foreach */ 
		?>
			
	</ul><!-- end wrapper -->