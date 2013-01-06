<?php
/**
* @package block
*/

/**
*人気タグブロック
*@return array
*/
function b_tagmemo_poulartag(){
	b_tagmemo_init();
	global $tagmemo_block_popular_hide;
	if($tagmemo_block_popular_hide == true){
		return false;
	}
	$tagmemo_tag_handeler =& xoops_getmodulehandler("tag", 'tagmemo');
	$ret = $tagmemo_tag_handeler->getPopularTag();
	return $ret;
}

/**
*最近のタグブロック
*@return array
*/
function b_tagmemo_recenttag(){
	b_tagmemo_init();
	global $tagmemo_block_recent_hide;
	if($tagmemo_block_recent_hide == true){
		return false;
	}
	$tagmemo_tag_handeler =& xoops_getmodulehandler("tag", 'tagmemo');
	$ret = $tagmemo_tag_handeler->getResentTag();
	return $ret;
}

function b_tagmemo_relatedtag(){
	b_tagmemo_init();
	global $tagmemo_related_tags;
	$ret["reltags"] = $tagmemo_related_tags;
	global $tag_condition;
	$ret["tag_condition"]=$tag_condition;
	global $tagmemo_query;
	$ret["query"]=$tagmemo_query;
	global $query_condition;
	$ret["query_condition"]=trim($query_condition);
	$ret["query_condition_url"]=urlencode($query_condition);
	$src_type = empty($_GET['src_type']) ? 0 : 1;
	$ret["src_type"] = $src_type;
	return $ret;
}
function b_tagmemo_id_serch(){
	return true;
}

function b_tagmemo_cloud(){
	$tagmemo_handler =& xoops_getmodulehandler("tagmemo", 'tagmemo');
	$ret = $tagmemo_handler->getAllTagsEx();
	b_tagmemo_init();
	return $ret;
}

function b_tagmemo_init()
{
	static $done = false;
	
	if ($done) return;
	$done = true;
	
	$module_handler =& xoops_gethandler('module');
	$Module =& $module_handler->getByDirname("tagmemo");
	$config_handler =& xoops_gethandler('config');
	$ModuleConfig =& $config_handler->getConfigsByCat(0, $Module->mid());
	if (!defined("_MD_TAGMEMO_SHORTURL")) define("_MD_TAGMEMO_SHORTURL", empty($ModuleConfig['tagmemo_shorturl'])? false : true);	
}
?>