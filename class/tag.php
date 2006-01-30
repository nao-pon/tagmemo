<?php
/**
* @package Persistence
*/

/**
* XoopsTableObject継承
*/
include_once dirname(__FILE__).'/xoopstableobject.php';


/**
* タグのデータオブジェクト
* @package Persistence
* @author twodash <twodash@twodash.net>
*/
class TagmemoTag extends XoopsTableObject
{
/**
* コンストラクタ
*/

	function TagmemoTag()
	{
		$this->XoopsObject();
		$this->initVar('tag_id', XOBJ_DTYPE_INT, null, true);
		$this->initVar('tag', XOBJ_DTYPE_TXTBOX, null, true, 120);

		//プライマリーキーの定義
		$this->setKeyFields(array('tag_id'));

		//AUTO_INCREMENT属性のフィールド定義
		// - 一つのテーブル内には、AUTO_INCREMENT属性を持つフィールドは
		//   一つしかない前提です。
		$this->setAutoIncrementField('tag_id');

	} 
}

/**
/**
* タグのオブジェクトハンドラ
* @package Persistence
* @author twodash <twodash@twodash.net>
*/
class TagmemoTagHandler extends XoopsTableObjectHandler
{
/**
* コンストラクタ
*/
    function TagmemoTagHandler(&$db)
    {
    ////////////////////////////////////////
    // 各クラス共通部分(書換不要)
    ////////////////////////////////////////

        //親クラスのコンストラクタ呼出
        $this->XoopsTableObjectHandler($db);
        
    ////////////////////////////////////////
    // 派生クラス固有の定義部分
    ////////////////////////////////////////

        //ハンドラの対象テーブル名定義
        $this->tableName = $this->db->prefix('tagmemo_tag');

        //関連クラス名称を小文字で定義
        // - 標準のネーミングに準拠している場合には設定不要
      $this->objectClassName = 'tagmemotag';
    }
/**
* 拡張版全てのタグを取得
* @access public
* @return array タグIDをキーとしてデータにタグを持つ配列
*/
	function &getAllTagsEx(){
		$wk_tags = $this->getTags(0,'tag ASC');
		//echo $this->_tag_handler->getlastsql();
		$count = count($wk_tags);
		$count = intval(ceil($count/10));
		$wk_tags_pop = $this->getTags(0,'f_count DESC',1,$count);
		$wk_tag_pop = array_shift($wk_tags_pop);
		$wk_pop = intval($wk_tag_pop["count"]);
		$wk_tags_new = $this->getTags(0,'timestamp DESC',1,$count);
		$wk_tag_new = array_shift($wk_tags_new);
		$wk_new = intval($wk_tag_new["timestamp"]);
		$ret = array();
		foreach($wk_tags as $wk_tag){
			$wk_arr_tagdata = array(); 
			$wk_arr_tagdata["tag"] = $wk_tag["tag"];
			$wk_arr_tagdata["tag_id"] = $wk_tag["tag_id"];
			$wk_arr_tagdata["populer"] = (intval($wk_tag["count"]) >= $wk_pop)?'populer':'normal';
			$wk_arr_tagdata["latest"] = (intval($wk_tag["timestamp"]) >= $wk_new)?'latest':'normal';
			$ret[] = $wk_arr_tagdata;
			unset($wk_arr_tagdata);
		}
		return $ret;
	}

/**
* 人気タグを取得
* @access public
* @param integer 取得数
* @return array 順位をキーとしてデータにタグIDとタグを持つ配列
*/
	function getPopularTag($count = 10,$start = 0){
		$wk_poptags = $this->getTags(0,'f_count DESC',$count,$start);
		//echo $this->_tag_handler->getlastsql();
		$ret = array();
		foreach($wk_poptags as $wk_tag){
			$wk_arr_tagdata = array(); 
			$wk_arr_tagdata["tag"] = $wk_tag["tag"];
			$wk_arr_tagdata["tag_id"] = $wk_tag["tag_id"];
			$wk_arr_tagdata["count"] = $wk_tag["count"];
			$ret[] = $wk_arr_tagdata;
			unset($wk_arr_tagdata);
		}
		return $ret;
	}
/**
* 最新タグを取得
* @access public
* @param integer 取得数
* @return array 順位をキーとしてデータにタグIDとタグを持つ配列
*/
	function getResentTag($count = 10,$start = 0){
		$wk_poptags = $this->getTags(0,'timestamp DESC',$count,$start);
//		echo $this->_tag_handler->getlastsql();
		$ret = array();
		foreach($wk_poptags as $wk_tag){
			$wk_arr_tagdata = array(); 
			$wk_arr_tagdata["tag"] = $wk_tag["tag"];
			$wk_arr_tagdata["tag_id"] = $wk_tag["tag_id"];
			$wk_arr_tagdata["date"] = formatTimestamp($wk_tag["timestamp"], "mysql");
			$ret[] = $wk_arr_tagdata;
			unset($wk_arr_tagdata);
		}
		return $ret;
	}
	
	/**
	*タグ文字列からIDを返す
	*@return int
	*@param string tagvar by ref
	*/
	function getTagId(&$tag_var){
		$ret = 0;
		return $ret;
	}
    /**
    * タグを付加情報とともに取得
    *@param string order by句
    *@param int limit
    *@param int start
    *@param array メモのIDの配列
    *@return array タグIDをキーに持つ タグIDとタグと最新日付の使用数の配列
    */
		function &getTags($memo_ids=false, $arg_order="", $limit=0, $start=0)
		{
		$criteria = null;
			$ret = array();
			$whereStr = '';
			$orderStr = ' ORDER BY tag_id DESC';
			if(strlen($arg_order) > 0){
				$orderStr = ' ORDER BY '.$arg_order.', tag_id DESC';
			}
			$groupStr = ' GROUP BY tag_id,tag';
			$criteria = new CriteriaCompo;
			if(is_array($memo_ids)){
				foreach($memo_ids as $wk_memo_id){
					if($wk_memo_id > 0){
						$wk_memoid_criteria = new Criteria('tagmemo_id', $wk_memo_id, '=', 'rel');
					}
					$criteria->add($wk_memoid_criteria,'OR');
					unset($wk_memoid_criteria);
				}
			}else{
				$wk_memo_id = intval($memo_ids);
				if($wk_memo_id > 0){
					$wk_criteria = new Criteria('tagmemo_id', $wk_memo_id, '=', 'rel');
					$criteria->add($wk_criteria,'AND');
				}
			}
# 			$wk_base_criteria = new Criteria('tagmemo_id','NULL', 'NOT IS ', 'main');
# 			$criteria->add($wk_base_criteria,'AND')
			$sql = "SELECT main.tag_id AS tag_id, tag, MAX(memo.timestamp) AS timestamp, COUNT(main.tag_id) AS f_count ";
			$sql .= sprintf("FROM %s AS main LEFT OUTER JOIN %s AS rel ON main.tag_id = rel.tag_id LEFT OUTER JOIN %s AS memo ON rel.tagmemo_id = memo.tagmemo_id",
			$this->tableName,
			$this->db->prefix('tagmemo_rel'),
			$this->db->prefix('tagmemo'));
			$sql .= " where rel.tagmemo_id is not null";
			if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
				$whereStr = $criteria->render();
				$whereStr =  empty($whereStr) ? '' : " AND $whereStr";
				$sql .= ' ' . $whereStr;
			}
 empty($cond) ? '' : "WHERE $cond";
			$sql .= ' '.$groupStr.' HAVING COUNT(main.tag_id) > 0 '.$orderStr;
			/* if (isset($criteria) && (is_subclass_of($criteria, 'criteriaelement')||get_class($criteria)=='criteriaelement')) {
				if ((is_array($criteria->getSort()) && count($criteria->getSort()) > 0)) {
					$orderStr = 'ORDER BY ';
					$orderDelim = "";
					foreach ($criteria->getSort() as $sortVar) {
						$orderStr .= $orderDelim . $sortVar.' '.$criteria->getOrder();
						$orderDelim = ",";
					}
					$sql .= ' '.$orderStr;
				} elseif ($criteria->getSort() != '') {
					$orderStr = 'ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
					$sql .= ' '.$orderStr;
				}
# 				$limit = $criteria->getLimit();
# 				$start = $criteria->getStart();
			} */
				$result =& $this->query($sql, false ,$limit, $start);
				if (!$result) {
					return $ret;
				}
				while ($myrow = $this->db->fetchArray($result)) {
					$record = array();
					$wk_id = $myrow['tag_id'];
					$record['tag_id'] = $wk_id;
					$record['tag'] = $myrow['tag'];
					$record['timestamp'] = $myrow['timestamp'];
//					$record['timestamp'] = formatTimestamp($myrow['timestamp'], "mysql");
					$record['count'] = $myrow['f_count'];
					$ret["$wk_id"]=$record;
					unset($record);
				}
			return $ret;
		}
		

    /**
    * get count of all tags
    *@return int count of all tags
    */
	
	function getCount(){
		$sql = "select count(*) from ".$this->db->prefix('tagmemo_rel');		
		$result =& $this->query($sql);
		list($count) = $this->db->fetchRow($result);
		return $count;		
	}
		
    /**
    * get array for tag cloud.
    *@return array array for tag cloud, containing tags with css 'class' selector.
    */
		
	function getTagArrayForCloud(){
		$wk_tags = $this->getTags(0,'tag ASC');

		$count = $this->getCount();

		$most_popular_count = ceil($count/25);
		$very_popular_count = ceil($count/10);
		$popular_count      = ceil($count/5);
		$fresh_count        = ceil($count/25);

		$wk_tags_fresh = $this->getTags(0, 'timestamp DESC', 1, $fresh_count);
		$wk_tag_fresh = array_shift($wk_tags_fresh);
		$wk_fresh_timestamp = intval($wk_tag_fresh["timestamp"]);

		$ret = array();
		$current_count = 0;
		
		foreach($wk_tags as $wk_tag){
			
			$wk_arr_tagdata = array(); 
			$current_count += intval($wk_tag["count"]);
			
			$wk_arr_tagdata["tag"] = $wk_tag["tag"];
			$wk_arr_tagdata["tag_id"] = $wk_tag["tag_id"];
			
			// popular
			switch(TRUE){
				case ($current_count > $popular_count):
					$wk_arr_tagdata["popular"] = 'populartags';
				break;
				
				case ($current_count > $very_popular_count):
					$wk_arr_tagdata["popular"] = 'verypopulartags';
				break;
				
				case ($current_count > $most_popular_count):
					$wk_arr_tagdata["popular"] = 'mostpopulartags';
				break;
				
				default:
					$wk_arr_tagdata["popular"] = 'nomaltags';
				break;
			}
			//fresh
			if(intval($wk_tag["timestamp"]) >= $wk_fresh_timestamp){
				$wk_arr_tagdata["fresh"] = 'fleshtags';
			} else {
				$wk_arr_tagdata["fresh"] = 'oldtags';
			}

			$ret[] = $wk_arr_tagdata;
			unset($wk_arr_tagdata);
		}
		return $ret;
		
	}



}
?>