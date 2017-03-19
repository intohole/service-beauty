<?php 
define('MATRIX_PHP_PATH', '/data/apache/www/_work');
define('MATRIX_ERROR_FILE',dirname(__FILE__).'/error.php');
$host = 'cms.ledu.com';
define('PAIN_STATIC_HOST',"http://$host/");
define('PAIN_HOST',"http://$host/pain");
define('PAIN_API_HOST',"http://$host/");
$GLOBALS['gPAIN_HOST'] = PAIN_HOST;

/*
//独立的新闻城市
$GLOBALS['gPAIN_MAIN_CITY']= array(
        'all' => '全国',
        'bj' => '北京',
        'sh' => '上海',
        'gz' => '广州',
        'nn' => '南宁',
        'sz' => '深圳'
);
*/

#$gPAIN_DEBUG = true;

class LeduPain{

		static $_id;
		static $_prop;
        /**
         * 碎片显示地址
         */
        function pain_get_show_path($id, $prop = 'all'){
                $p = md5($id);
                return   MATRIX_PHP_PATH . "/{$p[0]}{$p[1]}/{$p[2]}{$p[$h]}/{$id}.{$prop}";
        }
        /**
         * 碎片编辑地址
         */
        function pain_get_edit_path($id,$prop = 'all'){
                $p = md5($id);
                return  MATRIX_PHP_PATH . "/{$p[0]}{$p[1]}/{$p[2]}{$p[$h]}/$id.{$prop}.edit";
        }

        /** * 定时更新碎片执行路径
         */
        function pain_get_run_path($id,$prop = 'all'){
                $p = md5($id);
                return   MATRIX_PHP_PATH . "/{$p[0]}{$p[1]}/{$p[2]}{$p[$h]}/{$id}.{$prop}.run.php";
        } 

		function get_matrix_content(){
			ob_start();
			self::show_matrix();
			$data = ob_get_contents();
			ob_end_flush();
			return $data;
		}
        function show_matrix($id,$prop = 'all'){
                self::pain_show_matrix($id, $prop);
        }
		function show_run_matrix($id,$prop = 'all'){
 
                $path = self::pain_get_run_path($id, $prop);

                if(is_readable($path)){
                        include($path);
                }
                else{
                        echo "\n<!-- matrix not found (m_id: $id;city: $prop;  path: $path) -->\n";
                }
        }

        /**
         * 显示一个碎片 
         */
        function pain_show_matrix($id,$prop = 'all'){
                if($_GET['ala_edit_matrix'] || $GLOBALS['gPAIN_DEBUG'] || $GLOBALS['gPAIN_EDIT_MATRIX']){
                        $edit = true;
                }

                //for ledu  no need city matching
                $prop = 'all';
                //end for ledu 

                if($edit){
                        $path = self::pain_get_edit_path($id, $prop);
				}
                else{
                        $path = self::pain_get_show_path($id, $prop);
				}

                if(is_readable($path)){
                        include($path);
                }
                else{
                        echo "\n<!-- matrix not found (m_id: $id;city: $prop;  path: $path) -->\n";
                }
        }
		/**
		 * 若碎片存在直接展示 返回false 
		 * 若碎片不存在 打开缓存 返回true
		 */


		function start_show_matrix($id,$prop = 'all'){
			$path = self::pain_get_show_path($id, $prop);
			//碎片文件存在
			if(is_readable($path)){
				self::pain_show_matrix($id,$prop);
				return false;
			}
			else{
				self::$_id =$id;
				self::$_prop =$prop;
				ob_start();
				return true;
			}
		}

		function  end_show_matrix(){
			$data = ob_get_clean();
			$id = self::$_id;
			$prop = self::$_prop;
			if(empty($id) || empty($prop)){
				die('碎片创建id不存在 确认stat_show_matrix 与 end_show_matrix配对出现 <br>');
			}

			self::$_id = null;
			self::$_prop = null;
			$data =  self::_create_matrix($id, $data, $prop);

			if( $data !== false){
				echo $data;
			}
			else{
				echo "$id 创建失败";
			}

		}

		function  _create_matrix($id, $data,  $prop){
			$url = PAIN_API_HOST."api/create?id=$id&prop=$prop";
			$res = self::fopen($url,0, 'data='. urlencode($data));
			$res = json_decode($res);
			if(empty($res)){
				return false;
			}
			if($res->error != 0){
				return false;
			}

			return  $res->data;

		}

		function fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
		$return = '';
		$matches = parse_url ( $url );
		
		! isset ( $matches ['host'] ) && $matches ['host'] = '';
		! isset ( $matches ['path'] ) && $matches ['path'] = '';
		! isset ( $matches ['query'] ) && $matches ['query'] = '';
		! isset ( $matches ['port'] ) && $matches ['port'] = '';
		$host = $matches ['host'];
		$path = $matches ['path'] ? $matches ['path'] . ($matches ['query'] ? '?' . $matches ['query'] : '') : '/';
		$port = ! empty ( $matches ['port'] ) ? $matches ['port'] : 80;
		if ($post) {
			$out = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= 'Content-Length: ' . strlen ( $post ) . "\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
			$out .= $post;
		} else {
			$out = "GET $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
		}
		
		$fp = fsockopen ( ($ip ? $ip : $host), $port, $errno, $errstr, $timeout );
		
		if (! $fp) {
			return '';
		} else {
			stream_set_blocking ( $fp, $block );
			stream_set_timeout ( $fp, $timeout );
			@fwrite ( $fp, $out );
			$status = stream_get_meta_data ( $fp );
			if (! $status ['timed_out']) {
				while ( ! feof ( $fp ) ) {
					if (($header = @fgets ( $fp )) && ($header == "\r\n" || $header == "\n")) {
						break;
					}
				}
				
				$stop = false;
				while ( ! feof ( $fp ) && ! $stop ) {
					$data = fread ( $fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
					$return .= $data;
					if ($limit) {
						$limit -= strlen ( $data );
						$stop = $limit <= 0;
					}
				}
			}
			@fclose ( $fp );
			return $return;
		}
	}

        /**
         * 定时更新碎片执行
         */
        function pain_run_matrix($id, $prop = 'all'){
                $run_file = pain_get_run_path($id, $prop);
                $show_file = pain_get_show_path($id, $prop);
                if(!is_readable($run_file))
                        $run_file = MATRIX_ERROR_FILE;
                $content = "<!-- matrix: $id time:".date('Y-m-d H:i:s')."-->\n";

                ob_start();
                include($run_file);
                $res =  ob_get_clean();
                $content .= $res."<!-- matrix: $id end-->";
                file_put_contents($show_file , $content);
                return $content;
        }

        /**
         * 初始化
         * */
        function pain_start_edit($group = 'news'){
                //check login 
                if(empty($GLOBALS['login_id'])){
                        return ;

				}
                //check edit 
                if($GLOBALS['gPAIN_DEBUG']  ||  $GLOBALS['gPAIN_EDIT_MATRIX'] || ($_GET['ala_edit_matrix'] &&  preg_match('@^(dev\.)?cms\.ledu\.com$@',$_SERVER['HTTP_HOST']))){

                        $edit_type = 'frag';
                        $static  = PAIN_STATIC_HOST;
                        $str =<<<_EOF_
                                <!--- 碎片编辑 开始-->
                                <link rel="stylesheet" href="$static/pain/css/focusCms.css" type="text/css" media="screen" />
                                <link rel="stylesheet" href="$static/pain/css/mooRainbow.css" type="text/css" media="screen" />
                                <script type="text/javascript" src="$static/pain/js/log4js.js"></script>
                                <script type="text/javascript" src="$static/js/datePicker/WdatePicker.js"></script>
<script type="text/javascript" src="$static/pain/js/mootools.js"></script>
<script type="text/javascript" src="$static/pain/js/mootools-more.js"></script>
<script type="text/javascript" src="$static/pain/js/mooRainbow.1.2b2.js"></script>
<script type="text/javascript">
var gEditType = '$edit_type'; 
var gDebug = "{$GLOBLAS['gPAIN_DEBUG']}"; 
var pain_group = '$group';
                </script>
                <script type="text/javascript" src="$static/pain/js/sys.js"></script>
                <script type="text/javascript" src="$static/pain/js/cms.js"></script>
                <script type="text/javascript" src="$static/pain/js/cmsUtils.js"></script>
                <script type="text/javascript" src="$static/pain/js/window.js"></script>
                <script type="text/javascript" src="$static/pain/js/ztEditorWindow.js"></script>
                <div id="focus_frag_block"></div>
                <!--- 碎片编辑 结束-->
_EOF_;
echo $str;
        }
}

function _pain_get_css($p) {
        echo '<link rel="stylesheet" href="'.PAIN_STATIC_HOST.'/'.$p.'" type="text/css" media="screen" />';
}
function _pain_get_js($p){
        echo '<script type="text/javascript" src="'.PAIN_STATIC_HOST.'/'.$p.'"></script>';
}
}
