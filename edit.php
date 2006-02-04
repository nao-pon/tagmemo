<?php
/**
* @package Page
*/

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';

//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;
$myts =& MyTextSanitizer::getInstance();

$memo_id = isset($_GET["tagmemo_id"]) ? intval($_GET["tagmemo_id"]) : "";
$xoopsOption['template_main'] = 'tagmemo_edit.html';

//ユーザーIDをもらおう
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}

$memo = array();
$module_id = $xoopsModule->mid();

$tagmemo_handler =& xoops_getmodulehandler('tagmemo');
$tagmemo_handler->setUid($uid);
//echo $tagmemo_handler->_condition_uid ;
if($memo_id != ""){
	$memo_id =intval($memo_id );
	$memo =& $tagmemo_handler->getMemo4Edit($memo_id);
	if($memo["uid"] != $uid){
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
	}else{
		if($uid == 0){
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		}else{
			if(!($xoopsUser->isAdmin($module_id))){
				redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
			}
		}
	
	}
}
// ヘッダを書くおまじない。
/**
* XOOPSのテンプレートのヘッダー
*/
include (XOOPS_ROOT_PATH.'/header.php');
	$xoopsTpl->assign("memo", $memo);
//　画面の下の方のフッタを書くおまじない。
if($memo_id != ""){
$xoopsTpl->assign("xoopsGTicket_html", $xoopsGTicket->getTicketHtml( __LINE__ ));
}
/**
* XOOPSのテンプレートのフッター
*/
include(XOOPS_ROOT_PATH.'/footer.php');
?>