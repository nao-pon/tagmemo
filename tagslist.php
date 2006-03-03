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
$tag_id = empty($_GET["tag_id"]) ? "" : preg_replace("/[^\d,]+/","",$_GET["tag_id"]);
$condition = $tag_id;
$tag_id = explode(",",$tag_id);
$count = isset($xoopsModuleConfig['per_page']) ? $xoopsModuleConfig['per_page'] : 10;
$start = empty($_GET["start"]) ? 0 : intval($_GET["start"]);

$tagmemo_handler =& xoops_getmodulehandler('tagmemo');

if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}

$tagmemo_handler->setUid($uid);
$memo_array =& $tagmemo_handler->getMemosArray($tag_id);
$maxcount = count($memo_array);

$query_condition = $tagmemo_handler->getQueryCondition();
$query_condition = stripslashes($query_condition );
$query_condition = htmlspecialchars($query_condition );
$tag_condition = $tagmemo_handler->getTagCondition();
$tagmemo_related_tags = $tagmemo_handler->getRelatedTags();

$memo_array = array_splice($memo_array,$start,$count);

$xoopsOption['template_main'] = 'tagmemo_list_ajax.html';

require_once XOOPS_ROOT_PATH.'/class/template.php';
$xoopsTpl = new XoopsTpl();

$xoopsTpl->assign("memos", $memo_array);
$xoopsTpl->assign("rel_tags", $tagmemo_related_tags);
$xoopsTpl->assign("query_condition", $query_condition);
$xoopsTpl->assign("query_condition_url", urlencode($query_condition));
$xoopsTpl->assign("tag_condition", $tag_condition);
//error_reporting(E_ALL);
//$maxcount = $tagmemo_handler->getMemoCount($tag_id);

include_once './class/pagenavi.php';
$nav = new TagmemoPageNav($maxcount, $count, $start);
$nav->asjs = 'onClick="tagmemo_getTagslist(\''.$condition.'\',event,_NAV_);return false;"';
$xoopsTpl->assign('pagenav', $nav->renderNav());

$xoopsCachedTemplate = 'db:'.$xoopsOption['template_main'];
$xoopsTpl->display($xoopsCachedTemplate);
?>