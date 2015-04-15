<?php
/**
* @package Page
*/

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';

(method_exists('MyTextSanitizer', 'sGetInstance') and $myts =& MyTextSanitizer::sGetInstance()) || $myts =& MyTextSanitizer::getInstance();
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
$query_condition = htmlspecialchars($query_condition, ENT_COMPAT, _CHARSET);
$tag_condition = $tagmemo_handler->getTagCondition();
$tagmemo_related_tags = $tagmemo_handler->getRelatedTags();

$memo_array = array_splice($memo_array,$start,$count);

// favicon_src.
if (is_file(XOOPS_ROOT_PATH . '/class/hyp_common/favicon.php')) {
	$favicon_src = XOOPS_URL . '/class/hyp_common/favicon.php';
} else {
	$favicon_src = '';
}

$xoopsOption['template_main'] = 'tagmemo_list_ajax.html';

require_once XOOPS_ROOT_PATH.'/class/template.php';
$xoopsTpl = new XoopsTpl();

$xoopsTpl->assign("memos", $memo_array);
$xoopsTpl->assign("favicon_src", $favicon_src);
$xoopsTpl->assign("rel_tags", $tagmemo_related_tags);
$xoopsTpl->assign("query_condition", $query_condition);
$xoopsTpl->assign("query_condition_url", urlencode($query_condition));
$xoopsTpl->assign("tag_condition", $tag_condition);
//error_reporting(E_ALL);
//$maxcount = $tagmemo_handler->getMemoCount($tag_id);

include_once './class/pagenavi.php';
$nav = new TagmemoPageNav($maxcount, $count, $start);
$nav->asjs = 'onClick="return(tagmemoList.getTagslist(\''.$condition.'\',event,_NAV_))"';
$xoopsTpl->assign('pagenav', $nav->renderNav());

$xoopsCachedTemplate = 'db:'.$xoopsOption['template_main'];

header ("Content-Type: text/plain; charset=". _CHARSET );
$xoopsTpl->display($xoopsCachedTemplate);
?>