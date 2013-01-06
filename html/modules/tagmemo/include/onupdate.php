<?php
/*
 * Created on 2010/04/30 by nao-pon http://xoops.hypweb.net/
 * $Id: onupdate.php,v 1.1.2.1 2010/04/30 00:37:06 nao-pon Exp $
 */

function xoops_module_update_tagmemo ( $module ) {

	$db =& Database::getInstance();

	$table = $db->prefix('tagmemo_rel');
    if ($result = $db->query('SHOW INDEX FROM `' . $table . '`')) {
        $keys = array( 'tag_id' => '',
                       'tagmemo_id' => '' );
        while($arr = $db->fetchArray($result)) {
        	unset($keys[$arr['Key_name']]);
        }
        foreach ($keys as $_key => $_val) {
        	$query = 'ALTER TABLE `' . $table . '` ADD INDEX(`'.$_key.'`'.$_val.')';
        	$db->query($query);
        }
    }

	return TRUE;
}