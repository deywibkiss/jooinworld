<?php
/**
 * @version		$Id: mod_carousel_banner.php 2.2
 * @Joomla 2.5  by schro
 * @Official site http://www.templateplazza.com
 * @package		Joomla 2.5.x
 * @subpackage	mod_carousel_banner
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

require_once JPATH_ROOT . '/administrator/components/com_banners/helpers/banners.php';

$doc			= JFactory::getDocument();
$baseurl        = ''.JURI::base(true).'/';
$modulebase		= ''.JURI::base(true).'/modules/mod_carousel_banner/';
$modid          = $module->id;
$width          = (int) $params->get( 'width', 728 );
$height         = (int) $params->get( 'height', 90 );
$loadJquery     = (int) $params->get('loadJquery', 0);

$layout         = $params->get('layout', 0);

// Load All css
if($layout=='_:default') 
{
    $doc->addStylesheet($modulebase.'assets/css/jquery.jcarousel.css');
    $doc->addStylesheet($modulebase.'assets/css/skin.css');
        
    $cssinline = '
        .carouselbanner ul#mycarousel'.$module->id.' {list-style-type:none;padding:0; margin:0;}
        .jcarousel-skin-tango .jcarousel-container-horizontal{width:'.$width.'px;}
        .jcarousel-skin-tango .jcarousel-clip-horizontal{width:'.$width.'px;}
        ';

        $doc->addStyleDeclaration($cssinline, 'text/css');

} else {
    $doc->addStylesheet($modulebase.'assets/css/flexisel.css');
    $style = "
        #mycarousel-".$module->id." {display:none;}
        .nbs-flexisel-item img { max-width:$img_width; max-height:$img_height;}
    ";
    $doc->addStyleDeclaration($style);
}

// Then load all JS
JLoader::import( 'joomla.version' );
$version = new JVersion();
if (version_compare( $version->RELEASE, '2.5', '<=')) 
{
    if ($loadJquery) {
        if( $loadJquery == 1 ) {
            $doc->addScript($modulebase.'assets/js/jquery.min.js');
        }
        elseif( $loadJquery == 2 ) {
            $doc->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.8.4/jquery.min.js');
        }
    }
} else {
    JHtml::_('jquery.framework');
}


$doc->addScript($modulebase.'assets/js/jquery.noconflict.js');

if($layout=='_:default') 
{
    $doc->addScript($modulebase.'assets/js/jquery.jcarousel.min.js');

    $jsinline = '
        function mycarousel_initCallback(carousel)
        {
            // Disable autoscrolling if the user clicks the prev or next button.
            carousel.buttonNext.bind(\'click\', function() {
                carousel.startAuto(0);
            });

            carousel.buttonPrev.bind(\'click\', function() {
                carousel.startAuto(0);
            });

            // Pause autoscrolling if the user moves with the cursor over the clip.
            carousel.clip.hover(function() {
                carousel.stopAuto();
            }, function() {
                carousel.startAuto();
            });
        };



        jQuery.easing[\'BounceEaseOut\'] = function(p, t, b, c, d) {
            if ((t/=d) < (1/2.75)) {
                return c*(7.5625*t*t) + b;
            } else if (t < (2/2.75)) {
                return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
            } else if (t < (2.5/2.75)) {
                return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
            } else {
                return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
            }
        };

        jQuery.noConflict();
        jQuery(document).ready(function() {
            jQuery("#mycarousel'. $module->id.'").jcarousel({
                auto: 0,
                scroll:1,
                rtl:false,
                visible:1,
                wrap: \'last\',
                easing: \'BounceEaseOut\',
                animation: 1000,
                initCallback: mycarousel_initCallback
            });
        });
    ';

    $doc->addScriptDeclaration($jsinline);

} else { // flexisel

    // get module params
    $visible_items              = $params->get('visible_items', 4);
    $visible_items_portrait     = $params->get('visible_items_in_portrait', 2); 
    $visible_items_landscape    = $params->get('visible_items_in_landscape', 4);
    $visible_items_tablet       = $params->get('visible_items_in_tablet', 1);
    $anim_speed                 = $params->get('anim_speed',1000);
    $autoplay                   = $params->get('autoplay','true');
    $autoplay_speed             = $params->get('autoplay_speed',3000);
    $isPauseonhover             = $params->get('pause_on_hover', 'true');

    $doc->addScript($modulebase.'assets/js/jquery.flexisel.js');
    $jsinline = '
    jQuery(window).load(function() {
        jQuery("#mycarousel-'. $module->id.'").flexisel({

                visibleItems: '.$visible_items.', // $visible_items
                animationSpeed: '.$anim_speed.', // 1000
                autoPlay: '.$autoplay.', // false
                autoPlaySpeed:  '.$autoplay_speed.', // 3000
                pauseOnHover: '.$isPauseonhover.', // true
                enableResponsiveBreakpoints: true,
                responsiveBreakpoints: { 
                    portrait: { 
                        changePoint:480,
                        visibleItems: '.$visible_items_portrait.'
                    }, 
                    landscape: { 
                        changePoint:640,
                        visibleItems: '.$visible_items_landscape.'
                    },
                    tablet: { 
                        changePoint:768,
                        visibleItems: '.$visible_items_tablet.'
                    }
                }
            });
        });
    ';

    $doc->addScriptDeclaration($jsinline);    
}




BannersHelper::updateReset();
$list = modCarouselBannerHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_carousel_banner', $params->get('layout', 'default') );

