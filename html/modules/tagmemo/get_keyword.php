<?php
define("SOURCE_ENCODING","EUC-JP");
$url = (!empty($_POST['q']))? $_POST['q'] : "";
$text = (!empty($_POST['t']))? $_POST['t'] : "";

// URL中の & 対策
$url = str_replace("%26","&",$url);

$result = "var tmp = new Array();";
if ($url || $text)
{
	// mbstring のチェック
	if (!extension_loaded('mbstring'))
	{
		include_once('./include/mbstring.php');
	}
	
	include_once("include/hyp_common/hyp_common_func.php");
	include_once("include/hyp_common/hyp_kakasi.php");
	
	$body = $tags = $title = $data = "";
	
	if ($text)
	{
		$text = mb_convert_encoding($text, SOURCE_ENCODING, "UTF-8");
		
		str_format($text);
		
		// 既存タグとのマッチング
		$body = match_saved_tag($text);
				
		// 形態素解析でのマッチング
		$k = new Hyp_KAKASHI();
		$k->get_keyword($text, 15, 3, 2);
		$body .= $text;
		
		if ($body) $body = rtrim($body)." ";
	}

	if ($url)
	{
		$d = new Hyp_HTTP_Request();
	
		$d->url = $url;
		$d->method = 'GET';
		$d->ua = 'Mozilla/4.0';
		$d->get();
		
		if ($d->rc === 200)
		{
			$data = $d->data;
			
			// 文字コード判定 変換
			$src_enc = HypCommonFunc::get_encoding_by_meta($data);
			$data = str_replace("\0","",mb_convert_encoding($data, SOURCE_ENCODING, $src_enc));
			
			// 余分な部分の除去 & 整形
			$data = preg_replace("#<((?:no)?script|style|form)(.+?)/\\1>#is","",$data);
			$data = preg_replace("/&#[0-9]+;/i","",$data);
			
			// タイトルタグ
			$title = "";
			if (preg_match("#<title>(.+)</title>#is",$data,$match))
			{
				$title = $match[1];
				str_format($title);

				$k = new Hyp_KAKASHI();
				$k->get_keyword($title, 5, 3, 1);
				
				if ($title) $title .= " ";
			}
			
			// HTML全体
			$data = strip_tags($data);
			str_format($data);
					
			// 既存タグとのマッチング
			$tags = match_saved_tag($data);
			
			// 形態素解析でのマッチング
			$k = new Hyp_KAKASHI();
			$k->get_keyword($data, 15, 3, 2);
		}
	}

	$data = join(" ",array_unique(explode(" ",$body.$tags.$title.$data)));
	$data = mb_convert_encoding($data, "UTF-8", SOURCE_ENCODING);
	$result = 'var tmp = new Array("'.str_replace(array('"'," "),array('\"','","'),$data).'");';
}

function match_saved_tag(& $data)
{
	// 既存タグとのマッチング
	
	static $auto;
	
	if (!isset($auto))
	{
		$autofile = "../../uploads/tagmemo/tagmemo_autolink.dat";
		@list($auto,$dum,$forceignorepages) = @file($autofile);
		if (!$auto) $auto = "(?!)";
		$auto = explode("\t",trim($auto));
	}
	
	// TAGが多い場合は、セパレータ \t で複数パターンに分割されている
	$tags = "";
	$match_tags = array();
	foreach($auto as $pat)
	{
		$pattern = "/$pat/is";
		if (preg_match_all($pattern,$data,$match,PREG_PATTERN_ORDER))
		{
			$match_tags = array_merge($match_tags,$match[0]);
		}
	}
	if ($match_tags)
	{
		$tags = join(" ",$match_tags)." ";
	}
	return $tags;
}

function str_format(&$str)
{
	$str = str_replace(
			array("&nbsp;","&lt;","&gt;","&quot;","&#39;","&amp;","[","]","(",")"),
			array(" ",     "<",   ">",   "\"",    "'",    "&",    " "," "," "," "),
			$str);
	return $str;	
}

header ("Content-Type: text/html; charset=UTF-8");
echo $result;
?>