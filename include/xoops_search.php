<?php
function tagmemo_search($queryarray, $andor, $limit, $offset, $userid) {
	global $xoopsDB;
	$sql = 'SELECT DISTINCT m.tagmemo_id, m.uid, m.title, m.content, m.timestamp FROM '
	     . $xoopsDB->prefix('tagmemo') . ' AS m LEFT JOIN '
	     . $xoopsDB->prefix('tagmemo_rel') . ' AS r ON m.tagmemo_id = r.tagmemo_id LEFT JOIN '
	     . $xoopsDB->prefix('tagmemo_tag') . ' AS t ON r.tag_id = t.tag_id WHERE';
	
	if ( $userid != 0 ) {
		$sql .= " m.uid=" . $userid;
	} else {
		$sql .= " m.tagmemo_id>0";
	}
	
	if ( is_array($queryarray) && $queryarray ) {
		$sql .= " AND ((m.title LIKE '%$queryarray[0]%' OR m.content LIKE '%$queryarray[0]%' OR t.tag LIKE '%$queryarray[0]%')";
		for($i=1;$i<$count;$i++){
			$sql .= " $andor ";
			$sql .= "(m.title LIKE '%$queryarray[$i]%' OR m.content LIKE '%$queryarray[$i]%' OR t.tag LIKE '%$queryarray[$i]%')";
		}
		$sql .= ") ";
	}
	$sql .= " ORDER BY m.timestamp DESC";
	$result = $xoopsDB->query($sql,$limit,$offset);
	
	$myts =& MyTextSanitizer::getInstance();
	$make_context_func = function_exists( 'search_make_context' )? 'search_make_context' : (function_exists( 'xoops_make_context' )? 'xoops_make_context' : '');
	
	$ret = array();
	$i = 0;
 	while($myrow = $xoopsDB->fetchArray($result)){
		$ret[$i]['image'] = "images/page_find.gif";
		$ret[$i]['link'] = "detail.php?tagmemo_id=".$myrow['tagmemo_id'];
		$ret[$i]['title'] = $myrow['title'];
		$ret[$i]['time'] = $myrow['timestamp'];
		$ret[$i]['uid'] = $myrow['uid'];
		if ($make_context_func) {
			$ret[$i]['context'] = $make_context_func(strip_tags($myts->displayTarea($myrow['content'],$queryarray)));
		}
		$i++;
	}
	return $ret;
}
?>