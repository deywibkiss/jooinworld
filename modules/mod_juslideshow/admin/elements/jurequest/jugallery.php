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

//Disable all error report that may break JSON result
error_reporting(0);

JImport('joomla.filesystem.folder');

class jugallery{
	
	protected $params;
	protected $allowed_ext = 'bmp|gif|jpg|png|jpeg';
	
	function __construct($params){
		$this->params = $params;
	}
	
	/**
     *
     * Get all image from image source and render them
     * @param object $params
     * @return array image list
     */
    public function loadImages()
    {
		$folder = JRequest::getString('path', '');		
		$galleryfieldid = JRequest::getString('galleryfieldid', '');
		$folderfieldid = JRequest::getString('folderfieldid', '');
		
		if(!$folder){
			return null;
		}

		$saved_data = self::readDirectory($folder, $folderfieldid, $galleryfieldid);
		$images = $saved_data['images'];
		$published = $saved_data['published'];
        $data = array();
		
		if(empty($images)){
			$data['success'] = 0;	
			$data['jsondata'] = '[]';
			$data['reports'] = JText::_("FOLDER_EMPTY");
			$data['html'] = '';
			return $data;
		}
		$i = 0 ;
		
		$html = '<head>
			<script type="text/javascript">
				JU_jQuery(document).ready(function($){
					$("#'.$galleryfieldid.'").prev(".jugallery-holder").find(".jugallery").dragsort({ dragSelector: "div", dragBetween: false, dragEnd: function(){saveOrder(JU_jQuery(this), \''.$galleryfieldid.'\');}, placeHolderTemplate: "<li class=\'placeHolder\'><div></div></li>", dragSelectorExclude: "span, input, textarea" });
					
					$("#'.$galleryfieldid.'").parent().find(".jugallery-holder .img-element .view-image").each(function(){
						$(this).click(function(){
							viewImagePopup($(this).parent().parent().parent().parent().next(".jugallery-description"), $(this).parent().find(".img-item").attr("src"));
						});
					});
					
					$("#'.$galleryfieldid.'").parent().find(".jugallery-holder .img-element .publish-image").each(function(){
						$(this).click(function(){
							updateImageData($(this).parent().parent().data("itemid"), $(this).parent().parent().parent().parent().next(), ($(this).parent().hasClass("unpublished") ? 1 : 0));
						});
					});
					
					$("#'.$galleryfieldid.'").parent().find(".jugallery-holder .img-element .delete-image").each(function(){
						$(this).click(function(){
							deleteimage($(this), $(this).parent().parent().data("itemid"));
						});
					});
					
					$("#'.$galleryfieldid.'").parent().find(".jugallery-holder .img-element .edit-img").each(function(){
						$(this).click(function(){
							imageFormPopup($(this).parent().parent().parent().parent().next(".jugallery-description"), $(this).parent().parent().data("itemid"));
						});
					});
 				});
			</script>
			</head>';
		$html .= '<body>';
		$html .= '<ul class="jugallery">';
		
		
		$jsonData = array();
		
        foreach ($images as $k => $img) {
			$jsonData[] = "{\"image\":\"".$img."\",\"title\":\"\",\"link\":\"\",\"description\":\"\",\"class\":\"\",\"published\":1}";
			
			$html .= '<li data-itemid="'.$img.'">';
			$html .= '<div class=\'img-element '.($published[$img] ? '' : 'unpublished').'\'>';
			$html .= '<img class="img-item" src="'.JURI::root() . $folder . $img.'" />';
			$html .= '<span class="view-image" name="view" title="'.JText::_("VIEW").'">'.JText::_("VIEW").'</span>';
			$html .= '<span class="publish-image" name="published" title="'.JText::_("PUBLISH").'">'.JText::_("PUBLISH").'</span>';
			$html .= '<span class="delete-image" title="'.JText::_("DELETE").'">'.JText::_("DELETE").'</span>';
			$html .= '<span class="edit-img" title="'.JText::_("EDIT").' '.$img.'"><span class="edit-icon">'.JText::_("EDIT").'</span><span class="img-name">['.$img.']</span></span>';
			$html .= '</div>';
			$html .= '</li>';
	
        }
		$html .= '</ul>';
		$html .= '<div class=\'squeezebox-placeholder\' style=\'display: none;\'></div>';
		$html .= '</body>';
		
		if(!empty($saved_data['jsondata'])) {
			$jsonData = $saved_data['jsondata'];
		}
		
		$jsonData = "[".implode(",",$jsonData)."]";
		
		$data['jsondata'] = $jsonData;
		$data['html'] = $html;
		$data['success'] = 1;
        return $data;
    }	
	
	/**
     *
     * Get all image from resource
     * @param strinh $folder folder path
     * @param string $orderby
     * @param string $sort
     * @return array images
     */
    protected function readDirectory($folder, $folderfieldid, $galleryfieldid)
    {
        $imagePath = JPATH_SITE . "/" . $folder;
		//Get image files that has allowed_ext
        $imgFiles = JFolder::files($imagePath, "\.(".$this->allowed_ext.")$");
		
        $images = array();
		$data =  array();
		
        $i = 0;
		$published_arr = array();
        foreach ($imgFiles as $file) {		
            if (is_file($imagePath.$file)) {
                $images[$i] = $file;
				$published_arr[$file] = 1;
                $i++;
            }
        }
		
		$galleryfield_xml_name = str_replace("jform_params_", "", $galleryfieldid);
		$folderfield_xml_name = str_replace("jform_params_", "", $folderfieldid);
		
		//If has description => use description as saved_images_json(Reload folder)
		if(JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW)!='') {
			$saved_images_json = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
		//If load image from saved folder => use description from DB
		} elseif($folder==$this->params->get($folderfield_xml_name, 'images/')) {
			$saved_images_json = $this->params->get($galleryfield_xml_name, '[]');
		//Load image from new folder => description will be empty
		} else {
			$saved_images_json = '';
		}

		$new_jsondata_obj = array();
		
		//Re-sort image if has saved_images_json
		if($saved_images_json!='') {
			$saved_images_obj = json_decode($saved_images_json);
			$order_img_arr = array();
			
			foreach($saved_images_obj AS $key => $saved_image_obj) {
				$index_img = array_search($saved_image_obj->image, $images);
				if($index_img!==FALSE) {
					$order_img_arr[] = $saved_image_obj->image;
					$published_arr[$saved_image_obj->image] = $saved_image_obj->published;
					unset($images[$index_img]);
					$new_jsondata_obj[] = json_encode($saved_image_obj);
				}
			}
			$images = array_merge($order_img_arr, $images);
		}
		
		$data['images'] = $images;
		$data['published'] = $published_arr;
		$data['jsondata'] = $new_jsondata_obj;
        return $data;
    }
	
	//Upload image
	public function uploadimages() {
		$upload_folder = JRequest::getVar('folder');
		$upload_folder = JPATH_SITE . "/" . $upload_folder;
		
		if(!is_dir($upload_folder)) {
			$data['reports'] = JText::_("FOLDER_DOES_NOT_EXIST");
			$data['success'] = 0;
		}
		
		if(!count($_FILES)) {
			$data['reports'] = JText::_("NO_IMAGE_HAS_BEEN_UPLOADED");
			$data['success'] = 0;
			return $data;
		}
		
		$error_files = array();
		foreach ($_FILES["images"]["error"] as $key => $error) {
			$filename = $_FILES["images"]["name"][$key];
			
			//Only accept to upload image file that has allowed_ext
			if ($error == UPLOAD_ERR_OK && preg_match("/\.(".$this->allowed_ext.")$/i", $filename)) {
				//Filter filename
				$filename = preg_replace('/[\s]+/', '_', $filename);
				$filename = preg_replace('/[^a-zA-Z0-9\.\-_]+/', '', $filename);
				move_uploaded_file($_FILES["images"]["tmp_name"][$key], $upload_folder . $filename);
			} else {
				$error_files[] = $filename;
			}
		}

		if (count($error_files) > 0) {
			$data['reports'] = JText::_("ERROR_WHEN_UPLOAD_IMAGES").': '.implode(',', $error_files);
			$data['success'] = 0;
		} else {
			$data['reports'] = JText::_("SUCCESSFULLY_UPLOAD_IMAGES");
			$data['success'] = 1;
		}
		return $data;
	}
	
	//Delete image
	public function deleteimage() {
		$folder_path = JRequest::getVar('path');
		$folder_path = JPATH_SITE . "/" . $folder_path;
		if(substr($folder_path, -1) != '/'){
			$folder_path = $folder_path . "/";
		}
		
		$filename = JRequest::getVar('image');
		$file_path = $folder_path . $filename;
		
		//Only accept to delete image file that has allowed_ext
		if(!preg_match("/\.(".$this->allowed_ext.")$/i", $filename)) {
			$data['reports'] = JText::_("CAN_NOT_DELETE_IMAGE");
			$data['success'] = 0;
			return $data;
		}
		
		if(JFile::delete($file_path)) {
			$data['success'] = 1;
			$data['reports'] = JText::_("SUCCESSFULLY_DELETE_IMAGE");
		} else {
			$data['reports'] = JText::_("CAN_NOT_DELETE_IMAGE");
			$data['success'] = 0;
		}
		return $data;
	}
}