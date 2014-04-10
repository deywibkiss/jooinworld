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

defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}

require_once dirname(__FILE__).'/timthumb/timthumb.php';

jimport('joomla.html.parameter');
jimport('joomla.form.form');
	
class ModJUSlideshowHelper
{
	public $module;
	public $params;
	
	function __construct($module){
		$this->module = $module;
		$this->params = self::loadParams();
	}
	
	 /**
	 * Get values in file $profile.json + $params
	 * @return object
	 */
	public function loadParams() {
		$mainframe = JFactory::getApplication();
		$template = $mainframe->getTemplate();
		$mixed_params = new JRegistry();
		$db = JFactory::getDBO();
		
		//Get module params from database
		$query = "SELECT params FROM #__modules WHERE id=".$this->module->id;
		$db->setQuery($query);
		$db_params = $db->loadResult();
		
		//Original db_params
		$params = new JRegistry($db_params);
		
		if (trim($params->get('layoutsettings', ''))!='') {
			//Load params from layout params
			$mixed_params->loadString($params->get('layoutsettings', ''));
		}
		
		//Overwrite it by database params, by this way, DB params have higher priority, if DB params and layout params have the same param name, DB params will overwrite all
		$mixed_params->loadString($db_params);
		
		//Remove layout setttings
		$mixed_params->set('layoutsettings', '');
		
		return $mixed_params;
	}
	
	public function getList() {
		$source	= $this->params->def('source_selection', 0);
		switch ($source) {
			case 0:
			case 1:
				$list = self::ArticalgetList();
				break;
			case 2:
			case 3:
				$list = self::K2getList();
				break;
			case 4:
				$list = self::getImageFolder();
				break;
			case 5:
			case 6:
				$list = self::easyBloggetList();
				break;
		}
		return $list;
	}
	
	 /**
	 * Get list of article
	 * @return array 
	 */	
	public function ArticalGetList() {
		
		require_once JPATH_SITE.'/components/com_content/helpers/route.php';
		
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);


		// SOURCE
		$source			= $this->params->def('source_selection', 0);
		$source_cat		= $this->params->get('source_cat', array());
		$source_art		= trim(preg_replace(array('/\s*/', '/,+/'), array('', ','), $this->params->get('source_art')), ',');
		$exclude_art	= trim(preg_replace(array('/\s*/', '/,+/'), array('', ','), $this->params->get('exclude_art')), ',');

		if ($source == 0 && $source_cat) {
			if ($source_cat[0] != '') {
				$source_cat = (count($source_cat) == 1) ? '= '.$source_cat[0].'' : 'IN ('.implode(',', $source_cat).')';
				$query->where('a.catid '.$source_cat);
			}
			//Only exclude articles if source from article categories
			$exclude_art ? $query->where('a.id NOT IN ('.$exclude_art.')') : '';
		} else if ($source == 1 && $source_art) {
			$query->where('a.id IN ('.$source_art.')');
		} else {
			echo JText::_('MOD_JUSLIDESHOW_ERROR_SOURCE');
			return;
		}


		// FILTERS
		$maxitems			= (int) $this->params->get('maxitems', 15);
		//If use content source from list of article(1,2,4,5...), do NOT apply date_filtering, count_skip
		if ($source == 1) {
			$this->params->set('date_filtering', 'disabled');
			$this->params->set('count_skip', 0);
		}
		$count_skip			= (int) $this->params->get('count_skip', 0);
		
		$featured			= $this->params->def('show_featured', 'show');
		$ordering			= $this->params->def('ordering', 'created_dsc');
		$filter_image		= $this->params->def('filter_image', 0);

		$user				= JFactory::getUser();
		$userID				= (int) $user->get('id');
		$userLV				= implode(',', $user->getAuthorisedViewLevels());
		$access				= $this->params->def('not_public', 0) ? 0 : !JComponentHelper::getParams('com_content')->get('show_noauth');


		// TEXT
		$show_title			= $this->params->get('show_title', 1);
		
		$title_link			= $this->params->get('title_link', 1);

		$limit_title		= $this->params->get('limit_title', 30);
		
		$show_text			= $this->params->def('show_text', 1);
		
		$limit_text			= $this->params->get('limit_text', 120);

		$show_readmore		= $this->params->get('show_readmore', 0);
		$read_more			= $this->params->get('read_more', 'Read more');
		
		$target				= $this->params->get('target', '_self');

		$intro_clean		= $this->params->get('intro_clean', 1);
		$allowable_tags		= str_replace(' ', '', $this->params->get('allowable_tags'));
		$allowable_tags		= "<".str_replace(',', '><', $allowable_tags).">";
		$video_support		= $this->params->def('video_support', 1);
		$plugins_support	= $this->params->def('plugins_support', 0);

		// INFO
		$date_type			= $this->params->def('date_type', 'created');
		$comment_system		= $this->params->def('comment_system', 'jcomments');
		
		// IMAGES
		$image_width		= $this->params->get('image_width', 160);
		$image_height		= $this->params->get('image_height', 120);
		$image_source		= $this->params->get('image_source', 'automatic');
		$image_link			= $this->params->get('image_link', 1);

		// ORDERING
		switch ($ordering) {
			case 'created_asc':
				$orderBy = 'date ASC';
			break;
			case 'title_az':
				$orderBy = 'a.title ASC';
			break;
			case 'title_za':
				$orderBy = 'a.title DESC';
			break;
			case 'popular_first':
				$orderBy = 'a.hits DESC';
			break;
			case 'popular_last':
				$orderBy = 'a.hits ASC';
			break;
			case 'rated_most':
				$orderBy = 'rating_value DESC, r.rating_count DESC';
			break;
			case 'rated_least':
				$orderBy = 'rating_value ASC, r.rating_count ASC';
			break;
			case 'commented_most':
				$orderBy = 'comments_count DESC, comments_date DESC';
			break;
			case 'commented_latest':
				$orderBy = 'comments_date DESC, date DESC';
			break;
			case 'ordering_fwd':
				$orderBy = $featured == 'only' ? 'f.ordering ASC' : 'a.ordering ASC';
			break;
			case 'ordering_rev':
				$orderBy = $featured == 'only' ? 'f.ordering DESC' : 'a.ordering DESC';
			break;
			case 'id_asc':
				$orderBy = 'a.id ASC';
			break;
			case 'id_dsc':
				$orderBy = 'a.id DESC';
			break;
			case 'exact':
				$orderBy = ($source == 1 && $source_art) ? 'FIELD(a.id, '.$source_art.')' : 'a.id ASC';
			break;
			case 'random':
				$orderBy = 'RAND()';
			break;
			case 'created_dsc':
			default:
				$orderBy = 'date DESC';
			break;
		}


		// QUERY
		$query->select('a.id, a.title, a.alias, a.catid AS category_id, a.access');

		// Select: Date
		if ($date_type !== 'created') {
			$query->select('CASE WHEN a.'.$date_type.' = \'0000-00-00 00:00:00\' THEN a.created ELSE a.'.$date_type.' END AS date');
		} else {
			$query->select(' a.created AS date');
		}

		if ($show_text || $image_source == 'text' || $image_source == 'automatic') {
			$query->select(' a.introtext, a.fulltext');
		}
		
		$image_source != 'text' ? $query->select(' a.images') : '';
		
		$query->from('#__content AS a');

		// Join: Frontpage Ordering
		if ($featured == 'only' && ($ordering == 'ordering_fwd' || $ordering == 'ordering_rev')) {
			$query->join('LEFT', '#__content_frontpage AS f ON f.content_id = a.id');
		}

		// Join: Categories
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
		
		// Join: Rating
		if ($ordering == 'rated_most' || $ordering == 'rated_least') {
			$query->select(' ROUND(r.rating_sum / r.rating_count, 2) AS rating_value, r.rating_count');
			$query->join('LEFT', '#__content_rating AS r ON r.content_id = a.id');
		}
		
		// Join: Comments
		if ($show_comments || $ordering == 'commented_most' || $ordering == 'commented_latest') {
			switch ($comment_system) {
				case 'jacomment':
					$checktbl_query = "SHOW TABLES LIKE '%_jacomment_items'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__jacomment_items AS jc ON jc.contentid = a.id AND jc.option = \'com_content\' AND jc.type = 1');
						$comments_link	= "#jac-wrapper";
					}
					break;
				case 'rscomments':
					$checktbl_query = "SHOW TABLES LIKE '%_rscomments_comments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.IdComment) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__rscomments_comments AS jc ON jc.id = a.id AND jc.published = 1');
						$comments_link	= "#rscomments_big_container";
					}
					break;
				case 'komento':
					$checktbl_query = "SHOW TABLES LIKE '%_komento_comments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.created) AS comments_date');
						$query->join('LEFT', '#__komento_comments AS jc ON jc.cid = a.id AND jc.published = 1');
						$comments_link  = "#section-kmt";
					}
					break;
				case 'compojoom':
					$checktbl_query = "SHOW TABLES LIKE '%_comment'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__comment AS jc ON jc.contentid = a.id AND jc.published = 1');
						$comments_link	= "#JOSC_TOP";
					}
					break;
				case 'slicomments':
					$checktbl_query = "SHOW TABLES LIKE '%_slicomments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.created) AS comments_date');
						$query->join('LEFT', '#__slicomments AS jc ON jc.article_id = a.id AND jc.status = 1');
						$comments_link	= "#comments";
					}
					break;
				case 'jcomments':
				default:
					$checktbl_query = "SHOW TABLES LIKE '%_jcomments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__jcomments AS jc ON jc.object_id = a.id AND jc.object_group = \'com_content\' AND jc.published = 1');
						$comments_link	= "#comments";
					}
					break;
			}
			if($jc_tbl) {
				$query->group('a.id, a.created');
			} else {
				//Commnent table not found
				$show_comments = false;
				if($ordering == 'commented_most' || $ordering == 'commented_latest') {
					$ordering = 'created_dsc';
					$orderBy = 'date DESC';
				}
			}
		}

		// Filter: Published
		$query->where('c.published = 1 AND a.state = 1');

		// Filter: Access
		$access ? $query->where('c.access IN ('.$userLV.') AND a.access IN ('.$userLV.')') : '';

		// Filter: Featured
		$featured == 'hide' ? $query->where('a.featured = 0') : ($featured == 'only' ? $query->where('a.featured = 1') : '');

		// Filter: Language
		$app->getLanguageFilter() ? $query->where('a.language IN ('.$db->quote(JFactory::getLanguage()->getTag()).',\'*\')') : '';

		// Filter: Date
		$date	= JFactory::getDate();
		$now	= $db->quote($date->format('Y-m-d H:i:s'));

		$query->where('a.publish_up <= '.$now);
		$query->where('(a.publish_down = \'0000-00-00 00:00:00\' OR a.publish_down >= '.$now.')');
		
		// Filter: Date Range
		switch ($this->params->def('date_filtering', 'disabled'))
		{
			case 'today':
				$extraquery = ($date_type !== 'created') ? ' OR (a.'.$date_type.' = \'0000-00-00 00:00:00\' AND DATE(a.created) = '.$db->quote($date->format('Y-m-d')).')' : '';
				$query->where('(DATE(a.'.$date_type.') = '.$db->quote($date->format('Y-m-d')).$extraquery.')');
				break;

			case 'this_week':
				$extraquery = ($date_type !== 'created') ? ' OR (a.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEARWEEK(a.created) = YEARWEEK('.$now.'))' : '';
				$query->where('(YEARWEEK(a.'.$date_type.') = YEARWEEK('.$now.')'.$extraquery.')');
				break;

			case 'this_month':
				$extraquery = ($date_type !== 'created') ? ' OR (a.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEAR(a.created) = YEAR('.$now.') AND MONTH(a.created) = MONTH('.$now.'))' : '';
				$query->where('((YEAR(a.'.$date_type.') = YEAR('.$now.') AND MONTH(a.'.$date_type.') = MONTH('.$now.'))'.$extraquery.')');
				break;

			case 'this_year':
				$extraquery = ($date_type !== 'created') ? ' OR (a.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEAR(a.created) = '.$db->quote($date->format('Y')).')' : '';
				$query->where('(YEAR(a.'.$date_type.') = '.$db->quote($date->format('Y')).$extraquery.')');
				// $query->where('(YEAR(a.'.$date_type.') = YEAR('.$now.')'.$extraquery.')');
				break;

			case 'range':
				$date_range_start	= $db->quote($this->params->get('date_range_start', '1000-01-01 00:00:00'));
				$date_range_end		= $db->quote($this->params->get('date_range_end', '9999-12-31 23:59:59'));

				$extraquery = ($date_type !== 'created') ? ' OR (a.'.$date_type.' = \'0000-00-00 00:00:00\' AND a.created BETWEEN '.$date_range_start.' AND '.$date_range_end.')' : '';
				$query->where('(a.'.$date_type.' BETWEEN '.$date_range_start.' AND '.$date_range_end.$extraquery.')');
				break;

			case 'relative':
				$date_range_from	= $this->params->get('date_range_from');
				$date_range_from	= $date_range_from[0] >= '0' ? $db->quote(date('Y-m-d', strtotime($date.' - '.$date_range_from[0].' day'))) : $db->quote('1000-01-01 00:00:00');
				$date_range_to		= $this->params->get('date_range_to');
				$date_range_to		= $date_range_to[0] >= '0' ? $db->quote(date('Y-m-d', strtotime($date.' - '.$date_range_to[0].' day'))) : $db->quote('9999-12-31 23:59:59');

				$extraquery = ($date_type !== 'created') ? ' OR (a.'.$date_type.' = \'0000-00-00 00:00:00\' AND a.created BETWEEN '.$date_range_from.' AND '.$date_range_to.')' : '';
				$query->where('(a.'.$date_type.' BETWEEN '.$date_range_from.' AND '.$date_range_to.$extraquery.')');
				break;

			case 'disabled':
			default:
				break;
		}
		
		// Filter: Author
		switch ($this->params->get('authors')) {
			case 'by_me':
				if ($userID) {
					$query->where('(a.created_by = '.$userID.' OR a.modified_by = '.$userID.')');
				} else {
					return;
				}
				break;
			case 'not_me':
				if ($userID) {
					$query->where('(a.created_by <> '.$userID.' AND a.modified_by <> '.$userID.')');
				}
				break;
			case 'all':
			default:
				break;
		}
		
		// Filter: Only items has image
		if($filter_image) {
			if ($image_source == 'text' || $image_source == 'automatic') $image_filter_where[] = 'a.introtext REGEXP "<img[^>]+>" OR a.fulltext REGEXP "<img[^>]+>"';
			if ($image_source == 'intro' || $image_source == 'full' || $image_source == 'automatic') $image_filter_where[] = 'a.images REGEXP "\.(jpe?g|gif|png)"';
			$query->where('('.implode(' OR ', $image_filter_where).')');
		}
		
		$query->order($orderBy);
		$db->setQuery($query, $count_skip, $maxitems);

		// Retrieve Content
		$items = $db->loadObjectList();

		$lists = array();
		
		$slideshowclass 	= self::parseClass();
		$slideshowanimation = self::parseAnimation();
		
		foreach ($items as $i => &$item) {
			$lists[$i] = new stdClass;
			$lists[$i]->id 			= '';
			$lists[$i]->title 		= '';
			$lists[$i]->link 		= '';
			$lists[$i]->date 		= '';
			$lists[$i]->image 		= $lists[$i]->image_src = $lists[$i]->image_alt = '';
			$lists[$i]->text 		= '';
			$lists[$i]->author 		= '';
			$lists[$i]->rating 		= $lists[$i]->rating_value = $lists[$i]->rating_count = '';
			$lists[$i]->hits 		= '';
			$lists[$i]->comments	= $lists[$i]->comments_count = $lists[$i]->comments_link = '';
			$lists[$i]->readmore 	= '';
			
			$lists[$i]->class 		= $slideshowclass[$i];
			$lists[$i]->animation 	= $slideshowanimation[$i];

			$lists[$i]->id	= $item->id;
			$lists[$i]->category_id	= $item->category_id ? $item->category_id : '';

			// TOP Items & Regular Items

			// Item Link
			if ($access || strpos($userLV, $item->access) !== false) {
				$lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->id.':'.$item->alias, $item->category_id));
			} else {
				$link	= 'index.php?option=com_users&view=login';
				$menu	= $app->getMenu()->getItems('link', $link);
				$lists[$i]->link = isset($menu[0]) ? JRoute::_($link.'&Itemid='.$menu[0]->id) : JRoute::_($link);
			}

			// Show Title
			if ($show_title) {
				$lists[$i]->title = $limit_title ? self::truncateHTML($item->title, $limit_title, '&hellip;', false, false) : $item->title;
				$lists[$i]->title = ($title_link) ? '<a target="'.$target.'" href="'. $lists[$i]->link .'">'.$lists[$i]->title.'</a>' : $lists[$i]->title;
			}
			
			//Parse video
			$item->introtext = $video_support ? self::parseVideo($item->introtext) : $item->introtext;
			
			// Plugins Support
			$item->introtext = $plugins_support ? JHtml::_('content.prepare', $item->introtext) : preg_replace('/{[^{]+?{\/.+?}|{.+?}/', '', $item->introtext);
			
			// Images
			$img		= array();
			$img_source	= '';
			$images		= @$item->images ? json_decode($item->images) : '';

			if ($image_source == 'automatic') {
				if (@$images->image_intro) {
					$img_source = 'intro';
				} elseif (@$images->image_fulltext) {
					$img_source = 'full';
				} else {
					$img_source = 'text';
				}
			} else {
				$img_source = $image_source;
			}

			switch ($img_source) {
				case 'intro':
					if (@$images->image_intro) {
						$img['src'] = $images->image_intro;
						$img['alt'] = @$images->image_intro_alt;
						$img['ttl'] = @$images->image_intro_caption;
					}
				break;
				case 'full':
					if (@$images->image_fulltext) {
						$img['src'] = $images->image_fulltext;
						$img['alt'] = @$images->image_fulltext_alt;
						$img['ttl'] = @$images->image_fulltext_caption;
					}
				break;
				default:
					$pattern	= '/<img[^>]+>/i';
					preg_match($pattern, $item->introtext, $img_tag);
					if (!count($img_tag)) {
						preg_match($pattern, $item->fulltext, $img_tag);
					}
					if (count($img_tag)) {
						preg_match_all('/(alt|title|src)\s*=\s*(["\'])(.*?)\2/i', $img_tag[0], $img_atr);
						$img_atr = array_combine($img_atr[1], $img_atr[3]);
						if (@$img_atr['src']) {
							$img['src'] = trim($img_atr['src']);
							$img['alt'] = trim(@$img_atr['alt']);
							$img['ttl'] = trim(@$img_atr['title']);
							$item->introtext = preg_replace($pattern, '', $item->introtext, 1);
						}
					}
				break;
			}

			if ($img['src']) {
				// Create Thumbnail
				$lists[$i]->mainimage 	= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_main'), $this->params->get('height_main'));
				$lists[$i]->thumb 		= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_thumb'), $this->params->get('height_thumb'));
				$lists[$i]->image_src	= @$img['src'];
				$lists[$i]->image_alt	= @$img['alt'];
			}

			// Show Text
			if ($show_text) {
				// Clean XHTML
				if ($intro_clean) {
					$item->introtext = strip_tags($item->introtext, $allowable_tags);
					$item->introtext = str_replace('&nbsp;', ' ', $item->introtext);
					$item->introtext = preg_replace('/\s{2,}/u', ' ', trim($item->introtext));
				}
				// Limit Text
				$lists[$i]->text = $limit_text ? self::truncateHtml($item->introtext, $limit_text, '&hellip;', false, true) : $item->introtext;
			}
			
			// Show Readmore
			$lists[$i]->readmore = $show_readmore ? '<a class="readmore" target="'.$target.'" href="'.$lists[$i]->link.'"><span>'.$read_more.'</span></a>' : '';

		}
		return $lists;
	}
	
	 /**
	 * Get list of k2 item
	 * @return array 
	 */
	public function K2getList() {
	
		$k2_file = JPATH_SITE.'/components/com_k2/helpers/route.php';
		file_exists($k2_file) ? require_once $k2_file : '' ;

		$k2_file = 	JPATH_SITE.'/components/com_k2/helpers/utilities.php';
		file_exists($k2_file) ? require_once $k2_file : '' ;
		
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);


		// SOURCE
		$source				= $this->params->def('source_selection', 2);
		$source_cat			= $this->params->get('source_k2cat', array());
		$source_itm			= trim(preg_replace(array('/\s*/', '/,+/'), array('', ','), $this->params->get('source_itm')), ',');
		$exclude_itm		= trim(preg_replace(array('/\s*/', '/,+/'), array('', ','), $this->params->get('exclude_itm')), ',');
	
		if ($source == 2 && $source_cat) {
			if ($source_cat[0] != '') {
				$source_cat = (count($source_cat) == 1) ? '= '.$source_cat[0].'' : 'IN ('.implode(',', $source_cat).')';
				$query->where('i.catid '.$source_cat);
			}
			//Only exclude items if source from K2 categories
			$exclude_itm ? $query->where('i.id NOT IN ('.$exclude_itm.')') : '';
		} else if ($source == 3 && $source_itm) {
			$query->where('i.id IN ('.$source_itm.')');
		} else {
			echo JText::_('MOD_JUSLIDESHOW_ERROR_SOURCE');
			return;
		}

		
		// FILTERS
		$maxitems			= (int) $this->params->get('maxitems', 15);
		//If use content source from list of item(1,2,4,5...), do NOT apply date_filtering, count_skip, tag
		if ($source == 3) {
			$this->params->set('date_filtering', 'disabled');
			$this->params->set('count_skip', 0);
			$this->params->set('tags_k2', array('0'=>''));
		}
		$count_skip			= (int) $this->params->get('count_skip', 0);
		$tags				= $this->params->get('tags_k2', array('0'=>''));
		$tags				= ($tags[0] != '') ? 'IN ('.implode(',', $tags).')' : NULL;

		$featured			= $this->params->def('show_featured', 'show');
		$ordering			= $this->params->def('ordering', 'created_dsc');
		$filter_image		= $this->params->def('filter_image', 0);

		$user				= JFactory::getUser();
		$userID				= (int) $user->get('id');
		$userLV				= implode(',', $user->getAuthorisedViewLevels());
		$access				= $this->params->def('not_public', 0) ? 0 : 1;


		// TEXT
		$show_title			= $this->params->get('show_title', 1);
		
		$title_link			= $this->params->get('title_link', 1);

		$limit_title		= $this->params->get('limit_title', 30);
		
		$show_text			= $this->params->def('show_text', 1);
		
		$limit_text			= $this->params->get('limit_text', 120);

		$show_readmore		= $this->params->get('show_readmore', 0);
		$read_more			= $this->params->get('read_more', 'Read more');
		
		$target				= $this->params->get('target', '_self');

		$intro_clean		= $this->params->get('intro_clean', 1);
		$allowable_tags		= str_replace(' ', '', $this->params->get('allowable_tags'));
		$allowable_tags		= "<".str_replace(',', '><', $allowable_tags).">";
		$video_support		= $this->params->def('video_support', 1);
		$plugins_support	= $this->params->def('plugins_support', 0);

		// INFO
		$date_type			= $this->params->def('date_type', 'created');

		// IMAGES
		$image_width		= $this->params->get('image_width', 246);
		$image_height		= $this->params->get('image_height', 150);
		$image_source		= $this->params->get('image_source_k2', 'automatic');
		$image_link			= $this->params->get('image_link', 1);

		// ORDERING
		switch ($ordering) {
			case 'created_asc':
				$orderBy = 'date ASC';
			break;
			case 'title_az':
				$orderBy = 'i.title ASC';
			break;
			case 'title_za':
				$orderBy = 'i.title DESC';
			break;
			case 'popular_first':
				$orderBy = 'i.hits DESC';
			break;
			case 'popular_last':
				$orderBy = 'i.hits ASC';
			break;
			case 'rated_most':
				$orderBy = 'rating_value DESC, r.rating_count DESC';
			break;
			case 'rated_least':
				$orderBy = 'rating_value ASC, r.rating_count ASC';
			break;
			case 'commented_most':
				$orderBy = 'comments_count DESC, comments_date DESC';
			break;
			case 'commented_latest':
				$orderBy = 'comments_date DESC, date DESC';
			break;
			case 'ordering_fwd':
				$orderBy = $featured == 'only' ? 'i.featured_ordering ASC' : 'i.ordering ASC';
			break;
			case 'ordering_rev':
				$orderBy = $featured == 'only' ? 'i.featured_ordering DESC' : 'i.ordering DESC';
			break;
			case 'id_asc':
				$orderBy = 'i.id ASC';
			break;
			case 'id_dsc':
				$orderBy = 'i.id DESC';
			break;
			case 'exact':
				$orderBy = ($source == 3 && $source_itm) ? 'FIELD(i.id, '.$source_itm.')' : 'i.id ASC';
			break;
			case 'random':
				$orderBy = 'RAND()';
			break;
			case 'created_dsc':
			default:
				$orderBy = 'date DESC';
			break;
		}


		// QUERY
		$query->select('i.id, i.title, i.alias, i.catid AS category_id, c.alias AS category_alias, i.access');
		
		// Select: Date
		if ($date_type !== 'created') {
			$query->select('CASE WHEN i.'.$date_type.' = \'0000-00-00 00:00:00\' THEN i.created ELSE i.'.$date_type.' END AS date');
		} else {
			$query->select(' i.created AS date');
		}
		
		if ($show_text || $image_source == 'text' || $image_source == 'automatic') {
			$query->select(' i.introtext, i.fulltext');
		}
		$image_source != 'text' ? $query->select(' i.image_caption') : '';

		$query->from('#__k2_items AS i');

		// Join: Categories
		$query->join('LEFT', '#__k2_categories AS c ON c.id = i.catid');
		
		// Join: Rating
		if ($ordering == 'rated_most' || $ordering == 'rated_least') {
			$query->select(' ROUND(r.rating_sum / r.rating_count, 2) AS rating_value, r.rating_count');
			$query->join('LEFT', '#__k2_rating AS r ON r.itemID = i.id');
		}
		
		// Join: Tags
		$tags ? $query->join('INNER', '#__k2_tags_xref AS t ON t.itemID = i.id') : '';
		
		// Join: Comments
		if ($ordering == 'commented_most' || $ordering == 'commented_latest') {
			$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.commentDate) AS comments_date');
			$query->join('LEFT', '#__k2_comments AS jc ON jc.itemID = i.id AND jc.published = 1');
			$comments_link	= "#itemCommentsAnchor";
			// $query->group('i.id, i.created');
		}
		
		// Filter: Published & Trashed
		$query->where('c.published = 1 AND c.trash = 0 AND i.published = 1 AND i.trash = 0');

		// Filter: Access
		$access ? $query->where('c.access IN ('.$userLV.') AND i.access IN ('.$userLV.')') : '';

		// Filter: Tags
		$tags ? $query->where('t.tagID '.$tags) : '';
		
		// Filter: Featured
		$featured == 'hide' ? $query->where('i.featured = 0') : ($featured == 'only' ? $query->where('i.featured = 1') : '');

		// Filter: Language
		if ($app->getLanguageFilter()) {
			$languageTag = $db->quote(JFactory::getLanguage()->getTag());
			$query->where('c.language IN ('.$languageTag.',\'*\') AND i.language IN ('.$languageTag.',\'*\')');
		}

		// Filter: Date
		$date	= JFactory::getDate();
		$now	= $db->quote($date->format('Y-m-d H:i:s'));

		$query->where('i.publish_up <= '.$now);
		$query->where('(i.publish_down = \'0000-00-00 00:00:00\' OR i.publish_down >= '.$now.')');
		
		// Filter: Date Range
		switch ($this->params->def('date_filtering', 'disabled'))
		{
			case 'today':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND DATE(i.created) = '.$db->quote($date->format('Y-m-d')).')' : '';
				$query->where('(DATE(i.'.$date_type.') = '.$db->quote($date->format('Y-m-d')).$extraquery.')');
				break;

			case 'this_week':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEARWEEK(i.created) = YEARWEEK('.$now.'))' : '';
				$query->where('(YEARWEEK(i.'.$date_type.') = YEARWEEK('.$now.')'.$extraquery.')');
				break;

			case 'this_month':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEAR(i.created) = YEAR('.$now.') AND MONTH(i.created) = MONTH('.$now.'))' : '';
				$query->where('((YEAR(i.'.$date_type.') = YEAR('.$now.') AND MONTH(i.'.$date_type.') = MONTH('.$now.'))'.$extraquery.')');
				break;

			case 'this_year':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEAR(i.created) = '.$db->quote($date->format('Y')).')' : '';
				$query->where('(YEAR(i.'.$date_type.') = '.$db->quote($date->format('Y')).$extraquery.')');
				// $query->where('(YEAR(i.'.$date_type.') = YEAR('.$now.')'.$extraquery.')');
				break;

			case 'range':
				$date_range_start	= $db->quote($this->params->get('date_range_start', '1000-01-01 00:00:00'));
				$date_range_end		= $db->quote($this->params->get('date_range_end', '9999-12-31 23:59:59'));

				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND i.created BETWEEN '.$date_range_start.' AND '.$date_range_end.')' : '';
				$query->where('(i.'.$date_type.' BETWEEN '.$date_range_start.' AND '.$date_range_end.$extraquery.')');
				break;

			case 'relative':
				$date_range_from	= $this->params->get('date_range_from');
				$date_range_from	= $date_range_from[0] >= '0' ? $db->quote(date('Y-m-d', strtotime($date.' - '.$date_range_from[0].' day'))) : $db->quote('1000-01-01 00:00:00');
				$date_range_to		= $this->params->get('date_range_to');
				$date_range_to		= $date_range_to[0] >= '0' ? $db->quote(date('Y-m-d', strtotime($date.' - '.$date_range_to[0].' day'))) : $db->quote('9999-12-31 23:59:59');

				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND i.created BETWEEN '.$date_range_from.' AND '.$date_range_to.')' : '';
				$query->where('(i.'.$date_type.' BETWEEN '.$date_range_from.' AND '.$date_range_to.$extraquery.')');
				break;

			case 'disabled':
			default:
				break;
		}
		
		// Filter: Author
		switch ($this->params->get('authors')) {
			case 'by_me':
				if ($userID) {
					$query->where('(i.created_by = '.$userID.' OR i.modified_by = '.$userID.')');
				} else {
					return;
				}
				break;
			case 'not_me':
				if ($userID) {
					$query->where('(i.created_by <> '.$userID.' AND i.modified_by <> '.$userID.')');
				}
				break;
			case 'all':
			default:
				break;
		}
		
		// Filter: Only items has image (Only filter by SQL if image_source is text)
		if($filter_image) {
			if ($image_source == 'text') {
				$image_filter_where = 'a.introtext REGEXP "<img[^>]+>" OR a.fulltext REGEXP "<img[^>]+>"';
				$query->where('('.$image_filter_where.')');
			}
		}
		
		$tags || @$comments_link ? $query->group('i.id, i.created') : '';

		$query->order($orderBy);
		$db->setQuery($query, $count_skip, $maxitems);
		// Retrieve Content
		$items = $db->loadObjectList();
		$lists = array();
		$slideshowclass 	= self::parseClass();
		$slideshowanimation = self::parseAnimation();
		
		foreach ($items as $i => &$item) {
			$lists[$i] = new stdClass;
			$lists[$i]->id			= '';
			$lists[$i]->title		= '';
			$lists[$i]->link		= '';
			$lists[$i]->date		= '';
			$lists[$i]->image		= $lists[$i]->image_src = $lists[$i]->image_alt = '';
			$lists[$i]->text		= '';
			$lists[$i]->author		= $lists[$i]->author_name = $lists[$i]->author_link = '';
			$lists[$i]->rating		= $lists[$i]->rating_value = $lists[$i]->rating_count = '';
			$lists[$i]->hits		= '';
			$lists[$i]->comments	= $lists[$i]->comments_count = $lists[$i]->comments_link = '';
			$lists[$i]->readmore	= '';
			
			$lists[$i]->class		= $slideshowclass[$i];
			$lists[$i]->animation	= $slideshowanimation[$i];

			$lists[$i]->id			= $item->id;
			$lists[$i]->category_id	= $item->category_id ? $item->category_id : '';


			// Item Link
			if ($access || strpos($userLV, $item->access) !== false) {
				$lists[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->category_id.':'.urlencode($item->category_alias))));
			} else {
				$link	= 'index.php?option=com_users&view=login';
				$menu	= $app->getMenu()->getItems('link', $link);
				$lists[$i]->link = isset($menu[0]) ? JRoute::_($link.'&Itemid='.$menu[0]->id) : JRoute::_($link);
			}

			// Show Title
			if ($show_title) {
				$lists[$i]->title = $limit_title ? self::truncateHTML($item->title, $limit_title, '&hellip;', false, false) : $item->title;
				$lists[$i]->title = ($title_link) ? '<a target="'.$target.'" href="'. $lists[$i]->link .'">'.$lists[$i]->title.'</a>' : $lists[$i]->title;
			}
			
			//Parse video
			$item->introtext = $video_support ? self::parseVideo($item->introtext) : $item->introtext;
			
			// Plugins Support
			$item->introtext = $plugins_support ? JHtml::_('content.prepare', $item->introtext) : preg_replace('/{[^{]+?{\/.+?}|{.+?}/', '', $item->introtext);
			
			// Images
			$img		= array();
			$img_source	= '';
			$image		= md5("Image".$item->id);
			if (JFile::exists(JPATH_SITE.'/media/k2/items/src/'.$image.'.jpg')) {
				$image	= 'media/k2/items/src/'.$image.'.jpg';
			} elseif (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.$image.'_L.jpg')) {
				$image	= 'media/k2/items/cache/'.$image.'_L.jpg';
			} else {
				$image	= '';
			}

			if ($image_source == 'automatic') {
				if (@$image) {
					$img_source = 'image';
				} else {
					$img_source = 'text';
				}
			} else {
				$img_source = $image_source;
			}

			switch ($img_source) {
				case 'image':
					if (@$image) {
						$img['src'] = $image;
						$img['alt'] = @$item->image_caption;
						$img['ttl'] = $item->title;
					}
				break;
				default:
					$pattern = '/<img[^>]+>/i';
					preg_match($pattern, $item->introtext, $img_tag);
					if (!count($img_tag)) {
						preg_match($pattern, $item->fulltext, $img_tag);
					}
					if (count($img_tag)) {
						preg_match_all('/(alt|title|src)\s*=\s*(["\'])(.*?)\2/i', $img_tag[0], $img_atr);
						$img_atr = array_combine($img_atr[1], $img_atr[3]);
						if (@$img_atr['src']) {
							$img['src'] = trim($img_atr['src']);
							$img['alt'] = trim(@$img_atr['alt']);
							$img['ttl'] = trim(@$img_atr['title']);
							$item->introtext = preg_replace($pattern, '', $item->introtext, 1);
						}
					}
				break;
			}
			
			//If filter only items have image, and current item has not an image => skip it
			if (!@$img['src'] && $filter_image) {
				unset($lists[$i]);
				continue;
			}
			
			if ($img['src']) {
				// Create Thumbnail
				$lists[$i]->mainimage 	= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_main'), $this->params->get('height_main'));
				$lists[$i]->thumb 		= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_thumb'), $this->params->get('height_thumb'));
				$lists[$i]->image_src	= @$img['src'];
				$lists[$i]->image_alt	= @$img['alt'];
			}

			// Show Text
			if ($show_text) {
				// Clean XHTML
				if ($intro_clean) {
					$item->introtext = strip_tags($item->introtext, $allowable_tags);
					$item->introtext = str_replace('&nbsp;', ' ', $item->introtext);
					$item->introtext = preg_replace('/\s{2,}/u', ' ', trim($item->introtext));
				}
				// Limit Text
				$lists[$i]->text = $limit_text ? self::truncateHtml($item->introtext, $limit_text, '&hellip;', false, true) : $item->introtext;
			}
			
			
			// Show Readmore
			$lists[$i]->readmore = $show_readmore ? '<a class="readmore" target="'.$target.'" href="'.$lists[$i]->link.'">'.$read_more.'</a>' : '';

		}

		return $lists;
	}
	
	 /**
	 * Get list of easyBlog item
	 * @return array 
	 */
	public function easyBloggetList() {
		$easyblog_file = JPATH_SITE.'/components/com_easyblog/helpers/router.php';
		file_exists($easyblog_file) ? require_once $easyblog_file : '' ;
		
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);


		// SOURCE
		$source				= $this->params->def('source_selection', 5);
		$source_cat			= $this->params->get('source_easyblogcat', array());
		$source_itm			= trim(preg_replace(array('/\s*/', '/,+/'), array('', ','), $this->params->get('source_easyblogent')), ',');
		$exclude_itm		= trim(preg_replace(array('/\s*/', '/,+/'), array('', ','), $this->params->get('exclude_easyblogent')), ',');
	
		if ($source == 5 && $source_cat) {
			if ($source_cat[0] != '') {
				$source_cat = (count($source_cat) == 1) ? '= '.$source_cat[0].'' : 'IN ('.implode(',', $source_cat).')';
				$query->where('i.category_id '.$source_cat);
			}
			//Only exclude items if source from easyBlog categories
			$exclude_itm ? $query->where('i.id NOT IN ('.$exclude_itm.')') : '';
		} else if ($source == 6 && $source_itm) {
			$query->where('i.id IN ('.$source_itm.')');
		} else {
			echo JText::_('MOD_JUSLIDESHOW_ERROR_SOURCE');
			return;
		}

		
		// FILTERS
		$maxitems			= (int) $this->params->get('maxitems', 15);
		//If use content source from list of entry(1,2,4,5...), do NOT apply date_filtering, count_skip, tag
		if ($source == 6) {
			$this->params->set('date_filtering', 'disabled');
			$this->params->set('count_skip', 0);
			$this->params->set('tags_k2', array('0'=>''));
		}
		$count_skip			= (int) $this->params->get('count_skip', 0);
		$tags				= $this->params->get('tags_easyblog', array('0'=>''));
		$tags				= ($tags[0] != '') ? 'IN ('.implode(',', $tags).')' : NULL;

		$featured			= $this->params->def('show_featured', 'show');
		$ordering			= $this->params->def('ordering', 'created_dsc');
		$filter_image		= $this->params->def('filter_image', 0);

		$user				= JFactory::getUser();
		$userID				= (int) $user->get('id');
		$private			= $this->params->def('private_easyblog', 0); //1: Show private | 0: Hide private


		// TEXT
		$show_title			= $this->params->get('show_title', 1);
		
		$title_link			= $this->params->get('title_link', 1);

		$limit_title		= $this->params->get('limit_title', 30);
		
		$show_text			= $this->params->def('show_text', 1);
		
		$limit_text			= $this->params->get('limit_text', 120);

		$show_readmore		= $this->params->get('show_readmore', 0);
		$read_more			= $this->params->get('read_more', 'Read more');
		
		$target				= $this->params->get('target', '_self');

		$intro_clean		= $this->params->get('intro_clean', 1);
		$allowable_tags		= str_replace(' ', '', $this->params->get('allowable_tags'));
		$allowable_tags		= "<".str_replace(',', '><', $allowable_tags).">";
		$video_support		= $this->params->def('video_support', 1);
		$plugins_support	= $this->params->def('plugins_support', 0);

		// INFO
		$date_type			= $this->params->def('date_type', 'created');
		$comment_system		= $this->params->def('comment_system_easyblog', 'easyblog');

		// IMAGES
		$image_width		= $this->params->get('image_width', 246);
		$image_height		= $this->params->get('image_height', 150);
		$image_source		= $this->params->get('image_source_easyblog', 'automatic');
		$image_link			= $this->params->get('image_link', 1);

		// ORDERING
		switch ($ordering) {
			case 'created_asc':
				$orderBy = 'date ASC';
			break;
			case 'title_az':
				$orderBy = 'i.title ASC';
			break;
			case 'title_za':
				$orderBy = 'i.title DESC';
			break;
			case 'popular_first':
				$orderBy = 'i.hits DESC';
			break;
			case 'popular_last':
				$orderBy = 'i.hits ASC';
			break;
			case 'rated_most':
				$orderBy = 'rating_value DESC, rating_count DESC';
			break;
			case 'rated_least':
				$orderBy = 'rating_value ASC, rating_count ASC';
			break;
			case 'commented_most':
				$orderBy = 'comments_count DESC, comments_date DESC';
			break;
			case 'commented_latest':
				$orderBy = 'comments_date DESC, date DESC';
			break;
			case 'ordering_fwd':
				$orderBy = 'i.ordering ASC';
			break;
			case 'ordering_rev':
				$orderBy = 'i.ordering DESC';
			break;
			case 'id_asc':
				$orderBy = 'i.id ASC';
			break;
			case 'id_dsc':
				$orderBy = 'i.id DESC';
			break;
			case 'exact':
				$orderBy = ($source == 6 && $source_itm) ? 'FIELD(i.id, '.$source_itm.')' : 'i.id ASC';
			break;
			case 'random':
				$orderBy = 'RAND()';
			break;
			case 'created_dsc':
			default:
				$orderBy = 'date DESC';
			break;
		}


		// QUERY
		$query->select('i.id, i.title, i.permalink, i.category_id AS category_id, c.alias AS category_alias, i.private');
		
		// Select: Date
		if ($date_type !== 'created') {
			$query->select('CASE WHEN i.'.$date_type.' = \'0000-00-00 00:00:00\' THEN i.created ELSE i.'.$date_type.' END AS date');
		} else {
			$query->select(' i.created AS date');
		}
		
		if ($show_text || $image_source == 'text' || $image_source == 'automatic') {
			$query->select(' i.intro, i.content');
		}
		$image_source != 'text' ? $query->select(' i.image') : '';

		$query->from('#__easyblog_post AS i');

		// Join: Categories
		$query->join('LEFT', '#__easyblog_category AS c ON c.id = i.category_id');
		
		// Join: Rating
		if ($ordering == 'rated_most' || $ordering == 'rated_least') {
			$query->select(' ROUND(SUM(r.value) / COUNT( r.id ), 2) AS rating_value, COUNT( r.id ) AS rating_count');
			$query->join('LEFT', '#__easyblog_ratings AS r ON r.uid = i.id');
		}
		
		// Join: Tags
		$tags ? $query->join('INNER', '#__easyblog_post_tag AS t ON t.post_id = i.id') : '';
		
		// Join: Comments
		if ($show_comments || $ordering == 'commented_most' || $ordering == 'commented_latest') {
			switch ($comment_system) {
				case 'rscomments':
					$checktbl_query = "SHOW TABLES LIKE '%_rscomments_comments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.IdComment) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__rscomments_comments AS jc ON jc.id = a.id AND jc.published = 1');
						$comments_link	= "#rscomments_big_container";
					}
					break;
				case 'komento':
					$checktbl_query = "SHOW TABLES LIKE '%_komento_comments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.created) AS comments_date');
						$query->join('LEFT', '#__komento_comments AS jc ON jc.cid = a.id AND jc.published = 1');
						$comments_link  = "#section-kmt";
					}
					break;
				case 'compojoom':
					$checktbl_query = "SHOW TABLES LIKE '%_comment'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__comment AS jc ON jc.contentid = a.id AND jc.published = 1');
						$comments_link	= "#JOSC_TOP";
					}
					break;
				case 'jcomments':
					$checktbl_query = "SHOW TABLES LIKE '%_jcomments'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.date) AS comments_date');
						$query->join('LEFT', '#__jcomments AS jc ON jc.object_id = a.id AND jc.object_group = \'com_content\' AND jc.published = 1');
						$comments_link	= "#comments";
					}
					break;
				case 'easyblog':
				default:
					$checktbl_query = "SHOW TABLES LIKE '%_easyblog_comment'";
					$db->setQuery($checktbl_query);
					$jc_tbl = $db->loadResult();
					if($jc_tbl) {
						$query->select(' COUNT(jc.id) AS comments_count, MAX(jc.created) AS comments_date');
						$query->join('LEFT', '#__easyblog_comment AS jc ON jc.post_id = i.id AND jc.published = 1');
						$comments_link	= "#comments";
					}
					break;
			}
			if($jc_tbl) {
				$query->group('i.id, i.created');
			} else {
				//Commnent table not found
				$show_comments = false;
				if($ordering == 'commented_most' || $ordering == 'commented_latest') {
					$ordering = 'created_dsc';
					$orderBy = 'date DESC';
				}
			}
		}
		
		// Join: Featured
		if($featured == 'hide' || $featured == 'only') {
			$query->join('LEFT', '#__easyblog_featured AS f ON f.content_id = i.id AND f.type = \'post\'');
		}
		
		// Filter: Published
		$query->where('c.published = 1 AND i.published = 1');

		// Filter: Private
		$private == 0 ? $query->where('i.private = 0') : '';

		// Filter: Tags
		$tags ? $query->where('t.tag_id '.$tags) : '';
		
		// Filter: Featured
		$featured == 'hide' ? $query->where('f.id IS NULL') : ($featured == 'only' ? $query->where('f.id IS NOT NULL') : '');

		// Filter: Language
		if ($app->getLanguageFilter()) {
			$languageTag = $db->quote(JFactory::getLanguage()->getTag());
			$query->where('i.language IN ('.$languageTag.',\'*\')');
		}

		// Filter: Date
		$date	= JFactory::getDate();
		$now	= $db->quote($date->format('Y-m-d H:i:s'));

		$query->where('i.publish_up <= '.$now);
		$query->where('(i.publish_down = \'0000-00-00 00:00:00\' OR i.publish_down >= '.$now.')');
		
		// Filter: Date Range
		switch ($this->params->def('date_filtering', 'disabled'))
		{
			case 'today':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND DATE(i.created) = '.$db->quote($date->format('Y-m-d')).')' : '';
				$query->where('(DATE(i.'.$date_type.') = '.$db->quote($date->format('Y-m-d')).$extraquery.')');
				break;

			case 'this_week':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEARWEEK(i.created) = YEARWEEK('.$now.'))' : '';
				$query->where('(YEARWEEK(i.'.$date_type.') = YEARWEEK('.$now.')'.$extraquery.')');
				break;

			case 'this_month':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEAR(i.created) = YEAR('.$now.') AND MONTH(i.created) = MONTH('.$now.'))' : '';
				$query->where('((YEAR(i.'.$date_type.') = YEAR('.$now.') AND MONTH(i.'.$date_type.') = MONTH('.$now.'))'.$extraquery.')');
				break;

			case 'this_year':
				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND YEAR(i.created) = '.$db->quote($date->format('Y')).')' : '';
				$query->where('(YEAR(i.'.$date_type.') = '.$db->quote($date->format('Y')).$extraquery.')');
				// $query->where('(YEAR(i.'.$date_type.') = YEAR('.$now.')'.$extraquery.')');
				break;

			case 'range':
				$date_range_start	= $db->quote($this->params->get('date_range_start', '1000-01-01 00:00:00'));
				$date_range_end		= $db->quote($this->params->get('date_range_end', '9999-12-31 23:59:59'));

				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND i.created BETWEEN '.$date_range_start.' AND '.$date_range_end.')' : '';
				$query->where('(i.'.$date_type.' BETWEEN '.$date_range_start.' AND '.$date_range_end.$extraquery.')');
				break;

			case 'relative':
				$date_range_from	= $this->params->get('date_range_from');
				$date_range_from	= $date_range_from[0] >= '0' ? $db->quote(date('Y-m-d', strtotime($date.' - '.$date_range_from[0].' day'))) : $db->quote('1000-01-01 00:00:00');
				$date_range_to		= $this->params->get('date_range_to');
				$date_range_to		= $date_range_to[0] >= '0' ? $db->quote(date('Y-m-d', strtotime($date.' - '.$date_range_to[0].' day'))) : $db->quote('9999-12-31 23:59:59');

				$extraquery = ($date_type !== 'created') ? ' OR (i.'.$date_type.' = \'0000-00-00 00:00:00\' AND i.created BETWEEN '.$date_range_from.' AND '.$date_range_to.')' : '';
				$query->where('(i.'.$date_type.' BETWEEN '.$date_range_from.' AND '.$date_range_to.$extraquery.')');
				break;

			case 'disabled':
			default:
				break;
		}
		
		// Filter: Author
		switch ($this->params->get('authors')) {
			case 'by_me':
				if ($userID) {
					$query->where('i.created_by = '.$userID);
				} else {
					return;
				}
				break;
			case 'not_me':
				if ($userID) {
					$query->where('i.created_by <> '.$userID);
				}
				break;
			case 'all':
			default:
				break;
		}
		
		// Filter: Only items has image
		if($filter_image) {
			if ($image_source == 'text' || $image_source == 'automatic') $image_filter_where[] = 'i.intro REGEXP "<img[^>]+>" OR i.content REGEXP "<img[^>]+>"';
			if ($image_source == 'image' || $image_source == 'automatic') $image_filter_where[] = 'i.image REGEXP "\.(jpe?g|gif|png)"';
			$query->where('('.implode(' OR ', $image_filter_where).')');
		}
		
		$tags || @$comments_link || ($ordering == 'rated_most' || $ordering == 'rated_least') ? $query->group('i.id, i.created') : '';

		$query->order($orderBy);
		$db->setQuery($query, $count_skip, $maxitems);
		// Retrieve Content
		$items = $db->loadObjectList();
		$lists = array();
		$slideshowclass 	= self::parseClass();
		$slideshowanimation = self::parseAnimation();
		
		foreach ($items as $i => &$item) {
			$lists[$i] = new stdClass;
			$lists[$i]->id			= '';
			$lists[$i]->title		= '';
			$lists[$i]->link		= '';
			$lists[$i]->date		= '';
			$lists[$i]->image		= $lists[$i]->image_src = $lists[$i]->image_alt = '';
			$lists[$i]->text		= '';
			$lists[$i]->author		= $lists[$i]->author_name = $lists[$i]->author_link = '';
			$lists[$i]->rating		= $lists[$i]->rating_value = $lists[$i]->rating_count = '';
			$lists[$i]->hits		= '';
			$lists[$i]->comments	= $lists[$i]->comments_count = $lists[$i]->comments_link = '';
			$lists[$i]->readmore	= '';
			
			$lists[$i]->class		= $slideshowclass[$i];
			$lists[$i]->animation	= $slideshowanimation[$i];

			$lists[$i]->id			= $item->id;
			$lists[$i]->category_id	= $item->category_id ? $item->category_id : '';


			// Item Link
			if (!$item->private) {
				$lists[$i]->link = urldecode(JRoute::_(EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id='.$item->id)));
			} else {
				$link	= 'index.php?option=com_users&view=login';
				$menu	= $app->getMenu()->getItems('link', $link);
				$lists[$i]->link = isset($menu[0]) ? JRoute::_($link.'&Itemid='.$menu[0]->id) : JRoute::_($link);
			}

			// Show Title
			if ($show_title) {
				$lists[$i]->title = $limit_title ? self::truncateHTML($item->title, $limit_title, '&hellip;', false, false) : $item->title;
				$lists[$i]->title = ($title_link) ? '<a target="'.$target.'" href="'. $lists[$i]->link .'">'.$lists[$i]->title.'</a>' : $lists[$i]->title;
			}
			
			// Sync field name with other source for easily to call it by $list->fieldname...
			$item->introtext 	= $item->intro;
			$item->fulltext		= $item->content;
			
			//Parse video
			$item->introtext = $video_support ? self::parseVideo($item->introtext) : $item->introtext;
			
			// Plugins Support
			$item->introtext = $plugins_support ? JHtml::_('content.prepare', $item->introtext) : preg_replace('/{[^{]+?{\/.+?}|{.+?}/', '', $item->introtext);
			
			// Images
			$img		= array();
			$img_source	= '';
			$image		= @$item->image ? json_decode($item->image) : '';

			if ($image_source == 'automatic') {
				if (@$image->url) {
					$img_source = 'image';
				} else {
					$img_source = 'text';
				}
			} else {
				$img_source = $image_source;
			}

			switch ($img_source) {
				case 'image':
					if (@$image->url) {
						$img['src'] = $image->url;
						$img['alt'] = @$image->title;
						$img['ttl'] = @$item->title;
					}
				break;
				default:
					$pattern = '/<img[^>]+>/i';
					preg_match($pattern, $item->introtext, $img_tag);
					if (!count($img_tag)) {
						preg_match($pattern, $item->fulltext, $img_tag);
					}
					if (count($img_tag)) {
						preg_match_all('/(alt|title|src)\s*=\s*(["\'])(.*?)\2/i', $img_tag[0], $img_atr);
						$img_atr = array_combine($img_atr[1], $img_atr[3]);
						if (@$img_atr['src']) {
							$img['src'] = trim($img_atr['src']);
							$img['alt'] = trim(@$img_atr['alt']);
							$img['ttl'] = trim(@$img_atr['title']);
							$item->introtext = preg_replace($pattern, '', $item->introtext, 1);
						}
					}
				break;
			}
			
			if ($img['src']) {
				// Create Thumbnail
				$lists[$i]->mainimage 	= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_main'), $this->params->get('height_main'));
				$lists[$i]->thumb 		= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_thumb'), $this->params->get('height_thumb'));
				$lists[$i]->image_src	= @$img['src'];
				$lists[$i]->image_alt	= @$img['alt'];
			}

			// Show Text
			if ($show_text) {
				// Clean XHTML
				if ($intro_clean) {
					$item->introtext = strip_tags($item->introtext, $allowable_tags);
					$item->introtext = str_replace('&nbsp;', ' ', $item->introtext);
					$item->introtext = preg_replace('/\s{2,}/u', ' ', trim($item->introtext));
				}
				// Limit Text
				$lists[$i]->text = $limit_text ? self::truncateHtml($item->introtext, $limit_text, '&hellip;', false, true) : $item->introtext;
			}
			
			
			// Show Readmore
			$lists[$i]->readmore = $show_readmore ? '<a class="readmore" target="'.$target.'" href="'.$lists[$i]->link.'">'.$read_more.'</a>' : '';

		}

		return $lists;
	}
	
	/**
     * Get Images in Folder
     * @return array list images
     */
    function getImageFolder()
    {
		JImport('joomla.filesystem.folder');
		
		$show_title			= $this->params->def('show_title', 1);
		$title_link			= $this->params->get('title_link', 1 );
		$limit_title		= $this->params->get('limit_title', 30);
		
		$show_text			= $this->params->def('show_text', 1);
		$limit_text			= $this->params->get('limit_text', 120);

		$show_readmore		= $this->params->get('show_readmore', 0);
		$read_more			= $this->params->get('read_more', '');
		
		$target				= $this->params->get('target', '_self');
		
		$intro_clean		= $this->params->def('intro_clean', 1);
		$allowable_tags		= str_replace(' ', '', $this->params->get('allowable_tags'));
		$allowable_tags		= "<".str_replace(',', '><', $allowable_tags).">";
		$video_support		= $this->params->def('video_support', 1);
		$plugins_support	= $this->params->def('plugins_support', 0);
		
		// IMAGES
		$image_width		= $this->params->get('image_width', 160);
		$image_height		= $this->params->get('image_height', 120);
		$image_link			= $this->params->get('image_link', 1);
		
        $folder = $this->params->get('imagefolder', 'images/');
        $imageordering = $this->params->get('imageordering', 'ordering');
		$path = JPath::clean(JPATH_ROOT . DS .$folder);
		
		//Get image files that has allowed_ext
        $files = JFolder::files($path, "\.(jpg|png|gif|jpeg|bmp)$");
		
        $images = array();
		$gallery = array();
		
        $i = 0;
        foreach ($files as $file) {
            if (is_file($path . $file)) {
                $images[$i] = $file;
				
				$gallery[$i] = new stdClass();
				$gallery[$i]->image = $file;
				$gallery[$i]->title = '';
				$gallery[$i]->link = '';
				$gallery[$i]->class = '';
				$gallery[$i]->animation = '';
				$gallery[$i]->description = '';
				$gallery[$i]->time = filemtime($path . $file);
				$gallery[$i]->published = 1;
                $i++;
            }
        }
		
		$saved_images_json = $this->params->get('jugallery', '[]');
		
		$saved_images_obj = json_decode($saved_images_json);
		//Re-sort image if has saved_images_json
		if($saved_images_obj!=NULL) {
			$order_img_arr = array();
			
			foreach($saved_images_obj AS $key => $saved_image_obj) {
				$index_img = array_search($saved_image_obj->image, $images);
				
				//If saved image does not exist in folder => ignore it
				if($index_img===FALSE) {
					continue;
				//If saved image is unpublished remove from folder image list
				} elseif($saved_image_obj->published=='0') {
					unset($gallery[$index_img]);
				//If saved image is published, add it to order_img_arr, and remove from folder image list
				} else {
					$saved_image_obj->time = $gallery[$index_img]->time;
					$order_img_arr[] = $saved_image_obj;
					unset($gallery[$index_img]);
				}
			}
			$images = array_merge($order_img_arr, $gallery);
		}
		
		//Sort images
		switch ($imageordering) {
			case 'name_asc':
				function img_order_name_asc($a, $b) {
					return strcmp($a->image, $b->image);
				}
				usort($images, "img_order_name_asc");
				break;
			case 'name_desc':
				function img_order_name_desc($a, $b) {
					return strcmp($b->image, $a->image);
				}
				usort($images, "img_order_name_desc");
				break;
			case 'title_asc':
				function img_order_title_asc($a, $b) {
					return strcmp($a->title, $b->title);
				}
				usort($images, "img_order_title_asc");
				break;
			case 'title_desc':
				function img_order_title_desc($a, $b) {
					return strcmp($b->title, $a->title);
				}
				usort($images, "img_order_title_desc");
				break;
			case 'time_asc':
				function img_order_time_asc($a, $b) {
					return strcmp($a->time, $b->time);
				}
				usort($images, "img_order_time_asc");
				break;
			case 'time_desc':
				function img_order_time_desc($a, $b) {
					return strcmp($b->time, $a->time);
				}
				usort($images, "img_order_time_desc");
				break;
			case 'random':
				shuffle($images);
				break;
			case 'ordering':
			default:
				break;
		}
		
		$slideshowanimation = self::parseAnimation();
		$i = 0;
		foreach ($images as $image_item) {
			if ($i == $this->params->get('maxitems', 15)) break;
			$image = JURI::root() . JPath::clean( $folder . '/' . $image_item->image, '/');

			$lists[$i] = new stdClass;
			$lists[$i]->id			= '';
			$lists[$i]->title		= '';
			$lists[$i]->link		= '';
			$lists[$i]->date		= '';
			$lists[$i]->image		= $lists[$i]->image_src = $lists[$i]->image_alt = '';
			$lists[$i]->text		= '';
			$lists[$i]->author		= $lists[$i]->author_name = $lists[$i]->author_link = '';
			$lists[$i]->rating		= $lists[$i]->rating_value = $lists[$i]->rating_count = '';
			$lists[$i]->hits		= '';
			$lists[$i]->comments	= $lists[$i]->comments_count = $lists[$i]->comments_link = '';
			$lists[$i]->readmore	= '';
			$lists[$i]->class 		= $image_item->class;
			$lists[$i]->animation	= $slideshowanimation[$i];
			
			$lists[$i]->link = ($image_item->link) ? JRoute::_($image_item->link) : '';
			
			// Show Title
			if ($show_title) {
				$lists[$i]->title = ($image_item->title) ? $image_item->title : $image_item->image;
				$lists[$i]->title = $limit_title ? self::truncateHTML($lists[$i]->title, $limit_title, '&hellip;', false, false) : $lists[$i]->title;
				$lists[$i]->title = ($title_link && trim($lists[$i]->link)!='') ? '<a target="'.$target.'" href="'. $lists[$i]->link .'">'.$lists[$i]->title.'</a>' : $lists[$i]->title;
			}
			
			// Images
			$img['src']		= $image;
			
			// Image Parameters
			$img['ttl']		= $image_item->title;
			
			$img['alt']		= ($img['ttl']) ? $img['ttl'] : $image_item->image;
						
			// Create Thumbnail
			$lists[$i]->mainimage 	= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_main'), $this->params->get('height_main'));
			$lists[$i]->thumb 		= modJUSlideshowHelper::renderImage($img['src'], $this->params->get('width_thumb'), $this->params->get('height_thumb'));
			$lists[$i]->image_src	= @$img['src'];
			$lists[$i]->image_alt	= @$img['alt'];
			
			// Show Text
			if ($show_text) {
				$introtext = $image_item->description;
				
				//Parse video
				$introtext = $video_support ? self::parseVideo($introtext) : $introtext;
				
				// Plugins Support
				$introtext = $plugins_support ? JHtml::_('content.prepare', $introtext) : $introtext;//preg_replace('/(?<=[^"\']{1})\s*?(?:{[^{]+?{\/.+?}|{.+?})/', '', $introtext);
				
				// Clean XHTML
				if ($intro_clean) {
					$introtext = strip_tags($introtext, $allowable_tags);
					$introtext = str_replace('&nbsp;', ' ', $introtext);
					$introtext = preg_replace('/\s{2,}/u', ' ', trim($introtext));
				}

				// Limit Text
				$lists[$i]->text = $limit_text ? self::truncateHtml($introtext, $limit_text, '&hellip;', false, true) : $introtext;
			}
			
			// Show Readmore
			$lists[$i]->readmore = ($show_readmore && trim($lists[$i]->link)!='') ? '<a class="readmore" target="'.$target.'" href="'.$lists[$i]->link.'">'.$read_more.'</a>' : '';
			
			$i++;
		}
		
        return $lists;
    }
	
	/*
	 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
	 *
	 * @param string $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string $ending Ending to be appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 *
	 * @return string Trimmed string.
	 */
	public function truncateHtml($text, $length = 320, $ending = '&hellip;', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (JString::strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$open_tags = array();
			$total_length = $truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
					// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
						unset($open_tags[$pos]);
						}
					// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = JString::strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length + $content_length > $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entities_length <= $left) {
								$left--;
								$entities_length += JString::strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= JString::substr($line_matchings[2], 0, $left + $entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length >= $length) {
					break;
				}
			}
		} else {
			if (JString::strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = JString::substr($text, 0, $length);
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact && $length > 10) {
			$spacepos = JString::strrpos($truncate, ' ');
			if (isset($spacepos)) {
				$truncate = JString::substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		// close all unclosed html-tags
		if($considerHtml) {
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}
	
	/**
     *
     * Render resized image
     * @param string $image
     * @param int $width
     * @param int $height
     * @return string image
     */
    function renderImage($image, $width = 0, $height = 0) {	
        if ($image) {
			if ( $this->params->get('thumbnailmode', 1) ) {
				$timthumb_params = array();
				if(preg_match('/^https?:\/\/[^\/]+/i', $image)) {
					$timthumb_params['src'] = $image;
				} else {
					$timthumb_params['src'] = JURI::Base().$image;
				}
				$timthumb_params['w'] = $width;
				$timthumb_params['h'] = $height;
				$timthumb_params['q'] = $this->params->get('imagequality','90');
				$timthumb_params['a'] = $this->params->get('alignment','c');
				$timthumb_params['zc'] = $this->params->get('zoomcrop','1');
				if ($this->params->get('customfilters','') != '') { $timthumb_params['f'] = $this->params->get('customfilters',''); }
				else {
					$filters = $this->params->get('filters');
					if (!empty($filters)) {
						$filters = implode("|",$filters);
						$timthumb_params['f'] = $filters;
					}
				}
				$timthumb_params['s'] = $this->params->get('sharpen','0');
				$timthumb_params['cc'] = $this->params->get('canvascolour','FFFFFF');
				$timthumb_params['ct'] = $this->params->get('canvastransparency','1');
				$tb_image = timthumb::start($timthumb_params);
				// If can resize image -> return resized image, else keep original image
				if(trim($tb_image) != '') {
					$image = $tb_image;
				}
                return $image;
			} else {
				return $image;
			}
        } else {
            return '';
        }
    }
	
	/*
	* Parse class: 0:class0, 1:class1,... to array [0=>class0 1=>class1 ...]
	*/
	function parseClass()
	{
		$slideshowclass = $this->params->get('slideshowclass','');
		$class = array();
		if ($slideshowclass!="") {
			$slideshowclass_arr = explode(",", $slideshowclass);
			foreach ($slideshowclass_arr AS $slideshowclass_item) {
				$slideshow_item_arr = explode(":", $slideshowclass_item);
				$class[ trim($slideshow_item_arr[0]) ] = trim($slideshow_item_arr[1]);
			}
		}
		return $class;
	}
	
	/*
	* Parse animation: 0:animation0, 1:animation1,... to array [0=>animation0 1=>animation1 ...]
	*/
	function parseAnimation()
	{
		$slideshowanimation = $this->params->get('slideshowanimation','');
		$animation = array();
		if ($slideshowanimation!="") {
			$slideshowanimation_arr = explode(",", $slideshowanimation);
			foreach ($slideshowanimation_arr AS $slideshowanimation_item) {
				$slideshow_item_arr = explode(":", $slideshowanimation_item);
				$animation[ trim($slideshow_item_arr[0]) ] = trim($slideshow_item_arr[1]);
			}
		}
		return $animation;
	}
	
	/*
	* Return value of specified param from params string
	* Use for sub tab only
	*/
	protected function getParamValue($params_str, $keyword) {
		$params_str = html_entity_decode($params_str, ENT_QUOTES, 'UTF-8');
		$regex_pattern = "#\s*".$keyword."\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))#msi";
		preg_match_all($regex_pattern, $params_str, $matches);
		$value = $matches[2][0] ? $matches[2][0] : ($matches[3][0] ? $matches[3][0] : $matches[4][0]);
		return $value;
	}
	
	/*
	* Parse youtube/vimeo tag
	*/
	function parseVideo($content) {
		$document = JFactory::getDocument();
		//Parse Youtube video
		$regex = "#(?:\{youtube(?:\s|&nbsp;)*?\}|\{youtube(.*?(?:\"|&quot;))(?:\s|&nbsp;)*?\})(.*?)\{\/youtube\}#i";
		preg_match_all( $regex, $content, $matches );
		if(count($matches[0])) {
			foreach($matches[0] as $key=>$match){
				$params = $matches[1][$key];
				$url = trim(strip_tags($matches[2][$key]));
				if(preg_match("/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>\/]+)(?:$|\/|\?|\&)?/", $url, $yt_matches)) {
					$videoid = $yt_matches[1];
				} else {
					$videoid = $url;
				}
				
				$youtube_params = $this->params->get('youtube_params', 'hd=1&wmode=opaque&controls=1&showinfo=0;rel=0');
				if($videoid) {
					$src = "http://www.youtube.com/embed/".$videoid."?".$youtube_params;
					$data_attr_arr = array();
					if(self::getParamValue($params, "data-animation-from")) {
						$data_attr_arr[] = "data-animation-from='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-from"))."'";
					}
					if(self::getParamValue($params, "data-animation-in")) {
						$data_attr_arr[] = "data-animation-in='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-in"))."'";
					}
					if(self::getParamValue($params, "data-animation-timeline")) {
						$data_attr_arr[] = "data-animation-timeline='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-timeline"))."'";
					}
					if(self::getParamValue($params, "data-animation-out")) {
						$data_attr_arr[] = "data-animation-out='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-out"))."'";
					}
					$data_attr = implode(" ", $data_attr_arr);
					
					$youtube_html = '<iframe class="'.self::getParamValue($params, "class").'" style="'.self::getParamValue($params, "style").'" width="'.intval(self::getParamValue($params, "width")).'" height="'.intval(self::getParamValue($params, "height")).'" src="'.$src.'" frameborder="0" allowfullscreen '.$data_attr.'></iframe>';
				} else {
					$youtube_html = '';
				}
				
				$content = str_replace($match, $youtube_html, $content);
			}
			$document->addScript("https://www.youtube.com/iframe_api");
		}
		
		//Parse Vimeo video
		$regex = "#(?:\{vimeo(?:\s|&nbsp;)*?\}|\{vimeo(.*?(?:\"|&quot;))(?:\s|&nbsp;)*?\})(.*?)\{\/vimeo\}#i";
		preg_match_all( $regex, $content, $matches );
		if(count($matches[0])) {
			foreach($matches[0] as $key=>$match){
				$params = $matches[1][$key];
				$url = trim(strip_tags($matches[2][$key]));
				if(preg_match("/^https?:\/\/(?:www\.)?vimeo.com\/(\d+)(?:$|\/|\?)?/", $url, $vm_matches)) {
					$videoid = $vm_matches[1];
				} else {
					$videoid = $url;
				}
				
				$vimeo_params = $this->params->get('vimeo_params', 'title=0&byline=0&portrait=0;api=1');
				if($videoid) {
					$src = "http://player.vimeo.com/video/".$videoid."?".$vimeo_params;
					$data_attr_arr = array();
					if(self::getParamValue($params, "data-animation-from")) {
						$data_attr_arr[] = "data-animation-from='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-from"))."'";
					}
					if(self::getParamValue($params, "data-animation-in")) {
						$data_attr_arr[] = "data-animation-in='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-in"))."'";
					}
					if(self::getParamValue($params, "data-animation-timeline")) {
						$data_attr_arr[] = "data-animation-timeline='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-timeline"))."'";
					}
					if(self::getParamValue($params, "data-animation-out")) {
						$data_attr_arr[] = "data-animation-out='" . str_replace("'", "\"", self::getParamValue($params, "data-animation-out"))."'";
					}
					$data_attr = implode(" ", $data_attr_arr);
					$vimeo_html = '<iframe class="'.self::getParamValue($params, "class").'" style="'.self::getParamValue($params, "style").'" width="'.intval(self::getParamValue($params, "width")).'" height="'.intval(self::getParamValue($params, "height")).'" src="'.$src.'" frameborder="0" allowfullscreen '.$data_attr.'></iframe>';
				} else {
					$vimeo_html = '';
				}
				
				$content = str_replace($match, $vimeo_html, $content);
			}
			$document->addScript("http://a.vimeocdn.com/js/froogaloop2.min.js");
		}
		
		return $content;
	}
}
