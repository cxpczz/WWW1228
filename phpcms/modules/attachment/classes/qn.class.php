<?php
class SDK implements ArrayAccess {
	const QINIU_UP_HOST	= 'http://up.qiniu.com';
	const QINIU_RS_HOST	= 'http://rs.qbox.me';
	const QINIU_RSF_HOST= 'http://rsf.qbox.me';
	//查看 
	//删除 
	//复制 x
	//移动 x
	//上传 
	protected $access_token ;
	protected $secret_token ;
	protected $bucket;
	protected $cache = array();
	protected $aliases = array(); //文件别名, 针对文件名比较长的文件
	//curl 
	protected $ch;
	protected $headers;
	protected $options = array();
	protected $response;
	protected $info;
	protected $errno; 
	protected $error;
	public function __construct($access_token, $secret_token, $bucket = null)
	{
		$this->access_token = $access_token;
		$this->secret_token = $secret_token;
		$this->bucket = $bucket;
	}
	//获取空间名称
	public function getBucket()
	{
		return $this->bucket;
	}
	//设置空间
	public function setBucket($bucket)
	{
		$this->bucket = $bucket;
	}
	/**
	 * 查看指定文件信息。
	 * @param  string $key  	文件名或者目录+文件名
	 * @return Array|boolean 	成功返回文件内容，否会返回false.
	 */
	public function stat($key)
	{
		list($bucket, $key) = $this->parseKey($key);
		if ( is_null($bucket) ) 
		{
			die('error');
		}
		$url = self::QINIU_RS_HOST .'/stat/' . $this->encode("$bucket:$key");
		$token = $this->accessToken($url);
		$options[CURLOPT_HTTPHEADER] = array('Authorization: QBox '. $token);
		return $this->get($url, $options);
	}
	/**
	 * 删除指定文件信息。
	 * @param  string $key  	文件名或者目录+文件名
	 * @return NULL
	 */
	public function delete($key)
	{
		list($bucket, $key) = $this->parseKey($key);
		if ( is_null($bucket) ) 
		{
			die('error');
		}
		$url = self::QINIU_RS_HOST .'/delete/' . $this->encode("$bucket:$key");
		$token = $this->accessToken($url);
		$options[CURLOPT_HTTPHEADER] = array('Authorization: QBox '. $token);
		return $this->get($url, $options);
	}
	
	public function upload($file, $name=null, $token = null)
	{
		if ( NULL === $token ) 
		{
			$token = $this->uploadToken($this->bucket);
		}
		if ( !file_exists($file) ) 
		{
			die('文件不存在，构建一个临时文件');
		}
		$hash = hash_file('crc32b', $file);
		$array = unpack('N', pack('H*', $hash));
		$postFields = array(
			'token' => $token,
			'file'  => '@'.$file,
			'key'   => $name,
			'crc32' => sprintf('%u', $array[1]),
		);
		//未指定文件名，使用七牛默认的随机文件名
		if ( NULL === $name ) 
		{
			unset($postFields['key']);
		}
		else
		{
			//设置文件名后缀。
		}
		$options = array(
			CURLOPT_POSTFIELDS => $postFields,
		);

		return $this->get(self::QINIU_UP_HOST, $options);
	}
	
    /**
     * 上传目录
     * @param array $options
     * $options = array(
     *      'bucket'    =>  (Required) string
     *      'object'    =>  (Optional) string
     *      'directory' =>  (Required) string
     *      'exclude'   =>  (Optional) string
     *      'recursive' =>  (Optional) string
     *      'checkmd5'  =>  (Optional) boolean
     * )
     * @return bool
     * @
     */
	public function batch_upload_file($path,$name=null, $token = null)
	{
		if ( NULL === $token ) 
		{
			$token = $this->uploadToken($this->bucket);
		}
		if ( !file_exists($path) ) 
		{
			die('文件不存在，构建一个临时文件');
		}
		gws_main::load_sys_func('dir');
		$file_list_array=dir_list($path);
		
		foreach ($file_list_array as $k=>$file){
			if(is_dir($file)) continue;

			$hash = hash_file('crc32b', $file);
			$array = unpack('N', pack('H*', $hash));
			$postFields = array(
				'token' => $token,
				'file'  => '@'.$file,
				'key'   => $name.substr($file,strpos($file,$name)+strlen($name)),
				'crc32' => sprintf('%u', $array[1]),
			);
			//未指定文件名，使用七牛默认的随机文件名
			if ( NULL === $name ) {
				unset($postFields['key']);
			}
			else{
				//设置文件名后缀。
			}
			$options = array(
				CURLOPT_POSTFIELDS => $postFields,
			);

			
			$this->get(self::QINIU_UP_HOST, $options);
	  }
		
	}

	
	protected function parseKey($key)
	{
		$key = $this->getAlias($key);
		if ( isset($this->cache[$key]) ) 
		{
			return $this->cache[$key];
		}
		$segments = explode("|", $key);
		if ( count($segments) === 1 ) 
		{
			$this->cache[$key] = array($this->bucket, $segments[0]);
		}
		else
		{
			$temp = implode('|', array_slice($segments, 1));
			$this->cache[$key] = array($segments[0], $temp);
		}
		return $this->cache[$key];
	}
	public function getAlias($key)
	{
		return isset($this->aliases[$key]) ? $this->aliases[$key] : $key;
	}
	public function uploadToken($config = array())
	{
		if ( is_string($config) ) 
		{
			$scope = $config;
			$config = array();
		}
		else
		{
			$scope = $config['scope'];
		}
		$config['scope'] = $scope;
		//硬编码，需修改。
		$config['deadline'] = time() + 3600;
		foreach ( $this->activeUploadSettings($config) as $key => $value ) 
		{
			if ( $value ) 
			{
				$config[$key] = $value;
			}
		}
		//build token
		$body = json_encode($config);
		$body = $this->encode($body);
		$sign = hash_hmac('sha1', $body, $this->secret_token, true);
		return $this->access_token . ':' . $this->encode($sign) . ':' .$body;
	}
	public function uploadSettings()
	{
		return array(
			'scope','deadline','callbackUrl', 'callbackBody', 'returnUrl',
			'returnBody', 'asyncOps', 'endUser', 'exclusive', 'detectMime',
			'fsizeLimit', 'saveKey', 'persistentOps', 'persistentNotifyUrl'
		);
	}
	protected function activeUploadSettings($array)
	{
		return array_intersect_key($array, array_flip($this->uploadSettings()));
	}
	public function accessToken($url, $body = false)
	{
		$url = parse_url($url);
		$result = '';
		if (isset($url['path'])) {
			$result = $url['path'];
		}
		if (isset($url['query'])) {
			$result .= '?' . $url['query'];
		}
		$result .= "\n";
		if ($body) {
			$result .= $body;
		}
		$sign = hash_hmac('sha1', $result, $this->secret_token, true);
		return $this->access_token . ':' . $this->encode($sign);
	}
	public function get($url, $options = array())
	{
		$this->ch = curl_init();
		$this->options[CURLOPT_URL] = $url;
		$this->options = $options + $this->options;
		//临时处理逻辑
		
		return $this->execute();
	}
	 /**
     * 读取目录
     * @param $dir
     * @param string $exclude
     * @param bool $recursive
     * @return array
     */
	protected function read_dir($dir, $exclude = ".|..|.svn", $recursive = false){
			$file_list_array = array(); 
			$base_path=$dir; 
			$exclude_array = explode("|", $exclude); 
			// filter out "." and ".."
			$exclude_array = array_unique(array_merge($exclude_array,array('.','..'))); 
	
			if($recursive){
				foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $new_file)
				{
					if ($new_file->isDir()) continue;
						//echo "$new_file\n";
						$object = str_replace($base_path, '', $new_file);
						if(!in_array(strtolower($object), $exclude_array)){ 
							$object = ltrim($object, '/');
							if (is_file($new_file)){ 
								$key = md5($new_file.$object, false);
								$file_list_array[$key] = array('path' => $new_file,'file' => $object,); 
							} 
						}
				}
			}
			else if($handle = opendir($dir)){ 
				while ( false !== ($file = readdir($handle))){                 
					if(!in_array(strtolower($file), $exclude_array)){ 
						$new_file = $dir.'/'.$file;                 
	
						$object = $file;
						$object = ltrim($object, '/');
						if (is_file($new_file)){ 
							$key = md5($new_file.$object, false);
							$file_list_array[$key] = array('path' => $new_file,'file' => $object,); 
						} 
					} 
				} 
				closedir($handle);        
			}         
			return $file_list_array; 
	}	
	
	protected function execute() 
	{
		if ( !$this->option(CURLOPT_RETURNTRANSFER) ) 
		{
			$this->option(CURLOPT_RETURNTRANSFER, true);
		}
		if ( !$this->option(CURLOPT_SSL_VERIFYPEER) ) 
		{
			$this->option(CURLOPT_SSL_VERIFYPEER, false);
		}
		if ( !$this->option(CURLOPT_SSL_VERIFYHOST) ) 
		{
			$this->option(CURLOPT_SSL_VERIFYHOST, false);
		}
		if ( !$this->option(CURLOPT_CUSTOMREQUEST) ) 
		{
			$this->option(CURLOPT_CUSTOMREQUEST, 'POST');
		}
		if ( $this->headers ) 
		{
			$this->option(CURLOPT_HTTPHEADER, $this->headers);
		}
		$this->setupCurlOptions();
		$this->response = curl_exec($this->ch);
		$this->info = curl_getinfo($this->ch);

		if ( $this->response === false ) 
		{
			$this->error = curl_error($this->ch);
			$this->errno = curl_errno($this->ch);
			curl_close($this->ch);
			return false;
		}
		else
		{
			curl_close($this->ch);
			//未处理http_code。
			if ( $this->info['content_type'] == 'application/json' ) 
			{
				$this->response = json_decode($this->response, true);
			}
			return $this->response;
		}
	}
	public function setupCurlOptions()
	{
		curl_setopt_array($this->ch, $this->options);
	}
	public function option($key, $value = NULL)
	{
		if ( is_null($value) ) 
		{
			return !isset($this->options[$key]) ? null: $this->options[$key];
		}
		else
		{
			$this->options[$key] = $value;
			return $this;
		}
	}
	public function alias($key, $value)
	{
		$this->alias[$key] = $value;
	}
	protected function encode($str)
	{
        $trans = array("+" => "-", "/" => "_");
        return strtr(base64_encode($str), $trans);
	}
	public function __get($key)
	{
		return $this->$key;
	}
	public function offsetExists($key)
	{
		//check response;
	}
	public function offsetGet($key)
	{
		return $this->stat($key);
	}
	public function offsetSet($key, $value) 
	{
		//move or copy
	}
	public function offsetUnset($key)
	{
		return $this->delete();
	}
}



class Http
{
    public static $_httpInfo = '';
    public static $_curlHandler;

    /**
     * send http request
     * @param  array $rq http请求信息
     *                   url        : 请求的url地址
     *                   method     : 请求方法，'get', 'post', 'put', 'delete', 'head'
     *                   data       : 请求数据，如有设置，则method为post
     *                   header     : 需要设置的http头部
     *                   host       : 请求头部host
     *                   timeout    : 请求超时时间
     *                   cert       : ca文件路径
     *                   ssl_version: SSL版本号
     * @return string    http请求响应
     */
    public static function send($rq) {
        if (self::$_curlHandler) {
            if (function_exists('curl_reset')) {
                curl_reset(self::$_curlHandler);
            } else {
                my_curl_reset(self::$_curlHandler);
            }
        } else {
            self::$_curlHandler = curl_init();
        }
        curl_setopt(self::$_curlHandler, CURLOPT_URL, $rq['url']);
        switch (true) {
            case isset($rq['method']) && in_array(strtolower($rq['method']), array('get', 'post', 'put', 'delete', 'head')):
                $method = strtoupper($rq['method']);
                break;
            case isset($rq['data']):
                $method = 'POST';
                break;
            default:
                $method = 'GET';
        }
        $header = isset($rq['header']) ? $rq['header'] : array();
        $header[] = 'Method:'.$method;
        $header[] = 'User-Agent:'.Conf::getUA();
        $header[] = 'Connection: keep-alive';
        if ('POST' == $method) {
            $header[] = 'Expect: ';
        }

        isset($rq['host']) && $header[] = 'Host:'.$rq['host'];
        curl_setopt(self::$_curlHandler, CURLOPT_HTTPHEADER, $header);
        curl_setopt(self::$_curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$_curlHandler, CURLOPT_CUSTOMREQUEST, $method);
        isset($rq['timeout']) && curl_setopt(self::$_curlHandler, CURLOPT_TIMEOUT, $rq['timeout']);
        isset($rq['data']) && in_array($method, array('POST', 'PUT')) && curl_setopt(self::$_curlHandler, CURLOPT_POSTFIELDS, $rq['data']);
        $ssl = substr($rq['url'], 0, 8) == "https://" ? true : false;
        if( isset($rq['cert'])){
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYPEER,true);
            curl_setopt(self::$_curlHandler, CURLOPT_CAINFO, $rq['cert']);
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYHOST,2);
            if (isset($rq['ssl_version'])) {
                curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, $rq['ssl_version']);
            } else {
                curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, 4);
            }
        }else if( $ssl ){
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYPEER,false);   //true any ca
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYHOST,1);       //check only host
            if (isset($rq['ssl_version'])) {
                curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, $rq['ssl_version']);
            } else {
                curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, 4);
            }
        }
        $ret = curl_exec(self::$_curlHandler);
        self::$_httpInfo = curl_getinfo(self::$_curlHandler);
        //curl_close(self::$_curlHandler);
        return $ret;
    }

    public static function info() {
        return self::$_httpInfo;
    }
}