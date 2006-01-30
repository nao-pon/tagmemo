<?php
include_once XOOPS_ROOT_PATH."/class/xoopsobject.php";

if (!defined('XOBJ_DTYPE_FLOAT')) define('XOBJ_DTYPE_FLOAT', 101);
if (!defined('XOBJ_VCLASS_TFIELD')) define('XOBJ_VCLASS_TFIELD', 1);
if (!defined('XOBJ_VCLASS_ATTRIB')) define('XOBJ_VCLASS_ATTRIB', 2);
if (!defined('XOBJ_VCLASS_EXTRA')) define('XOBJ_VCLASS_EXTRA', 3);
/**
 * 汎用テーブル操作XoopsObject
 * 
 * @copyright copyright (c) 2000-2003 Kowa.ORG
 * @author Nobuki Kowa <Nobuki@Kowa.ORG> 
 * @package XoopsTableObject
 */
if( ! class_exists( 'XoopsTableObject' ) ) {
	class XoopsTableObject  extends XoopsObject
	{
		var $_extra_vars = array();
		var $_keys;
		var $_autoIncrement;
		var $_formElements;
		var $_listTableElements;
		var $_handler;
		
	    function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '')
	    {
	    	parent::initVar($key, $data_type, $value, $required, $maxlength, $options);
	    	$this->vars[$key]['var_class'] = XOBJ_VCLASS_TFIELD;
	    }

		function setAttribute($key, $value)
		{
	        $this->vars[$key] = array('value' => $value, 'required' => false, 'data_type' => XOBJ_DTYPE_OTHER, 'maxlength' => null, 'changed' => false, 'options' => '');
			$this->vars[$key]['var_class'] = XOBJ_VCLASS_ATTRIB;
		}

		function XoopsTableObject()
		{
			//親クラスのコンストラクタ呼出
			$this->XoopsObject();
			$this->_handler = null;
		}

		function setKeyFields($keys)
		{
			$this->_keys = $keys;
		}
		
		function getKeyFields()
		{
			return $this->_keys;
		}
		
		function isKey($field)
		{
			return in_array($field,$this->_keys);
		}

		function cacheKey()
		{
			$recordKeys = $this->getKeyFields();
			$recordVars = $this->getVars();
			$cacheKey = array();
			foreach ($this->getKeyFields() as $k => $v) {
				$cacheKey[$v] = $this->getVar($v);
			}
			return(serialize($cacheKey));
		}
		//AUTO_INCREMENT属性のフィールドはテーブルに一つしかない前提
		function setAutoIncrementField($fieldName)
		{
			$this->_autoIncrement = $fieldName;
		}
		
		function &getAutoIncrementField()
		{
			return $this->_autoIncrement;
		}

		function isAutoIncrement($fieldName)
		{
			return ($fieldName == $this->_autoIncrement);
		}
		
		function resetChenged()
		{
			foreach($this->vars as $k=>$v) {
				$this->vars[$k]['changed'] = false;
			}
		}
		
		function assignEditFormElement($name,$class,$params)
		{
			include_once XOOPS_ROOT_PATH.'/class/xoopsform/formelement.php';
			include_once XOOPS_ROOT_PATH.'/class/xoopsform/form'. strtolower($class) .'.php';
			$className = "XoopsForm". $class;
			$callstr = '$this->_formElements["'.$name.'"] = new XoopsForm'.$class.'(';
			$delim = '';
			for ($i=0;$i<count($params);$i++) {
				if (gettype($params[$i]) == "string") {
					$callstr .= $delim. '"'. $params[$i].'"';
				} else {
					$callstr .= $delim. $params[$i];
				}
				$delim = ', ';
			}
			$callstr .= ');';
	//		echo "$callstr<br />";
			eval($callstr);
		}
		
		function renderEditForm($caption,$name,$action)
		{
			include_once XOOPS_ROOT_PATH.'/class/xoopsform/form.php';
			include_once XOOPS_ROOT_PATH.'/class/xoopsform/themeform.php';
			include_once XOOPS_ROOT_PATH.'/class/xoopsform/formhidden.php';
			include_once XOOPS_ROOT_PATH.'/class/xoopsform/formbutton.php';
			
			$formEdit =& new XoopsThemeForm($caption,$name,$action);
			foreach ($this->_formElements as $key=>$formElement) {
				if (!$this->isNew()) {
					if ($this->vars[$key]['changed']) {
					        $formElement->setValue($this->getVar($key, 'e'));
					} else {
					        $formElement->setValue($this->getVar($key, 'e'));
					}
					$formElement->setValue($this->getVar($key,'e'));
				}
				$formEdit->addElement($formElement,$this->vars[$key]['required']);
//		echo "$key - " .get_class($formElement) ."<br/>";
				unset($formElement);
			}
			
			if ($this->isNew()) {
				$formEdit->addElement(new XoopsFormHidden('op','insert'));
			} else {
				$formEdit->addElement(new XoopsFormHidden('op','save'));
			}
			$formEdit->addElement(new XoopsFormButton('', 'submit', 'OK', 'submit'));

			$str = $formEdit->render();
			unset($formEdit);
			return $str;
		}

		function assignListTableElement($name,$type, $caption) {
			$_listTableElements[$name]['type'] = $type;
			$_listTableElements[$name]['caption'] = $caption;
		}
		
	    function assignVar($key, $value)
	    {
	        if (isset($value) && isset($this->vars[$key])) {
	            $this->vars[$key]['value'] =& $value;
	        } else {
	            $this->setExtraVar($key, $value);
	        }
	    }

		function &getExtraVar($key)
		{
			return $this->_extra_vars[$key];
		}
		
		function setExtraVar($key, $value)
		{
			$this->_extra_vars[$key] =& $value;
		}

	    /**
	    * assign a value to a variable
	    * 
	    * @access public
	    * @param string $key name of the variable to assign
	    * @param mixed $value value to assign
	    * @param bool $not_gpc
	    */
	    function setVar($key, $value, $not_gpc = false)
	    {
	        if (!empty($key) && isset($this->vars[$key])) {
	            $this->vars[$key]['value'] =& $value;
	            $this->vars[$key]['not_gpc'] = $not_gpc;
	            $this->vars[$key]['changed'] = true;
	            $this->setDirty();
	        }
	    }

	    /**
	    * returns a specific variable for the object in a proper format
	    * 
	    * @access public
	    * @param string $key key of the object's variable to be returned
	    * @param string $format format to use for the output
	    * @return mixed formatted value of the variable
	    */
	    function &getVar($key, $format = 's')
	    {
			$ret =& parent::getVar($key, $format);
			if ($this->vars[$key]['data_type'] == XOBJ_DTYPE_TXTAREA && ($format=='e' || $format=='edit')) {
				$ret = preg_replace("/&amp;(#[0-9]+;)/i", '&$1', $ret);
			}
			return $ret;
		}

		function cleanVars() {
			$iret =parent::cleanVars();
			foreach ($this->vars as $k => $v) {
				$cleanv = $v['value'];
				if (!$v['changed']) {
				} else {
					$cleanv = is_string($cleanv) ? trim($cleanv) : $cleanv;
					switch ($v['data_type']) {
					case XOBJ_DTYPE_FLOAT:
						$cleanv = (float)($cleanv);
						break;
					default:
						break;
					}
					//個別の変数チェックがあれば実行;
					$checkMethod = 'checkVar_'.$k;
					if(method_exists($this, $checkMethod)) {
						$this->$checkMethod($cleanv);
					}
				}
				$this->cleanVars[$k] =& $cleanv;
				unset($cleanv);
			}
			if (count($this->_errors) > 0) {
				return false;
			}
			$this->unsetDirty();
			return true;
		}
			
		function &getVarArray($type='s') {
			$varArray=array();
	        foreach ($this->vars as $k => $v) {
				$varArray[$k]=$this->getVar($k,$type);
			}
			return $varArray;
		}
		//Following two functions are only for WordPress Module.
		function &exportWpObject() {
			$wp_object = (object) null;
	        foreach ($this->vars as $k => $v) {
	        	$wp_object->$k = $v['value'];
			}
	        foreach ($this->_extra_vars as $k => $v) {
	        	$wp_object->$k = $v;
			}
			return $wp_object;
		}
		function importWpObject(&$wp_object) {
	        foreach ($this->vars as $k => $v) {
	        	$this->setVar($k, $wp_object->$k);
			}
		}
	}

	class XoopsTableObjectHandler  extends XoopsObjectHandler
	{
		var $tableName;
		var $useFullCache;
		var $cacheLimit;
		var $_entityClassName;
		var $_errors;
		var $_fullCached;
		var $_sql;
		
		function XoopsTableObjectHandler($db)
		{
			$this->_entityClassName = preg_replace("/handler$/i","", get_class($this));
			$this->XoopsObjectHandler($db);
			$this->_errors = array();
			$this->useFullCache = true;
			$this->cacheLimit = 0;
			$this->_fullCached = false;
		}
		
		function getErrors($html=true, $clear=true)
		{
			$error_str = "";
			$delim = $html ? "<br />\n" : "\n";
			if (count($this->_errors)) {
				$error_str = implode($delim, $this->_errors);
			}
			if ($clear) {
				$this->_errors = array();
			}
			return $error_str;
		}
		function setError($error_str)
		{
			$this->_errors[] = $error_str;
		}
		/**
		 * レコードオブジェクトの生成
		 * 
		 * @param	boolean $isNew 新規レコード設定フラグ
		 * 
		 * @return	object  {@link XoopsTableObject}
		 */
		function &create($isNew = true)
		{
			$record = new $this->_entityClassName;
			if ($isNew) {
				$record->setNew();
			}
			$record->_handler =& $this;
			return $record;
		}

		/**
		 * レコードの取得(プライマリーキーによる一意検索）
		 * 
		 * @param	mixed $key 検索キー
		 * 
		 * @return	object  {@link XoopsTableObject}, FALSE on fail
		 */
		function &get($keys)
		{
			$record =& $this->create(false);
			$recordKeys = $record->getKeyFields();
			$recordVars = $record->getVars();
			if (gettype($keys) != 'array') {
				if (count($recordKeys) == 1) {
					$keys = array($recordKeys[0] => $keys);
				} else {
					return false;
				}
			}
			$whereStr = "";
			$whereAnd = "";
			$cacheKey = array();
			foreach ($record->getKeyFields() as $k => $v) {
				if (array_key_exists($v, $keys)) {
					$whereStr .= $whereAnd . "`$v` = ";
					if (($recordVars[$v]['data_type'] == XOBJ_DTYPE_INT) || ($recordVars[$v]['data_type'] == XOBJ_DTYPE_FLOAT)) {
						$whereStr .= $keys[$v];
					} else {
						$whereStr .= $this->db->quoteString($keys[$v]);
					}
					$whereAnd = " AND ";
					$cacheKey[$v] = $keys[$v];
				} else {
					return false;
				}
			}
			$cacheKey = serialize($cacheKey);
			if ($GLOBALS['_xoopsTableCache']->exists($this->tableName, $cacheKey)) {
				$record->assignVars($GLOBALS['_xoopsTableCache']->get($this->tableName, $cacheKey));
				return $record;
			}
			$sql = sprintf("SELECT * FROM %s WHERE %s",$this->tableName, $whereStr);

			if ( !$result =& $this->query($sql) ) {
				return false;
			}
			$numrows = $this->db->getRowsNum($result);
//		echo $numrows."<br/>";
			if ( $numrows == 1 ) {
				$row = $this->db->fetchArray($result);
				$record->assignVars($row);
				$GLOBALS['_xoopsTableCache']->set($this->tableName, $cacheKey, $row, $this->cacheLimit);
				return $record;
			}
			unset($record);
			return false;
		}
	    /**
	     * レコードの保存
	     * 
	     * @param	object	&$record	{@link XoopsTableObject} object
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
	     */
		function insert(&$record,$force=false,$updateOnlyChanged=false)
		{
			if ( get_class($record) != $this->_entityClassName ) {
				return false;
			}
			if ( !$record->isDirty() ) {
				return true;
			}
			if (!$record->cleanVars()) {
				$this->_errors += $record->getErrors();
				return false;
			}
			$vars = $record->getVars();
			$cacheRow = array();
			if ($record->isNew()) {
				$fieldList = "(";
				$valueList = "(";
				$delim = "";
				foreach ($record->cleanVars as $k => $v) {
					if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
						continue;
					}
					$fieldList .= $delim ."`$k`";
					if ($record->isAutoIncrement($k)) {
						$v = $this->getAutoIncrementValue();
					}
					if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
						$value = preg_replace('/^__MySqlFunc__/', '', $v);
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
						if (!is_null($v)) {
							$v = intval($v);
							$v = ($v) ? $v : 0;
							$valueList .= $delim . $v;
						} else {
							$valueList .= $delim . 'null';
						}
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
						if (!is_null($v)) {
							$v = (float)($v);
							$v = ($v) ? $v : 0;
							$valueList .= $delim . $v;
						} else {
							$valueList .= $delim . 'null';
						}
					} else {
						$valueList .= $delim . $this->db->quoteString($v);
					}
					$cacheRow[$k] = $v;
					$delim = ", ";
				}
				$fieldList .= ")";
				$valueList .= ")";
				$sql = sprintf("INSERT INTO %s %s VALUES %s", $this->tableName,$fieldList,$valueList);
			} else {
				$setList = "";
				$setDelim = "";
				$whereList = "";
				$whereDelim = "";
				foreach ($record->cleanVars as $k => $v) {
					if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
						continue;
					}
					if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
						$value = preg_replace('/^__MySqlFunc__/', '', $v);
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
						$v = intval($v);
						$value = ($v) ? $v : 0;
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
						$v = (float)($v);
						$value = ($v) ? $v : 0;
					} else {
						$value = $this->db->quoteString($v);
					}

					if ($record->isKey($k)) {
						$whereList .= $whereDelim . "`$k` = ". $value;
						$whereDelim = " AND ";
					} else {
						if ($updateOnlyChanged && !$vars[$k]['changed']) {
							continue;
						}
						$setList .= $setDelim . "`$k` = ". $value . " ";
						$setDelim = ", ";
					}
					$cacheRow[$k] = $v;
				}
				if (!$setList) {
					$record->resetChenged();
					return true;
				}
				$sql = sprintf("UPDATE %s SET %s WHERE %s", $this->tableName, $setList, $whereList);
			}
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
			if ($record->isNew()) {
				$idField=$record->getAutoIncrementField();
				$idValue=$this->db->getInsertId();
				$record->assignVar($idField,$idValue);
				$cacheRow[$idField] = $idValue;
			}
			if (!$updateOnlyChanged) {
				$GLOBALS['_xoopsTableCache']->set($this->tableName, $record->cacheKey() ,$cacheRow, $this->cacheLimit);
			} else {
				$GLOBALS['_xoopsTableCache']->reset($this->tableName, $record->cacheKey());
				$this->_fullCached = false;
			}
			$record->resetChenged();
			return true;
		}

	    function updateByField(&$record, $fieldName, $fieldValue)
	    {
	        $record->setVar($fieldName, $fieldValue);
	        return $this->insert($record, true, true);
	    }

		/**
		 * レコードの削除
		 * 
	     * @param	object  &$record  {@link XoopsTableObject} object
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
		 */
		function delete(&$record,$force=false)
		{
			if ( get_class($record) != $this->_entityClassName ) {
				return false;
			}
			if (!$record->cleanVars()) {
				$this->_errors[] = $this->db->error();
				return false;
			}
			$vars = $record->getVars();
			$whereList = "";
			$whereDelim = "";
			foreach ($record->cleanVars as $k => $v) {
				if ($record->isKey($k)) {
					if (($vars[$k]['data_type'] == XOBJ_DTYPE_INT)||($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT)) {
						$value = $v;
					} else {
						$value = $this->db->quoteString($v);
					}
					$whereList .= $whereDelim . "`$k` = ". $value;
					$whereDelim = " AND ";
				}
			}
			$sql = sprintf("DELETE FROM %s WHERE %s", $this->tableName, $whereList);
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
			$GLOBALS['_xoopsTableCache']->reset($this->tableName, $record->cacheKey());
			return true;
		}

		/**
		 * テーブルの条件検索による複数レコード取得
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
		 * @param	bool $id_as_key		プライマリーキーを、戻り配列のキーにする場合はtrue
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
		 // add argument $having by twodash at 2006/01/15
		function &getObjects($criteria = null, $id_as_key = false, $fieldlist="", $distinct = false, $joindef = false, $having="")
		{
			$ret = array();
			$limit = $start = 0;
			$whereStr = '';
			$orderStr = '';
			if ($distinct) {
				$distinct = "DISTINCT ";
			} else {
				$distinct = "";
			}
			if ($fieldlist) {
			//2006/01/13 towdash set default alias as main
				$sql = 'SELECT '.$distinct.$fieldlist.' FROM '.$this->tableName . " AS main";
			} else {
			//2006/01/13 towdash set default alias as main
				$sql = 'SELECT '.$distinct.'* FROM '.$this->tableName ." AS main";;
			}
			if ($joindef) {
				$sql .= $joindef->render($this->tableName);
			}
			if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
				$whereStr = $criteria->renderWhere();
				$sql .= ' '.$whereStr;
			}
			if (isset($criteria) && (is_subclass_of($criteria, 'criteriaelement')||get_class($criteria)=='criteriaelement')) {
				if ($criteria->getGroupby() != ' GROUP BY ') {
					$sql .= ' '.$criteria->getGroupby();
					if($having != ""){
					$sql .= ' HAVING '.$having;
					}
				}
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
				$limit = $criteria->getLimit();
				$start = $criteria->getStart();
			}
			//今のところは、非常に限定された条件でしかキャッシュを使えない
			if (($this->useFullCache) && ($this->_fullCached) && (!$whereStr) && (!$orderStr) && ($limit==0) && ($start==0) && (!$fieldlist)) {
				$records = array();
				/* foreach ($GLOBALS['_xoopsTableCache']->getFull($this->tableName) as $myrow) {
					$record =& $this->create(false);
					$record->assignVars($myrow);
					if (!$id_as_key) {
						$records[] =& $record;
					} else {
						$ids = $record->getKeyFields();
						$r =& $records;
						$count_ids = count($ids);
						for ($i=0; $i<$count_ids; $i++) {
							if ($i == $count_ids-1) {
								$r[$myrow[$ids[$i]]] =& $record;
							} else {
								$r[$myrow[$ids[$i]]] = array();
								$r =& $r[$myrow[$ids[$i]]];
							}
						}
					}
					unset($record);
				} */
				
			} else {
				$result =& $this->query($sql, false ,$limit, $start);
				if (!$result) {
					return $ret;
				}
				if ((!$whereStr) && ($limit==0) && ($start ==0) && ($this->useFullCache) && ($this->cacheLimit==0)) {
					$this->_fullCached = true;
				}
				$records = array();
				while ($myrow = $this->db->fetchArray($result)) {
					$record =& $this->create(false);
					$record->assignVars($myrow);
					if (!$id_as_key) {
						$records[] =& $record;
					} else {
						$ids = $record->getKeyFields();
						$r =& $records;
						$count_ids = count($ids);
						for ($i=0; $i<$count_ids; $i++) {
							if ($i == $count_ids-1) {
								$r[$myrow[$ids[$i]]] =& $record;
							} else {
								if (!isset($r[$myrow[$ids[$i]]])) {
									$r[$myrow[$ids[$i]]] = array();
								}
								$r =& $r[$myrow[$ids[$i]]];
							}
						}
					}
					if (!$fieldlist) {
						$GLOBALS['_xoopsTableCache']->set($this->tableName, $record->cacheKey(), $myrow, $this->cacheLimit);
					}
					unset($record);
				}
			}
			return $records;
		}

		/**
		 * テーブルの条件検索による対象レコード件数
		 * 
		 * @param	object	$criteria 		{@link XoopsTableObject} 検索条件
		 * 
		 * @return	integer					検索結果レコードの件数
		 */
	    function getCount($criteria = null)
	    {
	        $sql = 'SELECT COUNT(*) FROM '.$this->tableName;
	        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
	            $sql .= ' '.$criteria->renderWhere();
	        }
	        $result =& $this->query($sql);
	        if (!$result) {
	            return 0;
	        }
	        list($count) = $this->db->fetchRow($result);
	        return $count;
	    }
	    

		/**
		 * テーブルの条件検索による複数レコード一括更新(対象フィールドは一つのみ)
		 * 
		 * @param	string	$fieldname 	更新フィールド名
		 * @param	mixed	$fieldvalue	更新値
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
	    function updateAll($fieldname, $fieldvalue, $criteria = null, $force=false)
	    {
	    	$record = $this->create();
	    	if ($record->vars[$fieldname]['data_type'] == XOBJ_DTYPE_INT) {
				$fieldvalue = intval($fieldvalue);
				$fieldvalue = ($fieldvalue) ? $fieldvalue : 0;
			} elseif ($record->vars[$fieldname]['data_type'] == XOBJ_DTYPE_FLOAT) {
				$fieldvalue = (float)($fieldvalue);
				$fieldvalue = ($fieldvalue) ? $fieldvalue : 0;
			} else {
				$fieldvalue = $this->db->quoteString($fieldvalue);
			}
	        $set_clause = $fieldname.' = '.$fieldvalue;
	        $sql = 'UPDATE '.$this->tableName.' SET '.$set_clause;
	        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
	            $sql .= ' '.$criteria->renderWhere();
	        }
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
	        //キャッシュのクリア(更新されたレコード再取得の方がコストが高そうなので)
	        $GLOBALS['_xoopsTableCache']->clear($this->tableName);
			$this->_fullCached = false;
	        return true;
	    }

		/**
		 * テーブルの条件検索による複数レコード削除
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
		 */
	    function deleteAll($criteria = null, $force=false)
	    {
	        $sql = 'DELETE FROM '.$this->tableName;
	        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
	            $sql .= ' '.$criteria->renderWhere();
	        }
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
	        //キャッシュのクリア(削除されたレコード再取得の方がコストが高そうなので)
	        $GLOBALS['_xoopsTableCache']->clear($this->tableName);
			$this->_fullCached = false;
	        return true;
	    }

		function getAutoIncrementValue()
		{
			return $this->db->genId(get_class($this).'_id_seq');
		}

		function &query($sql, $force=false, $limit=0, $start=0) {
			if (empty($GLOBALS['_xoopsTableQueryCount'])) {
				$GLOBALS['_xoopsTableQueryCount'] = 1;
			} else {
				$GLOBALS['_xoopsTableQueryCount']++;
			}
			if (!empty($GLOBALS['wpdb'])) {
				$GLOBALS['wpdb']->querycount++;
			}
			if ($force) {
				$result =& $this->db->queryF($sql, $limit, $start);
			} else {
				$result =& $this->db->query($sql, $limit, $start);
			}
			$this->_sql = $sql;
			$this->_start = $start;
			$this->_limit = $limit;

			if (!$result) {
				$this->_errors[] = $this->db->error();
				return false;
			}
			return $result;
		}
		
		function getLastSQL()
		{
			return $this->_sql;
		}
	}
	
	class XoopsJoinCriteria
	{
		var $_table_name;
		var $_main_field;
		var $_sub_field;
		var $_join_type;
		var $_next_join;
		//add: 2006/01/15 towdash
		var $_main_prefix;
		var $_sub_prefix;
		 
		//add argument $main_prefix=""  and $sub_prifix="": 2006/01/15 towdash
		function XoopsJoinCriteria($table_name, $main_field, $sub_field, $join_type='LEFT', $main_prefix="" , $sub_prefix="")
		{
			$this->_table_name = $table_name;
			$this->_main_field = $main_field;
			$this->_sub_field = $sub_field;
			$this->_join_type = $join_type;
			$this->_next_join = false;
			$this->_main_prefix = $main_prefix;
			$this->_sub_prefix = $sub_prefix;
		}
		
		function cascade(&$joinCriteria) {
			$this->_next_join =& $joinCriteria;
		}
		
		function render($main_table)
		{
			//set prefix alias as : 2006/01/15 towdash
			$main_prefix = $this->_main_prefix;
			$sub_prefix = $this->_sub_prefix;
			$sub_as = " AS ";
			if($main_prefix == ""){
				$main_prefix = $main_table;
			}
			if($sub_prefix == ""){
				$sub_prefix = $this->_table_name;
				$sub_as = " ";
			}
		// set prefix alias as : 2006/01/13 towdash
			$join_str = " ".$this->_join_type." JOIN ".$this->_table_name . $sub_as . $sub_prefix." ON ".$main_prefix.".".$this->_main_field."=".$sub_prefix.".".$this->_sub_field." ";
 			if ($this->_next_join) {
				$join_str .= $this->_next_join->render($this->_table_name);
			}
			return $join_str;
		}
	}
	
	class XoopsTableCache
	{
		var $cache;
		
		function set($table, $key, $row, $limit=0) {
			$this->cache[$table][$key] = $row;
			$cache_size = count($this->cache[$table]);
			if (($limit != 0) && $cache_size >$limit) {
				array_splice($this->cache[$table],1, $cache_size-$limit);
			}
		}
		function reset($table, $key) {
			unset($this->cache[$table][$key]);
		}
		function exists($table, $key) {
			return (!empty($this->cache[$table][$key]));
		}
		function &get($table,$key) {
			return $this->cache[$table][$key];
		}
		function &getFull($table) {
			return $this->cache[$table];
		}
		function clear($table) {
			$this->cache[$table] = array();
		}
	}
	$GLOBALS['_xoopsTableCache'] = new XoopsTableCache;
}
?>