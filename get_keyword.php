<?php
define("SOURCE_ENCODING","EUC-JP");
$url = (!empty($_GET['q']))? $_GET['q'] : "";
$result = "var tmp = new Array();";
if ($url)
{
	include_once("include/hyp_common_func.php");
	include_once("include/hyp_kakasi.php");

	$d = new Hyp_HTTP_Request();

	$d->url = $url;
	$d->method = 'GET';
	$d->get();

	if ($d->rc === 200)
	{
		$data = $d->data;
		
		// 文字コード判定 変換
		$src_enc = HypCommonFunc::get_encoding_by_meta($data);
		$data = preg_replace("#<script(.+?)/script>#is","",$data);
		$data = preg_replace("#<style(.+?)/style>#is","",$data);
		
		// タイトルタグ
		$title = "";
		if (preg_match("#<title>(.+)</title>#is",$data,$match))
		{
			$title = $match[1];
			$title = str_replace(array("&nbsp;","&lt;","&gt;","&amp;"),array(" ","<",">","&"),$title);
			$title = str_replace("\0","",mb_convert_encoding($title, SOURCE_ENCODING, $src_enc));
			
			$k = new Hyp_KAKASHI();
			$k->get_keyword($title, 3, 3, 1);
			
			if ($title) $title .= " ";
		}
		
		// HTML全体
		$data = strip_tags($data);
		$data = str_replace(array("&nbsp;","&lt;","&gt;","&amp;"),array(" ","<",">","&"),$data);
		$data = str_replace("\0","",mb_convert_encoding($data, SOURCE_ENCODING, $src_enc));
	
		$k = new Hyp_KAKASHI();
		$k->get_keyword($data);
		
		$data = mb_convert_encoding($title.$data, "UTF-8", "EUC-JP");
		$result = 'var tmp = new Array("'.str_replace(array('"'," "),array('\"','","'),$data).'");';
	}
}
header ("Content-Type: text/html; charset=UTF-8");
echo $result;
?>