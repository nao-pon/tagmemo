<?php
// $Id$
// HypCommonFunc Class by nao-pon http://hypweb.net
////////////////////////////////////////////////

if( ! class_exists( 'HypCommonFunc' ) )
{

class HypCommonFunc
{
	// 1バイト文字をエンティティ化
	function str_to_entity(&$str)
	{
		$e_mail = "";
		$i = 0;
		while($str[$i])
		{
			$e_mail .= "&#".ord((string)$str[$i]).";";
			$i++;
		}
		$str = $e_mail;
		return $str;
	}
	
	// ",' で括ったフレーズ対応スプリット
	function phrase_split($str)
	{
		$words = array();
		$str = preg_replace("/(\"|'|”|’)(.+?)(?:\\1)/e","str_replace(' ','\x08','$2')",$str);
		$words = preg_split('/\s+/',$str,-1,PREG_SPLIT_NO_EMPTY);
		$words = str_replace("\x08"," ",$words);
		return $words;
	}
	
	// 配列対応 & gpc 対応のstripslashes
	function stripslashes_gpc(&$v)
	{
		if(ini_get("magic_quotes_gpc"))
		{
			if (is_array($v))
			{
				$arr =array();
				foreach($v as $k=>$m)
				{
					$arr[$k] = HypCommonFunc::stripslashes_gpc($m);
				}
				$v = $arr;
			}
			else
			{
				$v = stripslashes($v);
			}
		}
		return $v;
	}
	
	// RSS関連のキャッシュを削除する
	function clear_rss_cache($files=array())
	{
		include_once XOOPS_ROOT_PATH.'/class/template.php';
		
		if (empty($files) || !is_array($files))
		{
			$files = array(
				'db:BopComments_rss.html',
				'db:whatsnew_rss.html',
				'db:whatsnew_atom.html',
				'db:whatsnew_rdf.html',
				'db:whatsnew_pda.html',
				'db:whatsnew_block_bop.html',
				'db:whatsnew_block_mod.html',
				'db:whatsnew_block_date.html',
			);
		}
		
		$tpl = new XoopsTpl();
		$tpl->xoops_setCaching(2);
		foreach($files as $tgt)
		{
			if ($tgt) {$tpl->clear_cache($tgt);}
		}
	}
	
	// RPC Update Ping を打つ
	function update_rpc_ping($default_update="http://bulkfeeds.net/rpc http://ping.myblog.jp http://ping.bloggers.jp/rpc/ http://blog.goo.ne.jp/XMLRPC http://ping.cocolog-nifty.com/xmlrpc http://rpc.technorati.jp/rpc/ping")
	{
		global $xoopsConfig;
		
		//RSSキャッシュファイルを削除
		HypCommonFunc::clear_rss_cache();
		
		$update_ping2 = $default_update;
		$update_ping = preg_split ( "/[\s,]+/" , $update_ping2 );

		$ping_blog_name = $xoopsConfig['sitename'];
		$ping_url		= XOOPS_URL."/";

		$ping_update = <<<EOF
	<?xml version="1.0"?>
	<methodCall>
		<methodName>weblogUpdates.ping</methodName>
		<params>
		<param><value>$ping_blog_name</value></param>
		<param><value>$ping_url</value></param>
		</params>
	</methodCall>
EOF;

		$ping_update = mb_convert_encoding
					   ( $ping_update , "UTF-8" , "EUC-JP" );

		$ping_update_leng = strlen($ping_update);

		foreach ( $update_ping as $up )
		{
			if ( $up != "" )
			{
				$uph = ereg_replace ( "http:\/\/", "", $up );
				list ( $host , $uri ) = split ( "/", $uph , 2 );
				list ( $host , $port ) = split ( ":", $host );

				if ( $port == "" )
				{
					$port = 80;
					$add_port = "";
				}
				else
				{
					$add_port = ":$port";
				}
				
				$errNo = 0;
				$errStr = "";
				$files = @fsockopen($host, $port , $errNo , $errStr, 10);

				@fputs($files, "POST /$uri HTTP/1.0\r\n" );
				@fputs($files, "Host: $host$add_port\r\n" );
				@fputs($files, "Content-Length: $ping_update_leng\r\n" );
				@fputs($files, "User-Agent: XOOPS update pinger Ver 1.00\r\n" );
				@fputs($files, "Content-Type: text/xml\r\n" );
				@fputs($files, "\r\n" );
				@fputs($files, "$ping_update" );

				fclose ( $files );

			}
		}
		return ;
	}
	
	function make_context($text,$words=array(),$l=255)
	{
		static $strcut = "";
		if (!$strcut)
			$strcut = create_function ( '$a,$b,$c', (function_exists('mb_strcut'))?
				'return mb_strcut($a,$b,$c);':
				'return strcut($a,$b,$c);');
		
		$text = str_replace(array('&lt;','&gt;','&amp;','&quot;','&#039;'),array('<','>','&','"',"'"),$text);
		
		if (!is_array($words)) $words = array();
		
		$ret = "";
		$q_word = str_replace(" ","|",preg_quote(join(' ',$words),"/"));
		
		$match = array();
		if (preg_match("/$q_word/i",$text,$match))
		{
			$ret = ltrim(preg_replace('/\s+/', ' ', $text));
			list($pre, $aft) = array_pad(preg_split("/$q_word/i", $ret, 2), 2, "");
			$m = intval($l/2);
			$ret = (strlen($pre) > $m)? "... " : "";
			$ret .= $strcut($pre, max(strlen($pre)-$m+1,0),$m).$match[0];
			$m = $l-strlen($ret);
			$ret .= $strcut($aft, 0, min(strlen($aft),$m));
			if (strlen($aft) > $m) $ret .= " ...";
		}
		
		if (!$ret)
			$ret = $strcut($text, 0, $l);
		
		return htmlspecialchars($ret, ENT_NOQUOTES);
	}
	
	function set_need_refresh($mode)
	{
		if ($mode)
		{
			setcookie ("HypNeedRefresh", "1");
		}
		else
		{
			setcookie ("HypNeedRefresh", "", time() - 3600);
		}
	}
	
	// HTML の meta タグから文字エンコーディングを取得する
	function get_encoding_by_meta($html)
	{
		$codesets = array(
			'shift_jis' => 'Shift_JIS',
			'x-sjis' => 'Shift_JIS',
			'euc-jp' => 'EUC-JP',
			'x-euc-jp' => 'EUC-JP',
			'iso-2022-jp' => 'JIS',
			'utf-8' => 'UTF-8',
		);
		$match = array();
		if (preg_match("/<meta[^>]*content=(?:\"|')[^\"'>]*charset=([^\"'>]+)(?:\"|')[^>]*>/is",$html,$match))
		{
			$encode = strtolower($match[1]);
			if (array_key_exists($encode,$codesets))
			{
				return $codesets[$encode];
			}
			else
			{
				return "EUC-JP,UTF-8,Shift_JIS,JIS";
			}
		}
		else
		{
			return "EUC-JP,UTF-8,Shift_JIS,JIS";
		}
	}

	// サムネイル画像を作成。
	// 成功ならサムネイルのファイルのパス、不成功なら元ファイルパスを返す
	function make_thumb($o_file, $s_file, $max_width, $max_height, $zoom_limit="5,90", $refresh=FALSE, $quality = 75)
	{
		//GD のバージョンを取得
		static $gd_ver = null;
		if (is_null($gd_ver))
		{
			$gd_ver = HypCommonFunc::gdVersion();
		}
		
		// すでに作成済み
		if (!$refresh && file_exists($s_file)) return $s_file;
		
		// gd fuction のチェック
		if ($gd_ver < 1 || !function_exists("imagecreate")) return $o_file;//gdをサポートしていない
		
		// gd のバージョンによる関数名の定義
		$imagecreate = ($gd_ver >= 2)? "imagecreatetruecolor" : "imagecreate";
		$imageresize = ($gd_ver >= 2)? "imagecopyresampled" : "imagecopyresized";
		
		$size = @getimagesize($o_file);
		if (!$size) return $o_file;//画像ファイルではない
		
		// 元画像のサイズ
		$org_w = $size[0];
		$org_h = $size[1];
		
		if ($max_width >= $org_w && $max_height >= $org_h) return $o_file;//指定サイズが元サイズより大きい
		
		// 縮小率の設定
		list($zoom_limit_min,$zoom_limit_max) = explode(",",$zoom_limit);
		$zoom = min(($max_width/$org_w),($max_height/$org_h));
		if (!$zoom || $zoom < $zoom_limit_min/100 || $zoom > $zoom_limit_max/100) return $o_file;//ZOOM値が範囲外
		$width = $org_w * $zoom;
		$height = $org_h * $zoom;
		
		// サムネイルのファイルタイプが指定されている？(.jpg)
		$s_ext = "";
		$s_ext = preg_replace("/\.([^\.]+)$/","$1",$s_file);
		
		switch($size[2])
		{
			case "1": //gif形式
				if (function_exists ("imagecreatefromgif"))
				{
					$src_im = imagecreatefromgif($o_file);
					$colortransparent = imagecolortransparent($src_im);
					if ($s_ext != "jpg" && $colortransparent > -1)
					{
						// 透過色あり
						$dst_im = imagecreate($width,$height);
						imagepalettecopy ($dst_im, $src_im);
						imagefill($dst_im,0,0,$colortransparent);
						imagecolortransparent($dst_im, $colortransparent);
						imagecopyresized($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
					}
					else
					{
						// 透過色なし
						$dst_im = $imagecreate($width,$height);
						$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
						imagetruecolortopalette ($dst_im,imagecolorstotal ($src_im));
					}
					touch($s_file);
					if ($s_ext == "jpg")
					{
						imagejpeg($dst_im,$s_file,$quality);
					}
					else
					{
						if (function_exists("imagegif"))
						{
							imagegif($dst_im,$s_file);
						}
						else
						{
							imagepng($dst_im,$s_file);
						}
					}
					$o_file = $s_file;
				}
				break;
			case "2": //jpg形式
				$src_im = imagecreatefromjpeg($o_file);
				$dst_im = $imagecreate($width,$height);
				$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
				touch($s_file);
				imagejpeg($dst_im,$s_file,$quality);
				$o_file = $s_file;
				break;
			case "3": //png形式
				$src_im = imagecreatefrompng($o_file);
				if (imagecolorstotal($src_im))
				{
					// PaletteColor
					$colortransparent = imagecolortransparent($src_im);
					if ($s_ext != "jpg" && $colortransparent > -1)
					{
						// 透過色あり
						$dst_im = imagecreate($width,$height);
						imagepalettecopy ($dst_im, $src_im);
						imagefill($dst_im,0,0,$colortransparent);
						imagecolortransparent($dst_im, $colortransparent);
						imagecopyresized($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
					}
					else
					{
						// 透過色なし
						$dst_im = $imagecreate($width,$height);
						$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
						imagetruecolortopalette ($dst_im,imagecolorstotal ($src_im));
					}
				}
				else
				{
					// TrueColor
					$dst_im = $imagecreate($width,$height);
					$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
				}
				touch($s_file);
				if ($s_ext == "jpg")
				{
					imagejpeg($dst_im,$s_file,$quality);
				}
				else
				{
					imagepng($dst_im,$s_file);
				}
				$o_file = $s_file;
				break;
			default:
				break;
		}
		@imagedestroy($dst_im);
		@imagedestroy($src_im);
		return $o_file;
	}
	
	// GD のバージョンを取得
	// RETURN 0:GDなし, 1:Ver 1, 2:Ver 2
	function gdVersion($user_ver = 0)
	{
		if (! extension_loaded('gd')) { return 0; }
		static $gd_ver = 0;
		// Just accept the specified setting if it's 1.
		if ($user_ver == 1) { $gd_ver = 1; return 1; }
		// Use the static variable if function was called previously.
		if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
		// Use the gd_info() function if possible.
		if (function_exists('gd_info')) {
			$ver_info = gd_info();
			$match = array();
			preg_match('/\d/', $ver_info['GD Version'], $match);
			$gd_ver = $match[0];
			return $match[0];
		}
		// If phpinfo() is disabled use a specified / fail-safe choice...
		if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
			if ($user_ver == 2) {
				$gd_ver = 2;
				return 2;
			} else {
				$gd_ver = 1;
				return 1;
			}
		}
		// ...otherwise use phpinfo().
		ob_start();
		phpinfo(8);
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr($info, 'gd version');
		preg_match('/\d/', $info, $match);
		$gd_ver = $match[0];
		return $match[0];
	}
	
	// イメージを回転
	function rotateImage($src, $count = 1, $quality = 95)
	{
		if (!file_exists($src)) {
			return false;
		}

		list($w, $h) = getimagesize($src);
		
		if (!$w || !$h) return false;

		if (($in = imageCreateFromJpeg($src)) === false) {
			return false;
		}

		$angle = 360 - ((($count > 0 && $count < 4) ? $count : 0 ) * 90);

		if ($w == $h || $angle == 180) {
			$out = imageRotate($in, $angle, 0);
		} elseif ($angle == 90 || $angle == 270) {
			$size = ($w > $h ? $w : $h);
			
			$portrait = ($h > $w)? true : false; 
			
			// Create a square image the size of the largest side of our src image
			if (($tmp = imageCreateTrueColor($size, $size)) == false) {
				//echo "Failed create square trueColor<br>";
				return false;
			}

			// Exchange sides
			if (($out = imageCreateTrueColor($h, $w)) == false) {
				//echo "Failed create trueColor<br>";
				return false;
			}

			// Now copy our src image to tmp where we will rotate and then copy that to $out
			imageCopy($tmp, $in, 0, 0, 0, 0, $w, $h);
			$tmp2 = imageRotate($tmp, $angle, 0);

			// Now copy tmp2 to $out;
			imageCopy($out, $tmp2, 0, 0, (($angle == 270 && !$portrait) ? abs($w - $h) : 0), (($angle == 90 && $portrait) ? abs($w - $h) : 0), $h, $w);
			imageDestroy($tmp);
			imageDestroy($tmp2);
		} elseif ($angle == 360) {
			imageDestroy($in);
			return true;
		}

		imageJpeg($out, $src, $quality);
		imageDestroy($in);
		imageDestroy($out);
		return true;
	}

	// 2ch BBQ あらしお断りシステム にリスティングされているかチェック
	function IsBBQListed($safe_reg = '/^$/', $msg = false, $ip = NULL)
	{
		if (is_null($ip)) $ip = $_SERVER['REMOTE_ADDR'];
		if(! preg_match($safe_reg, $ip))
		{
			if (!$msg) $msg = '公開プロキシ経由での投稿はできません。';
			
			$host = array_reverse(explode('.', $ip));
			$addr = sprintf("%d.%d.%d.%d.niku.2ch.net",
				$host[0],$host[1],$host[2],$host[3]);
			$addr = gethostbyname($addr);
			if(preg_match("/^127\.0\.0/",$addr)) return $msg;
		}
		return false;
	}
	
	// 2ch BBQ チェック用汎用関数
	function BBQ_Check($safe_reg = "/^(127\.0\.0\.1)/", $msg = false, $ip = NULL)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if ($_msg = HypCommonFunc::IsBBQListed($safe_reg, $msg, $ip))
			{
				exit ($_msg);
			}
		}
		return;
	}
	
	// リファラーから検索語と検索エンジンを取得し定数に定義する
	function set_query_words($qw="HYP_QUERY_WORD",$qw2="HYP_QUERY_WORD2",$en="HYP_SEARCH_ENGINE_NAME",$tmpdir="")
	{
		if (!defined($qw))
		{
			if (file_exists(dirname(__FILE__)."/hyp_get_engine.php"))
			{
				include_once(dirname(__FILE__)."/hyp_get_engine.php");
				HypGetQueryWord::set_constants($qw,$qw2,$en,$tmpdir);
			}
			else
			{
				define($qw , "");
				define($qw2, "");
				define($en , "");
			}
		}
	}
}

/*
 *   HTTPリクエストを発行し、データを取得する
 * $url     : http://から始まるURL(http://user:pass@host:port/path?query)
 * $method  : GET, POST, HEADのいずれか(デフォルトはGET)
 * $headers : 任意の追加ヘッダ
 * $post    : POSTの時に送信するデータを格納した配列('変数名'=>'値')
 * $redirect_max : HTTP redirectの回数制限
*/

if( ! class_exists( 'Hyp_HTTP_Request' ) )
{
class Hyp_HTTP_Request
{
	var $url='';
	var $method='GET';
	var $headers='';
	var $post=array();
	var $ua='';
	
	// リダイレクト回数制限
	var $redirect_max=10;
	// 同期モード or 非同期モード
	var $blocking=TRUE;
	// 接続試行回数
	var $connect_try=1;
	// 接続時タイムアウト
	var $connect_timeout=30;
	// 通信時タイムアウト
	var $read_timeout=10;
	
	
	// プロキシ使用？
	var $use_proxy=0;
	
	// proxy ホスト
	var $proxy_host='proxy.xxx.yyy.zzz';
	
	// proxy ポート番号
	var $proxy_port='';
	
	// プロキシサーバを使用しないホストのリスト
	var $no_proxy=array( 
		'127.0.0.1', 
		'localhost', 
		//'192.168.1.0/24', 
		//'no-proxy.com', 
	);
	
	// プロキシ認証
	var $need_proxy_auth=0;
	var $proxy_auth_user='';
	var $proxy_auth_pass='';

	// result
	var $query = '';   // Query String
	var $rc = '';      // Response Code
	var $header = '';  // Header
	var $data = '';    // Data
	
	function Hyp_HTTP_Request()
	{
		$this->ua="PHP/".PHP_VERSION;
	}
		
	function init()
	{
		$this->url='';
		$this->method='GET';
		$this->headers='';
		$this->post=array();
		$this->ua="PHP/".PHP_VERSION;
		
		// result
		$this->query = '';   // Query String
		$this->rc = '';      // Response Code
		$this->header = '';  // Header
		$this->data = '';    // Data
	}
	function get()
	{
		$max_execution_time = ini_get('max_execution_time');
		$max_execution_time = ($max_execution_time)? $max_execution_time : 30;
		
		$rc = array();
		$arr = parse_url($this->url);
		if (!$this->connect_try) $this->connect_try = 1;
		
		$via_proxy = $this->use_proxy and via_proxy($arr['host']);
		
		// query
		$arr['query'] = isset($arr['query']) ? '?'.$arr['query'] : '';
		// port
		$arr['port'] = isset($arr['port']) ? $arr['port'] : 80;
		
		$url_base = $arr['scheme'].'://'.$arr['host'].':'.$arr['port'];
		$url_path = isset($arr['path']) ? $arr['path'] : '/';
		$this->url = ($via_proxy ? $url_base : '').$url_path.$arr['query'];

		
		$query = $this->method.' '.$this->url." HTTP/1.0\r\n";
		$query .= "Host: ".$arr['host']."\r\n";
		if (!empty($this->ua)) $query .= "User-Agent: ".$this->ua."\r\n";
		
		// proxyのBasic認証 
		if ($this->need_proxy_auth and isset($this->proxy_auth_user) and isset($this->proxy_auth_pass)) 
		{
			$query .= 'Proxy-Authorization: Basic '.
				base64_encode($this->proxy_auth_user.':'.$this->proxy_auth_pass)."\r\n";
		}

		// Basic 認証用
		if (isset($arr['user']) and isset($arr['pass']))
		{
			$query .= 'Authorization: Basic '.
				base64_encode($arr['user'].':'.$arr['pass'])."\r\n";
		}
		
		$query .= $this->headers;
		
		// POST 時は、urlencode したデータとする
		if (strtoupper($this->method) == 'POST')
		{
			if (is_array($this->post))
			{
				$_send = array();
				foreach ($this->post as $name=>$val)
				{
					$_send[] = $name.'='.urlencode($val);
				}
				$data = join('&',$_send);
				$query .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$query .= 'Content-Length: '.strlen($data)."\r\n";
				$query .= "\r\n";
				$query .= $data;
			}
			else
			{
				$query .= 'Content-Length: '.strlen($this->post)."\r\n";
				$query .= "\r\n";
				$query .= $this->post;
			}
		}
		else
		{
			$query .= "\r\n";
		}
		
		//set_time_limit($this->connect_timeout * $this->connect_try + 60);
		$fp = $connect_try_count = 0;
		while( !$fp && $connect_try_count < $this->connect_try )
		{
			@set_time_limit($this->connect_timeout + $max_execution_time);
			$errno = 0;
			$errstr = "";
			$fp = fsockopen(
				$via_proxy ? $this->proxy_host : $arr['host'],
				$via_proxy ? $this->proxy_port : $arr['port'],
				$errno,$errstr,$this->connect_timeout);
			if ($fp) break;
			$connect_try_count++;
			sleep(2); //2秒待つ
		}
		if (!$fp)
		{
			$this->query  = $query;  // Query String
			$this->rc     = $errno;  // エラー番号
			$this->header = '';      // Header
			$this->data   = $errstr; // エラーメッセージ
			return;
		}
		
		fputs($fp, $query);
		
		// 非同期モード
		if (!$this->blocking)
		{
			fclose($fp);
			$this->query  = $query;
			$this->rc     = 200;
			$this->header = '';
			$this->data   = 'Blocking mode is FALSE';
			return;
		}
		
		$response = '';
		while (!feof($fp))
		{
			if ($this->read_timeout)
			{
				@set_time_limit($this->read_timeout + $max_execution_time);
				socket_set_timeout($fp, $this->read_timeout);
			}
			$_response = fread($fp,4096);
			$_status = socket_get_status($fp);
			if ($_status['timed_out'] === false)
			{
				$response .= $_response;
			}
			else
			{
				fclose($fp);
				$this->query  = $query;
				$this->rc     = 408;
				$this->header = '';
				$this->data   = 'Request Time-out';
				return;
			}
		}
		fclose($fp);
		
		$resp = explode("\r\n\r\n",$response,2);
		$rccd = explode(' ',$resp[0],3); // array('HTTP/1.1','200','OK\r\n...')
		$rc = (integer)$rccd[1];
		
		// Redirect
		switch ($rc)
		{
			case 302: // Moved Temporarily
			case 301: // Moved Permanently
				$matches = array();
				if (preg_match('/^Location: (.+)$/m',$resp[0],$matches)
					and --$this->redirect_max > 0)
				{
					$this->url = trim($matches[1]);
					if (!preg_match('/^https?:\//',$this->url)) // no scheme
					{
						if ($this->url{0} != '/') // Relative path
						{
							// to Absolute path
							$this->url = substr($url_path,0,strrpos($url_path,'/')).'/'.$this->url;
						}
						// add sheme,host
						$this->url = $url_base.$this->url;
					} 
					return $this->get();
				}
		}
		
		$this->query = $query;    // Query String
		$this->rc = $rc;          // Response Code
		$this->header = $resp[0]; // Header
		$this->data = $resp[1];   // Data
		return;
	}
	// プロキシを経由する必要があるかどうか判定
	function via_proxy($host)
	{
		static $ip_pattern = '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?:\/(.+))?$/';
		
		if (!$this->use_proxy)
		{
			return FALSE;
		}
		$ip = gethostbyname($host);
		$l_ip = ip2long($ip);
		$valid = (is_long($l_ip) and long2ip($l_ip) == $ip); // valid ip address
		
		foreach ($this->no_proxy as $network)
		{
			$matches = array();
			if ($valid and preg_match($ip_pattern,$network,$matches))
			{
				$l_net = ip2long($matches[1]);
				$mask = array_key_exists(2,$matches) ? $matches[2] : 32;
				$mask = is_numeric($mask) ?
					pow(2,32) - pow(2,32 - $mask) : // "10.0.0.0/8"
					ip2long($mask);                 // "10.0.0.0/255.0.0.0"
				if (($l_ip & $mask) == $l_net)
				{
					return FALSE;
				}
			}
			else
			{
				if (preg_match('/'.preg_quote($network,'/').'/',$host))
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}
}

// create a instance in global scope
//$GLOBALS['hypCommonFunc'] = new HypCommonFunc() ;

// Make context for search by nao-pon
if (!function_exists('xoops_make_context'))
{
function xoops_make_context($text,$words=array(),$l=255)
{
	return HypCommonFunc::make_context($text,$words,$l);
}
}

if (!function_exists('xoops_update_rpc_ping'))
{
function xoops_update_rpc_ping($default_update="http://bulkfeeds.net/rpc http://ping.myblog.jp http://ping.bloggers.jp/rpc/ http://blog.goo.ne.jp/XMLRPC http://ping.cocolog-nifty.com/xmlrpc http://rpc.technorati.jp/rpc/ping")
{
	return HypCommonFunc::update_rpc_ping($default_update);
}
}

}
?>