<?php
/**
* @package Page
*/

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';

$myts =& MyTextSanitizer::getInstance();
$tag_id = empty($_GET["tag_id"]) ? "" :(int)$_GET["tag_id"];
$count = isset($xoopsModuleConfig['per_page']) ? $xoopsModuleConfig['per_page'] : 10;
$start = empty($_GET["start"]) ? 0 : intval($_GET["start"]);

$tagmemo_handler =& xoops_getmodulehandler('tagmemo');

if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}

$tagmemo_handler->setUid($uid);
$memo_array =& $tagmemo_handler->getMemosArray($tag_id,$count,$start);

$xoopsOption['template_main'] = 'tagmemo_list_ajax.html';

require_once XOOPS_ROOT_PATH.'/class/template.php';
$xoopsTpl = new XoopsTpl();

$xoopsTpl->assign("memos", $memo_array);
//error_reporting(E_ALL);
$maxcount = $tagmemo_handler->getMemoCount($tag_id);

include_once './class/pagenavi.php';
$nav = new TagmemoPageNav($maxcount, $count, $start);
$nav->asjs = 'onClick="tagmemo_getTagslist('.$tag_id.',event,_NAV_);return false;"';
$xoopsTpl->assign('pagenav', $nav->renderNav());

$xoopsCachedTemplate = 'db:'.$xoopsOption['template_main'];
$xoopsTpl->display($xoopsCachedTemplate);
?>