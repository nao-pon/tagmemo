<?php
/*
require_once '../../mainfile.php';

$tag = $_GET['tag'];

echo "tag : $tag <br/>";

$tagmemo_handler =& xoops_getmodulehandler("tagmemo");

echo 'iaExist? : ';
if($tagmemo_handler->isExistTag($tag)){
	$class = 'exist';
} else {
	$class = 'noexist';
}

echo "<br/><br/> dump of \$tagmemo_handler->_tags : ";

// 空っぽだけどほわい?　
// 手続きをまちがっとるんやろか？

echo '<pre>';
var_dump($tagmemo_handler->_tags);
echo '</pre>';
*/

$tag = $_GET['tag'];

//$class = 'exist';
$class = 'noexist';

header('Content-type: text/xml; charset=utf-8');
echo '<?xml version=\'1.0\'?>';
echo '<response>';
echo '<tag>'.htmlspecialchars($tag, ENT_QUOTES).'</tag>';
echo '<class>'.$class.'</class>';
echo '</response>';

?>