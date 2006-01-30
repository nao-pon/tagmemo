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
include './include/prototype140.js';
//include './include/scriptaculous.js';

include './include/dragdrop.js';
include './include/effects.js';


include './include/tagmemo_quickform.js';

exit();

?>