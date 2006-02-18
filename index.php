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
$tag_id = empty($_GET["tag_id"]) ? "" :$_GET["tag_id"];
$condition = $tag_id;
$tag_id = explode(",",$tag_id);
$keyword = empty($_GET["query"]) ? "" :$_GET["query"];
//$keyword = $myts->addSlashes($keyword);
$condition .= $keyword;
$count = isset($xoopsModuleConfig['per_page']) ? $xoopsModuleConfig['per_page'] : 10;
$count = (strlen($condition)>0) ? 0 : $count;
//$search_from = empty($_GET["from"]) ? "sub" :$_GET["from"];
$start = empty($_GET["start"]) ? 0 : intval($_GET["start"]);
$tagmemo_handler =& xoops_getmodulehandler('tagmemo');

if(strlen($keyword)>0){
	$tagmemo_handler->search($keyword);
//	if($search_from=="all"){
		$tag_id  = array();
//	}
}

if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}
	$tagmemo_handler->setUid($uid);
$memo_array =& $tagmemo_handler->getMemosArray($tag_id,$count,$start);
if(strlen($condition)>0){
	$tagmemo_query=true;
	$query_condition = $tagmemo_handler->getQueryCondition();
	$query_condition = stripslashes($query_condition );
	$query_condition = htmlspecialchars($query_condition );
	$tag_condition = $tagmemo_handler->getTagCondition();
	$tagmemo_related_tags = $tagmemo_handler->getRelatedTags();
}else{
	$tagmemo_query=false;
//	$tagmemo_block_recent_hide = true;
}
$xoopsOption['template_main'] = 'tagmemo_list.html';
// echo 'my uid =' . $xoopsUser->getVar("uid");
// ヘッダを書くおまじない。
/**
* XOOPSのテンプレートのヘッダー
*/
include XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign("memos", $memo_array);
if(strlen($condition)>0){
	$xoopsTpl->assign("query",true);
	$xoopsTpl->assign("rel_tags", $tagmemo_related_tags);
	$xoopsTpl->assign("query_condition", $query_condition);
	$xoopsTpl->assign("query_condition_url", urlencode($query_condition));
	$xoopsTpl->assign("tag_condition", $tag_condition);
}else{

	$xoopsTpl->assign("query",false);
	$maxcount = $tagmemo_handler->getMemoCount();
	include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
	$nav = new XoopsPageNav($maxcount, $count, $start);
	$xoopsTpl->assign('pagenav', $nav->renderNav());
}

$xoopsTpl->assign('bookmarklet','javascript: tagmemo_quickform_script = document.createElement(\'script\'); tagmemo_quickform_script.src = \''.XOOPS_URL.'/modules/tagmemo/quickform.php\'; tagmemo_quickform_script.type = \'text/javascript\'; void(document.body.appendChild(tagmemo_quickform_script));');
$ff_plugin = isset($xoopsModuleConfig['ff_plugin']) ? $xoopsModuleConfig['ff_plugin'] : 0;
$xoopsTpl->assign('ff_plugin', $ff_plugin);

/**
* XOOPSのテンプレートのフッター
*/
include(XOOPS_ROOT_PATH.'/footer.php');
?>
