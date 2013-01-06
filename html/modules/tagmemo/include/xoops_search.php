<?php
function tagmemo_search($queryarray, $andor, $limit, $offset, $userid = 0)
{
	global $xoopsDB;

	$module_handler =& xoops_gethandler('module');
	$config_handler =& xoops_gethandler('config');
	$basename = basename(dirname(dirname(__FILE__)));
	$xoopsModule =& $module_handler->getByDirname($basename);
	$xoopsModuleConfig =& $config_handler->getConfigsByCat(0, $xoopsModule->getVar('mid'));

	$do_search_tag = $xoopsModuleConfig['tagmemo_search_tag'];
	
	$flg_search_tag = ($do_search_tag && $queryarray);
	
	$sql = 'SELECT ' . ($flg_search_tag? 'DISTINCT ' : '')
	     . 'm.tagmemo_id, m.uid, m.title, m.content, m.timestamp FROM '
	     . $xoopsDB->prefix('tagmemo') . ' AS m';
	
	if ($flg_search_tag) {
		$sql .= ' LEFT JOIN ' . $xoopsDB->prefix('tagmemo_rel') . ' AS r ON m.tagmemo_id = r.tagmemo_id'
		      . ' LEFT JOIN ' . $xoopsDB->prefix('tagmemo_tag') . ' AS t ON r.tag_id = t.tag_id';
	}
	
	$sql .= ' WHERE';
	
	if ($userid) {
		$sql .= ' m.uid = ' . $userid;
	}
	
	if (is_array($queryarray) && $queryarray){
		$querys =array();
		foreach($queryarray as $query){
			if ($flg_search_tag){
				$querys[] = '(m.content LIKE \'%' . $query . '%\' OR t.tag LIKE \'%' . $query. '%\')';
			} else {
				$querys[] = 'm.content LIKE \'%' . $query . '%\'';
			}
		}
		if ($userid) $sql .= ' AND';
		$sql .= ' (' . join(' ' . $andor . ' ', $querys) . ')';
	}
	$sql .= " ORDER BY m.timestamp DESC";
	$result = $xoopsDB->query($sql,$limit,$offset);
	
	$myts =& MyTextSanitizer::getInstance();
	
	// for XOOPS search module.
	// http://xoops.suinyeze.com/modules/mydownloads/singlefile-cid-6-lid-10.html
	$make_context_func = function_exists( 'search_make_context' )?
		'search_make_context' : 
		(function_exists( 'xoops_make_context' )? 'xoops_make_context' : '');
	
	$ret = array();
	$i = 0;
 	while($myrow = $xoopsDB->fetchArray($result)){
		$ret[$i]['image'] = "images/page_find.gif";
		$ret[$i]['link']  = "detail.php?tagmemo_id=".$myrow['tagmemo_id'];
		$ret[$i]['title'] = $myrow['title'];
		$ret[$i]['time']  = $myrow['timestamp'];
		$ret[$i]['uid']   = $myrow['uid'];
		if ($make_context_func) {
			$ret[$i]['context'] = $make_context_func(strip_tags($myts->displayTarea($myrow['content'],$queryarray)));
		}
		$i++;
	}
	return $ret;
}
?>