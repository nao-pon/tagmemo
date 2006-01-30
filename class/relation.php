<?php
/**
* タグとメモの関連を扱うクラスを定義
* @package Persistence
*/
/**
* Xoops Objectの定義読み込み
*/
include_once XOOPS_ROOT_PATH."/class/xoopsobject.php";
/**
* タグとメモの関連を扱うクラス
* @package Persistence
*/
class TagmemoRelationHandler extends XoopsObjectHandler{
/**
* タグをキーとした関連したメモのリスト
* @var array
* @access public
*/
	var $tag2memo = array();
/**
* タグをキーとした関連したメモのリスト
* @var array
* @access public
*/
	var $memo2tag = array();
/**
* テーブル名
* @var string
* @access public
*/
	var $tablename = "";
//タグとメモを関連付ける
/**
* コンストラクタ
* @param mixed XoopsDBHandler
*/
	function TagmemoRelationHandler($db){
		$this->tablename = $db->prefix("tagmemo_rel");
		$this->db =$db;
	}
	function &getPopularTag($count=10){
		$ret = array();
		$sql = sprintf("select tag_id, count(tagmemo_id) as f_count from %s group by tag_id order by f_count DESC ,tag_id ASC limit %u",$this->tablename,$count);
		$result =& $this->db->query($sql);
		if (!$result) {
			return $ret;
		}
		while ($myrow = $this->db->fetchArray($result)){
			$wk_tag_id = intval($myrow['tag_id']);
			$wk_count = intval($myrow['f_count']);
			$wk_arr = array();
			$wk_arr["tag_id"] = $wk_tag_id;
			$wk_arr["count"] = $wk_count;
			$ret[] = $wk_arr;
			unset($wk_arr);
		}
		return $ret;
	}
/**
* DBから　メモとタグの関連を読み込んでこのオブジェクトにセットする。 
*
* @param integer メモのID >0
* @param integer タグのID >0
*/
	function setRelation($tagmemo_id, $tag_id){
		// @todo SQL サニタライズ
		$sql = sprintf("INSERT IGNORE INTO %s (tagmemo_id, tag_id)VALUES(%u,%u)",$this->tablename,intval($tagmemo_id),intval($tag_id));
//		echo "RelationHandler.setRelation:SQL".$sql."<br>\n";
		$this->db->queryF($sql);
	}
	
/**
* メモに関連するタグを全て削除する
*
* @param integer メモのID >0
*/
	function removeRelation($tagmemo_id){
		$sql = sprintf("DELETE FROM %s WHERE tagmemo_id=%u ",$this->tablename,intval($tagmemo_id));
//		echo "RelationHandler.setRelation:SQL".$sql."<br>\n";
		$this->db->query($sql);
	}
/** 
* メモとタグの関連をDBから読み込む
* @param mixed CriteriaObject 検索条件
*/
	function readRelation($criteria=null){
		$sql = sprintf("select tag_id, tagmemo_id from %s ",$this->tablename);
		// @todo impliment cirteria
		if ($criteria){
			$where = $criteria->renderWhere();
			$sql = $sql . $where ;
		}
		$result =& $this->db->query($sql);
		if (!$result) {
			return $ret;
		}
		while ($myrow = $this->db->fetchArray($result)){
			$wk_tag_id = intval($myrow['tag_id']);
			$wk_memo_id = intval($myrow['tagmemo_id']);
			$this->addRelation($wk_memo_id, $wk_tag_id);
		}
	}
/**
* このオブジェクトのフィールドの$tag2memoと$memo2tagにデータをセットする
* @see $tag2memo
* @see $memo2tag
* @param integer メモのID >0
* @param integer タグのID >0
*/
	function addRelation($tagmemo_id, $tag_id){
		if($tagmemo_id !=0 & $tag_id != 0){
			if(!isset($this->tag2memo[$tag_id])){
				$this->tag2memo[$tag_id] = array();
			}
			if(!in_array($tagmemo_id, $this->tag2memo[$tag_id])){
				$this->tag2memo[$tag_id][]=$tagmemo_id;
			}
			if(!isset($this->memo2tag[$tagmemo_id])){
				$this->memo2tag[$tagmemo_id] = array();
			}

			if(!in_array($tag_id, $this->memo2tag[$tagmemo_id])){
				$this->memo2tag[$tagmemo_id][]=$tag_id;
			}
		}
	}
}
?>