<?php
/**
 * @package		JBolo
 * @version		$versionID$
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 * @copyright	Copyright © 2009-2013 TechJoomla. All rights reserved.
 * @license		GNU General Public License version 2, or later
*/
//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$document=JFactory::getDocument();
$document=JFactory::getDocument();
if(JVERSION>'3.0'){
	JHtml::_('jquery.framework',false);
}
else{
	include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
	TjAkeebaStrapper::bootstrap();
}

//load required CSS and JS files for file upload
//<!-- Generic page styles -->
$document->addStyleSheet(JURI::root().'components'.DS.'com_jbolo'.DS.'css'.DS.'jboloapi.css');
//<!-- Generic page styles -->
$document->addStyleSheet(JURI::root().'components'.DS.'com_jbolo'.DS.'jbolo'.DS.'assets'.DS.'css'.DS.'style.css');
//<!-- Bootstrap Image Gallery styles -->
$document->addStyleSheet(JURI::root().'components'.DS.'com_jbolo'.DS.'jbolo'.DS.'assets'.DS.'css'.DS.'bootstrap-image-gallery.min.css');
//<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
$document->addStyleSheet(JURI::root().'components'.DS.'com_jbolo'.DS.'jbolo'.DS.'assets'.DS.'css'.DS.'jquery.fileupload-ui.css');
?>

<div class="techjoomla-bootstrap jbolo_word_break_wrap jbolo_pad_me">
	<?php
		if($this->isParticipant !=1 )
		{
			echo '<div class="well">
					<div class="alert alert-error">';
			if(!$this->isParticipant)//not participant
				echo JText::_('COM_JBOLO_NON_MEMBER_MSG');
			elseif($this->isParticipant==2)//inactive participant
				echo JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG');
			elseif($this->isParticipant==3)//not logged in
				echo JText::_('COM_JBOLO_LOGIN_SENDFILE');
			echo '</div>
			</p>
			</div> <!--End bootsrap div-->';
		}
		else
		{
		?>
			<div class="well">
				<div class="alert alert-info">
					<?php
					echo JText::_("COM_JBOLO_FILESIZE_WARNING");
					echo "<br/>";
					echo sprintf(JText::_("COM_JBOLO_FILESIZE_LIMIT"), $this->params->get('maxSizeLimit'));
					echo "<br/>";
					echo sprintf(JText::_("COM_JBOLO_ALLOWED_EXTENSIONS"), $this->params->get('allowedFileExtensions'));
					?>
				</div>
			</div>

			<!-- The file upload form used as target for the file upload widget -->
			<form id="fileupload" method="POST" enctype="multipart/form-data">

				<input type="hidden" name="option" value="com_jbolo">
				<input type="hidden" name="controller" value="sendfile">
				<input type="hidden" name="action" value="uploadFile">

				<script>
					var fileUpload_nid=<?php echo $this->nodeid;?>;
					var fileUpload_maxFileSize=<?php echo $this->params->get('maxSizeLimit')*1024*1024;?>;
					var fileUpload_acceptFileTypes=/(\.|\/)(<?php
							$arr=explode(',', $this->params->get('allowedFileExtensions'));
							echo implode("|",$arr);
					?>)$/i;
				</script>

				<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
				<div class="row-fluid fileupload-buttonbar">

					<div class="span12">
						<!-- The fileinput-button span is used to style the file input field as button -->
						<span class="btn btn-success fileinput-button">
							<i class="icon-plus icon-white"></i>
							<span>Add files...</span>
							<input type="file" name="files[]" multiple >
						</span>
						<button type="submit" class="btn btn-primary start">
							<i class="icon-upload icon-white"></i>
							<span>Start upload</span>
						</button>
						<button type="reset" class="btn btn-warning cancel">
							<i class="icon-ban-circle icon-white"></i>
							<span>Cancel upload</span>
						</button>

						<button type="reset" class="btn btn-danger" onclick="self.close()">
							<i class="icon-ban-circle icon-white"></i>
							<span>Close window</span>
						</button>

					</div>
				</div>

				<div>&nbsp;</div>
				<div class="row-fluid fileupload-buttonbar">
					<!-- The global progress information -->
					<div class="span12 fileupload-progress fade">
						<!-- The global progress bar -->
						<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							<div class="bar" style="width:0%;"></div>
						</div>
						<!-- The extended global progress information -->
						<div class="progress-extended">&nbsp;</div>
					</div>

				</div>
				<!-- The loading indicator is shown during file processing -->
				<div class="fileupload-loading"></div>
				<br>
				<!-- The table listing the files available for upload/download -->
				<table role="presentation" class="table table-striped">
					<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
					</tbody>
				</table>

				<div id="result" class="text-success">
					<div id="msgheader" style="display:none;">Files sent - </div>
				</div>


			</form>

		</div> <!--End bootsrap div-->

		<!-- The template to display files available for upload -->
		<script id="template-upload" type="text/x-tmpl">
		{% for (var i=0, file; file=o.files[i]; i++) { %}
			<tr class="template-upload fade">
				<td class="preview"><span class="fade"></span></td>
				<td class="name"><span>{%=file.name%}</span></td>
				<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
				{% if (file.error) { %}
					<td class="error" colspan="2"><span class="label label-important"><i class=" icon-ban-circle icon-white"></i> Error</span> {%=file.error%}</td>
				{% } else if (o.files.valid && !i) { %}
					<td>
						<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
					</td>
					<td class="start">{% if (!o.options.autoUpload) { %}
						<button class="btn btn-primary">
							<i class="icon-upload icon-white"></i>
							<span>Start</span>
						</button>
					{% } %}</td>
				{% } else { %}
					<td colspan="2"></td>
				{% } %}
				<td class="cancel">{% if (!i) { %}
					<button class="btn btn-warning">
						<i class="icon-ban-circle icon-white"></i>
						<span>Cancel</span>
					</button>
				{% } %}</td>
			</tr>
		{% } %}
		</script>

		<!-- The template to display files available for download-->

		<script id="template-download" type="text/x-tmpl">
		{% for (var i=0, file; file=o.files[i]; i++) { %}
			<tr class="template-download fade">
				{% if (file.error) { %}
					<td></td>
					<td class="name"><span>{%=file.name%}</span></td>
					<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
					<td class="error" colspan="2"><span class="label label-important"><i class=" icon-ban-circle icon-white"></i> Error</span> {%=file.error%}</td>
				{% } else { %}
					<td class="preview">
						{% if (file.thumbnail_url) { %}
						<img src="{%=file.thumbnail_url%}">
						{% } else { %}
						<span class="label label-warning"><i class="icon-info-sign icon-white"></i></span>
						No preview available
						{% } %}
					</td>
					<td class="name">
						{%=file.name%}
					</td>
					<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
					<td colspan="2">
						<span class="label label-success"><i class="icon-circle-arrow-up icon-white"></i> Success</span>
						File sent
					</td>

				{% } %}

			</tr>
		{% } %}
		</script>
	<?php
	}
?>

<!-- Bootstrap CSS fixes for IE6 -->
<!--[if lt IE 7]><link rel="stylesheet" href="<?php echo JURI::root();?>components/com_jbolo/jbolo/assets/css/bootstrap-ie6.min.css"><![endif]-->

<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="<?php echo JURI::root();?>components/com_jbolo/jbolo/assets/css/jquery.fileupload-ui-noscript.css"></noscript>

<script src="components/com_jbolo/jbolo/model/jquery-1.8.3.min.js"></script>

<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="components/com_jbolo/jbolo/assets/js/jquery.ui.widget.js"></script>

<!-- The Templates plugin is included to render the upload/download listings -->
<script src="components/com_jbolo/jbolo/assets/js/tmpl.min.js"></script>

<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="components/com_jbolo/jbolo/assets/js/load-image.min.js"></script>

<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="components/com_jbolo/jbolo/assets/js/canvas-to-blob.min.js"></script>

<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="components/com_jbolo/jbolo/assets/js/jquery.iframe-transport.js"></script>

<!-- The basic File Upload plugin -->
<script src="components/com_jbolo/jbolo/assets/js/jquery.fileupload.js"></script>

<!-- The File Upload file processing plugin -->
<script src="components/com_jbolo/jbolo/assets/js/jquery.fileupload-fp.js"></script>

<!-- The File Upload user interface plugin -->
<script src="components/com_jbolo/jbolo/assets/js/jquery.fileupload-ui.js"></script>

<!-- The main application script -->
<script src="components/com_jbolo/jbolo/assets/js/main.js"></script>

<!-- Shim to make HTML5 elements usable in older Internet Explorer versions -->
<!--[if lt IE 9]><script src="<?php echo JURI::root();?>components/com_jbolo/jbolo/assets/js/html5.js"></script><![endif]-->