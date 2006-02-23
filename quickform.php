<?php
/**
* @package Page
*/


require '../../mainfile.php';
//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;

$token ='token should be created by php here.';

// set vars for javascript dinamically;

echo 'baseurl = \''.XOOPS_URL.'/modules/tagmemo\';';
echo 'token = \''.$token.'\';';

// output static javascript;

include './include/javascript/prototype/prototype.js';

include './include/javascript/scriptaculous/dragdrop.js';
include './include/javascript/scriptaculous/effects.js';

include './include/javascript/tagmemo_quickform.js';

exit();

?>