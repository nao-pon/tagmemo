<?php

$tag = $_GET['tag'];

$class = 'exist';
//$class = 'noexist';

header('Content-type: text/xml; charset=utf-8');
echo '<?xml version=\'1.0\'?>';
echo '<response>';
echo '<tag>'.htmlspecialchars($tag, ENT_QUOTES).'</tag>';
echo '<class>'.$class.'</class>';
echo '</response>';

?>