<?
include("../../../wp-load.php");

class BlipprCreateProxy {
	var $apiHost = "api.blippr.com";
	var $apiPath = "/v2/";

	function createTitle($post) {
		global $apiHost, $apiPath;
		
		$host = $this->apiHost;
		$path = $this->apiPath . "titles.json";
		$user = get_option("blippr_username");
		$pass = get_option("blippr_password");
		$auth = base64_encode($user . ":" . $pass);
		$data = array();
		$data[] = "media_type=" . $post["media_type"];
		if(is_array($post["node"])) {
			foreach($post["node"] as $k => $v) {
				$data[] = "node[$k]=$v";
			}
		}
		$sdata = join("&", $data);
		
		$fp = fsockopen($host, 80);
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ". strlen($sdata) ."\r\n");
		fputs($fp, "Authorization: Basic $auth\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $sdata);
		
		$result = ''; 
		while(!feof($fp)) { $result .= fgets($fp, 512); }
		
		fclose($fp);
		
		$crlf = "\r\n";
		$pos = strpos($result, $crlf . $crlf);
		if($pos === false)
			return($result);
			
		$header = substr($result, 0, $pos);
		$body = substr($result, $pos + 2 * strlen($crlf));		
		
		return array($header, $body);
	}
}

$proxy = new BlipprCreateProxy();
list($headers, $body) = $proxy->createTitle($_REQUEST);
unset($proxy);
echo $body;
?>