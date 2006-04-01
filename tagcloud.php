<?php
/**
* @package Page
*/

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';
//$tagmemo_handler =& xoops_getmodulehandler('tagmemo');
// $tagmemo_handler =& xoops_getmodulehandler('memo');
$tagmemo_handler =& xoops_getmodulehandler('tagmemo');
//$tagmemo_objs =& $tagmemo_handler->getMemos();

$xoopsOption['template_main'] = 'tagmemo_tagcloud.html';

// ヘッダを書くおまじない。
/**
* XOOPSのテンプレートのヘッダー
*/
include XOOPS_ROOT_PATH.'/header.php';

// タグリストのポップアップ指定
$xoopsTpl->assign("taglist_popup",empty($xoopsModuleConfig['tagpopup_cloud'])? false : true);

 	//$cloud =$tagmemo_handler->_tag_handler->getTagArrayForCloud();
 	$cloud =$tagmemo_handler->getAllTagsEx();
	$xoopsTpl->assign('cloud', $cloud);
/**
* XOOPSのテンプレートのフッター
*/
include(XOOPS_ROOT_PATH.'/footer.php');
?>