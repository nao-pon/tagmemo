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
$tagmemo_objs =& $tagmemo_handler->getMemos();

$xoopsOption['template_main'] = 'tagmemo_tagcloud.html';

// ヘッダを書くおまじない。
/**
* XOOPSのテンプレートのヘッダー
*/
include XOOPS_ROOT_PATH.'/header.php';
/*  $tag_array =& $tagmemo_handler->getAllTags();
 $xoopsTpl->assign('tags', $tag_array); */

	$popular_tags = $tagmemo_handler->getPopularTag();
	 $xoopsTpl->assign('populartags', $popular_tags);
	$recent_tags = $tagmemo_handler->getResentTag();
	 $xoopsTpl->assign('recentTags', $recent_tags);
 	$AllTagEx =$tagmemo_handler->getAllTagsEx();
	 $xoopsTpl->assign('alltags_ex', $AllTagEx);
/**
* XOOPSのテンプレートのフッター
*/
include(XOOPS_ROOT_PATH.'/footer.php');
?>