<?php
/*
 * Created on 2006/02/13
 */

require_once('../../../include/cp_header.php');
include_once "../include/gtickets.php" ;

$op = isset($_POST['op']) ? $_POST['op'] : 'main';
if ($op != 'main') {
	if ( !$xoopsGTicket->check() ) {
		redirect_header(XOOPS_URL.'/', 3, $xoopsGTicket->getErrors());
	}
	if ($op != 'seturl') {
		exit('Irregular post found');
	}
}
$dist = XOOPS_ROOT_PATH."/modules/tagmemo/include/src/tagmemo.src.dist";
$filename = XOOPS_ROOT_PATH."/uploads/tagmemo/tagmemo.src";

$error = '';
if (!file_exists($filename)) {
	if (!copy($dist, $filename)) {
		$error = 'Copying of file went wrong.';
	}
}

if ($error == '') {
	if ( $file = fopen($filename,"r") ) {
		$content = fread($file, filesize($filename));
		fclose($file);
		
		if(preg_match("/action=\"XOOPS_URL\/modules\/tagmemo\/index\.php\"/", $content)){
			$content = str_replace('XOOPS_URL', XOOPS_URL, $content);
			$content = str_replace('_CHARSET', _CHARSET, $content);
			
			if ($op == 'seturl') {
				if ( @$file = fopen($filename,"w") ) {
					if (fwrite($file, $content) > 0) {
						fclose($file);
						redirect_header(XOOPS_URL."/modules/tagmemo/admin/index.php", 2, 'Setting of url succeeded.');
					}
					$error = 'The writing of a file went wrong.';
					fclose($file);
				} else {
					$error = 'The writing to a file is not permitted.';
				}
			} else {
				@chmod($filename, 0777);
				if (!is_writeable($filename)) {
					$error = 'The writing to a file is not permitted.';
				}
			}
		} else {
			$error = 'File format is wrong.(or It is already written in.)';
		}
	} else {
		$error = 'File cannot be opened.';
	}
}

xoops_cp_header();
include('./mymenu.php');  // add Hiro
if ($error == '') {
	echo "<h4>Set this site's url to forefox search plugin file.</h4>\n";
	echo "<div>Are you sure?</div>\n";
	echo "<form method=\"post\" action=\"ff_plugin.php\">\n";
	echo "<input type=\"hidden\" name=\"op\" value=\"seturl\"></input>\n";
	echo "<input type=\"hidden\" name=\"op\" value=\"seturl\"></input>\n";
	echo $xoopsGTicket->getTicketHtml( __LINE__ );
	echo "<input type=\"submit\" value=\"OK\"></input>\n";
	echo "<input type=\"button\" onclick=\"location.href='index.php'\" value=\"Cancel\"></input>\n";
	echo "</form>";
} else {
	echo "<h4>{$error}</h4>";
}
xoops_cp_footer();
?>
