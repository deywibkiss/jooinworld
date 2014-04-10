<?php 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$toolbar = CToolbarLibrary::getInstance();

echo '<div id="community-wrap">'.$toolbar->getHTML().'</div>';
?>