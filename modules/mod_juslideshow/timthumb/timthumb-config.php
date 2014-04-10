<?php
/**
 * TimThumb by Ben Gillbanks and Mark Maunder
 * Based on work done by Tim McDaniels and Darren Hoyt
 * http://code.google.com/p/timthumb/
 * 
 * @license - GNU General Public License, version 2
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted accessd');

error_reporting(0);

/**
--- TimThumb CONFIGURATION ---
This file use for overriding TimThumb Configuration
*/

//Image fetching and caching
define ('ALLOW_EXTERNAL', TRUE);
define ('ALLOW_ALL_EXTERNAL_SITES', false);		
define ('FILE_CACHE_ENABLED', TRUE);
define ('FILE_CACHE_TIME_BETWEEN_CLEANS', 86400);
define ('FILE_CACHE_MAX_FILE_AGE', 86400);
define ('FILE_CACHE_SUFFIX', '.resized');
define ('FILE_CACHE_PREFIX', '');
define ('FILE_CACHE_DIRECTORY', 'images/ju_cached_images');
define ('MAX_FILE_SIZE', 10485760);

//Browser caching
define ('BROWSER_CACHE_MAX_AGE', 864000);
define ('BROWSER_CACHE_DISABLE', false);

//Image size and defaults
define ('MAX_WIDTH', 2800);
define ('MAX_HEIGHT', 1800);
define ('NOT_FOUND_IMAGE', '');
define ('ERROR_IMAGE', '');
define ('PNG_IS_TRANSPARENT', FALSE);
define ('DEFAULT_Q', 90);
define ('DEFAULT_CC', 'ffffff');
?>