<?php 
class attachment {
	var $contentid;
	var $module;
	var $catid;
	var $attachments;
	var $attach_setting;
	var $field;
	var $imageexts = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
	var $uploadedfiles = array();
	var $downloadedfiles = array();
	var $error;
	var $upload_root;
	var $siteid;
	var $site = array();
	
	var $cos4tencent;//腾讯cos
	
	private $log_attachment_lib;
	
	function __construct($module='', $catid = 0,$siteid = 0,$upload_dir = '') {
	   
	    // logger
	    $this->log_attachment_lib= Logger::getLogger(__CLASS__);
	    ini_set('memory_limit','256M');
		$this->catid = intval($catid);
		$this->siteid = intval($siteid)== 0 ? 1 : intval($siteid);
		$this->module = $module ? $module : 'content';
		pc_base::load_sys_func('dir');		
		pc_base::load_sys_class('image','','0');
				//远程文件上传
		$this->attachments=getcache('attachments_var','attachments');
		$this->attach_setting=getcache('attach_setting','attachments');
		
		$this->log_attachment_lib->info('$this->attachments'.$this->attachments);
		$this->log_attachment_lib->info('$this->attach_setting'.$this->attachments);
		
		
		$this->upload_root = pc_base::load_config('system','upload_path');
		$this->upload_func = 'copy';
		$this->upload_dir = $upload_dir;
		

	}
	/**
	 * 附件上传方法
	 * @param $field 上传字段
	 * @param $alowexts 允许上传类型
	 * @param $maxsize 最大上传大小
	 * @param $overwrite 是否覆盖原有文件
	 * @param $thumb_setting 缩略图设置
	 * @param $watermark_enable  是否添加水印
	 */
	function upload($field, $alowexts = '', $maxsize = 0, $overwrite = 0,$thumb_setting = array(), $watermark_enable = 1) {
		
	    if(!isset($_FILES[$field])) {
			$this->error = UPLOAD_ERR_OK;
			return false;
		}
		if(empty($alowexts) || $alowexts == '') {
			$site_setting = $this->_get_site_setting($this->siteid);
			$alowexts = $site_setting['upload_allowext'];
		}

		if(($thumb_setting[0]==0&&$thumb_setting[1]==0)){
			$site_setting = $this->_get_site_setting($this->siteid);
			
			$thumb_setting[0]=(int)$site_setting['thumb_width'];
			$thumb_setting[1]=(int)$site_setting['thumb_height'];

		}

		$fn = isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : '1';
		#echo $fn;
		#exit;

		$this->field = $field;
		$this->savepath = $this->upload_root.$this->upload_dir.date('Y/md/');
		$this->alowexts = $alowexts;
		$this->maxsize = $maxsize;
		$this->overwrite = $overwrite;
		$uploadfiles = array();
		$description = isset($GLOBALS[$field.'_description']) ? $GLOBALS[$field.'_description'] : array();
		if(is_array($_FILES[$field]['error'])) {
			$this->uploads = count($_FILES[$field]['error']);
			foreach($_FILES[$field]['error'] as $key => $error) {
				if($error === UPLOAD_ERR_NO_FILE) continue;
				if($error !== UPLOAD_ERR_OK) {
					$this->error = $error;
					return false;
				}
				$uploadfiles[$key] = array('tmp_name' => $_FILES[$field]['tmp_name'][$key], 'name' => $_FILES[$field]['name'][$key], 'type' => $_FILES[$field]['type'][$key], 'size' => $_FILES[$field]['size'][$key], 'error' => $_FILES[$field]['error'][$key], 'description'=>$description[$key],'fn'=>$fn);
			}
		} else {
			$this->uploads = 1;
			if(!$description) $description = '';
			$uploadfiles[0] = array('tmp_name' => $_FILES[$field]['tmp_name'], 'name' => $_FILES[$field]['name'], 'type' => $_FILES[$field]['type'], 'size' => $_FILES[$field]['size'], 'error' => $_FILES[$field]['error'], 'description'=>$description,'fn'=>$fn);
		}

		if(!dir_create($this->savepath)) {
			$this->error = '8';
			return false;
		}
		if(!is_dir($this->savepath)) {
			$this->error = '8';
			return false;
		}
		@chmod($this->savepath, 0777);

		if(!is_writeable($this->savepath)) {
			$this->error = '9';
			return false;
		}
		if(!$this->is_allow_upload()) {
			$this->error = '13';
			return false;
		}
		$aids = array();
		foreach($uploadfiles as $k=>$file) {
			$fileext = fileext($file['name']);
			if($file['error'] != 0) {
				$this->error = $file['error'];
				return false;				
			}
			if(!preg_match("/^(".$this->alowexts.")$/", $fileext)) {
				$this->error = '10';
				return false;
			}
			if($this->maxsize && $file['size'] > $this->maxsize) {
				$this->error = '11';
				return false;
			}
			if(!$this->isuploadedfile($file['tmp_name'])) {
				$this->error = '12';
				return false;
			}
			
			
			if(in_array($fileext,$this->imageexts)&&$this->isImage($file['tmp_name'])===false){			
					$this->error = '14';
					return false;	
			}
	

			$temp_filename = $this->getname($fileext);
			$savefile = $this->savepath.$temp_filename;
			$savefile = preg_replace("/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", $savefile);
			$filepath = preg_replace(new_addslashes("|^".$this->upload_root."|"), "", $savefile);
			if(!$this->overwrite && file_exists($savefile)) continue;
			$upload_func = $this->upload_func;
			if(@$upload_func($file['tmp_name'], $savefile)) {//远程图片本地化 @copy($file['tmp_name'], $savefile)
				$this->uploadeds++;
				@chmod($savefile, 0644);
				@unlink($file['tmp_name']);
				$file['name'] = iconv("utf-8",CHARSET,$file['name']);
				$file['name'] = safe_replace($file['name']);
				$uploadedfile = array('filename'=>$file['name'], 'filepath'=>$filepath, 'filesize'=>$file['size'], 'fileext'=>$fileext, 'fn'=>$file['fn']);
				$thumb_enable = is_array($thumb_setting) && ($thumb_setting[0] > 0 || $thumb_setting[1] > 0 ) ? 1 : 0;	
				$image = new image($thumb_enable,$this->siteid);				
				if($thumb_enable) {
					$image->thumb($savefile,$savefile,$thumb_setting[0],$thumb_setting[1]);
					clearstatcache(true,$savefile);
				}
				if($watermark_enable) {
					$image->watermark($savefile,$savefile,$watermark_enable);
				}
				
				
				
				if(!empty($this->attachments)&&is_array($this->attachments)===true&&array_key_exists($fileext,$this->attachments)){
					
				    $this->log_attachment_lib->info('lib_aaa');
					switch($this->attachments[$fileext]['type']){
					    
						case "cos": 
						    
						  //加载腾讯cos
						  pc_base::vendor('cos4tencent','tencentcos',1);
						  
                          #$bucket = $this->attachments[$fileext]['bucket'];
                          define('APP_ID',$this->attachments[$fileext]['appid']);
                          define('SECRET_ID',$this->attachments[$fileext]['username']);
                          define('SECRET_KEY',$this->attachments[$fileext]['password']);
                          define('API_COSAPI_END_POINT',$this->attachments[$fileext]['host']);
                          
                          $this->cos4tencent = new cos4tencent(APP_ID, SECRET_ID, SECRET_KEY);
                          
                          $this->cos4tencent->putObject();

                          qcloudcos\Cosapi::setTimeout(180);
                          qcloudcos\Cosapi::setRegion($this->attachments[$fileext]['area']);
                          $ret =  qcloudcos\Cosapi::upload($this->attachments[$fileext]['bucket'],$this->upload_root.$filepath,$this->attachments[$fileext]['path'].$filepath);

						  #pc_base::load_app_class('qcloudcos','attachment',0);//载入qcloud cos类
						  #define('APPID',$this->attachments[$fileext]['appid']);
						  #define('SECRET_ID',$this->attachments[$fileext]['username']);
						  #define('SECRET_KEY',$this->attachments[$fileext]['password']);
					 	  #Cosapi::upload($this->upload_root.$filepath,$this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$filepath);//上传文件
						  
							
						break;						
					}
				   if(!$this->attachments[$fileext]['stored']) @unlink($savefile);
				}

				$uploadedfile = array('filename'=>$file['name'],'md5'=>md5_file($savefile), 'filepath'=>$filepath, 'filesize'=>filesize($savefile), 'fileext'=>$fileext, 'fn'=>$file['fn']);
				
				 unset($thumb_enable);
				$image=NULL;
				$aids[] = $this->add($uploadedfile);
			}
		}
		return $aids;
	}
	
	/**
	 * 附件下载
	 * Enter description here ...
	 * @param $field 预留字段
	 * @param $value 传入下载内容
	 * @param $watermark 是否加入水印
	 * @param $ext 下载扩展名
	 * @param $absurl 绝对路径
	 * @param $basehref 
	 */
	function download($field, $value,$watermark = '0',$ext = 'gif|jpg|jpeg|bmp|png', $absurl = '', $basehref = ''){
		global $image_d;
		$this->att_db = pc_base::load_model('attachment_model');
		$upload_url = pc_base::load_config('system','upload_url');
		$annex	 = pc_base::load_config('system','annex');
		$this->field = $field;
		$dir = date('Y/md/');
		$uploadpath =	$annex[array_rand($annex)].$dir; //$upload_url.$dir;//保存随机路径 以便于轮询
		
		$uploaddir = $this->upload_root.$dir;
		$string = new_stripslashes($value);
		$site_setting = $this->_get_site_setting($this->siteid);
		$thumb_setting[0]=(int)$site_setting['thumb_width'];
		$thumb_setting[1]=(int)$site_setting['thumb_height'];
		$thumb_enable = is_array($thumb_setting) && ($thumb_setting[0] > 0 || $thumb_setting[1] > 0 ) ? 1 : 0;			
		$dataurl = $datapath = array();
		
		if (preg_match_all("/src=('|\")data:([^;]*);base64,([^'\"]+)('|\")/",$string, $matches)) {//匹配data:图片，如果没有该参数，就进行下载
			dir_create($uploaddir);
			foreach($matches[3] as $r=>$matche){
				$dataurl[] = 'data:'.$matches[2][$r].';base64,'.$matche;
				$fileext= $filename=explode("/",$matches[2][$r])[1]; //获取拓展名
				$filename =$this->getname($filename);
				file_put_contents($uploaddir.$filename,base64_decode($matche));//写入文件
				if($thumb_enable) {
					$image = new image($thumb_enable,$this->siteid);
					$image->thumb($uploaddir.$filename,$uploaddir.$filename,$thumb_setting[0],$thumb_setting[1],'',0, 0,true); //base64图片强制压缩
					clearstatcache(true,$uploaddir.$filename);
					$image=NULL;
				}
				
				if($watermark){
						watermark($uploaddir.$filename,$uploaddir.$filename,$this->siteid,$watermark);
				}
				


				if(!empty($this->attachments)&&is_array($this->attachments)===true&&array_key_exists($fileext,$this->attachments)){
					
					switch($this->attachments[$fileext]['type']){
						case "ftp":
							$ftps = pc_base::load_sys_class('ftps');
							if ($ftps->connect($this->attachments[$fileext]['host'],$this->attachments[$fileext]['username'], $this->attachments[$fileext]['password'],$this->attachments[$fileext]['port'],$this->attachments[$fileext]['pasv'], $this->attachments[$fileext]['ssl'])) {
								if(!$ftps->get_error()){
									$_path=$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir);
									$_path_s 	= explode("/",$_path); // 取目录数组
									array_pop($_path_s);
									$_path_list = implode("/",$_path_s); //弹出文件名
		
									$ftps->mkdir($_path_list);
									$ftps->put($this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename),$uploaddir.$filename);
									#@unlink($savefile);
								}
							$ftps->close();	
							unset($ftps);
							}
						break;
						case "cos":
                            pc_base::vendor('include','cos','attachment'); //加载第三方类
                            #$bucket = $this->attachments[$fileext]['bucket'];
                            define('APP_ID',$this->attachments[$fileext]['appid']);
                            define('SECRET_ID',$this->attachments[$fileext]['username']);
                            define('SECRET_KEY',$this->attachments[$fileext]['password']);
                            define('API_COSAPI_END_POINT',$this->attachments[$fileext]['host']);

                            qcloudcos\Cosapi::setTimeout(180);
                            qcloudcos\Cosapi::setRegion($this->attachments[$fileext]['area']);
                            $ret =  qcloudcos\Cosapi::upload($this->attachments[$fileext]['bucket'],$uploaddir.$filename,$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename));

                            #pc_base::load_app_class('qcloudcos','attachment',0);//载入qcloud cos类
                            #define('APPID',$this->attachments[$fileext]['appid']);
                            #define('SECRET_ID',$this->attachments[$fileext]['username']);
                            #define('SECRET_KEY',$this->attachments[$fileext]['password']);
                            #Cosapi::upload($this->upload_root.$filepath,$this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$filepath);//上传文件

                            break;
						case "oss":
						 pc_base::vendor('autoload','oss','attachment'); //加载第三方类
						 $ossClient = new OSS\OssClient($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);	 
						 $ossClient->uploadFile($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename),$uploaddir.$filename);
						 $ossClient=NULL;						
/*						 pc_base::load_app_class('sdk','attachment',0);//载入OSS类
						 $oss_sdk_service = new ALIOSS($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);
						 $oss_sdk_service->set_debug_mode(true); //设置是否打开curl调试模式
						 $oss_sdk_service->upload_file_by_file($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename),$uploaddir.$filename);		
						 $oss_sdk_service=NULL;	 */
						break;
						case "qn":
							
						pc_base::vendor('autoload','qiniu','attachment'); //加载第三方类库
						$auth = new Qiniu\Auth($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password']);	
						$token = $auth->uploadToken($this->attachments[$fileext]['bucket']);	
						$uploadMgr = new Qiniu\Storage\UploadManager();
						list($ret, $err) = $uploadMgr->putFile($token,$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename),$uploaddir.$filename);		
							
						#pc_base::load_app_class('qn','attachment',0);
						#$Qiniu=new SDK($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['bucket']);
						#$Qiniu->upload($uploaddir.$filename,$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename));
						#$Qiniu=NULL;
						break;
							
						case 'nos':
						pc_base::vendor('autoload','nos','attachment'); //加载第三方类	
						$NosClient=new NOS\NosClient($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);
						$NosClient->multiuploadFile($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename),$uploaddir.$filename);						
						break;
					}
				   if(!$this->attachments[$fileext]['stored']) @unlink($uploaddir.$filename);	
				}
				
				$downloadedfile = array('filename'=>$filename,'md5'=>md5_file($uploaddir.$filename),'filepath'=>preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir.$filename), 'filesize'=>filesize($uploaddir.$filename), 'fileext'=>$fileext);
				
				$aid = $this->add($downloadedfile);
				
				$this->downloadedfiles[$aid] = $filepath;
				
				
				if(!empty($this->attachments)&&is_array($this->attachments)===true&&array_key_exists($fileext,$this->attachments)){
				$datapath[]=str_replace($this->upload_root,'',$this->attachments[$fileext]['url'].$this->attachments[$fileext]['path'].$uploaddir.$filename);

				}else{
				
				$datapath[]=str_replace($this->upload_root,'',$annex[array_rand($annex)].$uploaddir.$filename);
				}
				
				
			}
			$string=str_replace($dataurl,$datapath,$string);
		}
		
		if(!preg_match_all("/(href|src|poster)=([\"|']?)([^ \"'>]+)\\2/is", $string, $matches))  return $value;
		//if(!preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $string, $matches))  return $value;
		
		$remotefileurls =$source= array();
		#file_put_contents(PHPCMS_PATH.'Tags.json',"source:".array2string($remotefileurls)."\n",FILE_APPEND);
		foreach(array_unique($matches[3]) as $matche)
		{
		#print_r($matche);
		#print_r($this->attach_setting);	
	
		if(substr($matche,0,2)== '//' && IsDomain(parse_url($matche)['host'])===true && strpos_arr($matche,$annex)=== false && strpos_arr($matche,$this->attach_setting)=== false ) {
			 $source['old'][] = $matche;
			 $source['new'][] = polling($matche).$matche; // $matche; //
			 $matche='http:'.$matche;
			}
			if(strpos($matche, '//')=== false && IsDomain(parse_url($matche)['host']) === false) continue;
			dir_create($uploaddir);
			$remotefileurls[$matche] = $this->fillurl($matche, $absurl, $basehref);
		}
		$value=str_replace($source['old'],$source['new'],$value); 
		unset($matches, $string);
		$remotefileurls = array_unique($remotefileurls);
		$oldpath = $newpath = array();
		
		foreach($remotefileurls as $k=>$file) {
			/*var_dump(strpos($file, '//') === false ||strpos_arr($file,$annex) !== false || strpos_arr($file,$this->attach_setting)!== false );
			exit;*/
			if(strpos($file, '//') === false ||strpos_arr($file,$annex) !== false || strpos_arr($file,$this->attach_setting)!== false ) continue;
			// TODO
			//if(IsDomain(parse_url($file)['host'])===true); $file=polling($file).$file;//补齐协议
			$Content_Type=get_headers($file,1);
			if(isset($Content_Type['Content-Type'])){
				$filename=explode('/',explode(';',trim($Content_Type['Content-Type']))[0])[1];		
				//file_put_contents(PHPCMS_PATH.'Tags.json',$filename.':文件类型'.$file."\n",FILE_APPEND);
				if (!preg_match("/($ext)/is",$filename)){
					if(!preg_match("/($ext)/is",fileext($file))){//解决content_type为空的问题
						file_put_contents(PHPCMS_PATH.'Tags.json',$filename.':退出'.$filename."\n",FILE_APPEND);
						continue;
					}
				}
			}
			// E_TODO
			$upload_func = "$this->_copy";//$this->upload_func;
			$data=$this->_copy($file);
			//print_r($data['info']);exit;
			if ($data['info']['http_code']==0) continue;
			$filename=str_replace('image/','',explode(';',trim($data['info']['content_type']))[0]);
			if (!preg_match("/($ext)/is",$filename)){
				if(!preg_match("/($ext)/is",fileext($file))){//解决content_type为空的问题
					continue;
				}
			}
			
			//$filename = fileext($file);// * 取得文件扩展名
			$file_name = basename($file);
			if(!$filename) $filename=fileext($file);
			if($filename=="jpeg") $filename="jpg";
			$filename = $this->getname($filename);
			
			$newfile = $uploaddir.$filename;
			// copy函数不可靠
			if(@file_put_contents($newfile,$data['data'])!==false) { //远程图片本地化 @copy($file['tmp_name'], $savefile)  $this->_copy($file,$newfile)
				$oldpath[] = $k;
				
				@chmod($newfile, 0644);
				$fileext = fileext($filename);
				$GLOBALS['downloadfiles'][] = $newpath[] = (!empty($this->attachments)&&is_array($this->attachments)===true&&array_key_exists($fileext,$this->attachments)) ? $this->attachments[$fileext]['url'].$this->attachments[$fileext]['path'].$dir.$filename :$annex[array_rand($annex)].$dir.$filename;//$uploadpath.$filename;
				if($thumb_enable) {
					$image = new image($thumb_enable,$this->siteid);//
					$image->thumb($newfile,$newfile,$thumb_setting[0],$thumb_setting[1]);
					clearstatcache(true,$newfile);
					$image=NULL;
				}
				if($watermark){
					watermark($newfile, $newfile,$this->siteid,$watermark);
				}
				$filepath = $dir.$filename;
				
				
				
				if(!empty($this->attachments)&&is_array($this->attachments)===true&&array_key_exists($fileext,$this->attachments)){
					
					switch($this->attachments[$fileext]['type']){
						case "ftp":
							$ftps = pc_base::load_sys_class('ftps');
							if ($ftps->connect($this->attachments[$fileext]['host'],$this->attachments[$fileext]['username'], $this->attachments[$fileext]['password'],$this->attachments[$fileext]['port'],$this->attachments[$fileext]['pasv'], $this->attachments[$fileext]['ssl'])) {
								if(!$ftps->get_error()){
									$_path=$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$uploaddir);
									$_path_s 	= explode("/",$_path); // 取目录数组
									array_pop($_path_s);
									$_path_list = implode("/",$_path_s); //弹出文件名
		
									$ftps->mkdir($_path_list);
									$ftps->put($this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$newfile),$newfile);
									#@unlink($savefile);
								}
							$ftps->close();	
							unset($ftps);
							}
						break;
						case "cos":
                            pc_base::vendor('include','cos','attachment'); //加载第三方类
                            #$bucket = $this->attachments[$fileext]['bucket'];
                            define('APP_ID',$this->attachments[$fileext]['appid']);
                            define('SECRET_ID',$this->attachments[$fileext]['username']);
                            define('SECRET_KEY',$this->attachments[$fileext]['password']);
                            define('API_COSAPI_END_POINT',$this->attachments[$fileext]['host']);

                            qcloudcos\Cosapi::setTimeout(180);
                            qcloudcos\Cosapi::setRegion($this->attachments[$fileext]['area']);
                            $ret =  qcloudcos\Cosapi::upload($this->attachments[$fileext]['bucket'],$this->upload_root.$filepath,$this->attachments[$fileext]['path'].$filepath);

                            #pc_base::load_app_class('qcloudcos','attachment',0);//载入qcloud cos类
                            #define('APPID',$this->attachments[$fileext]['appid']);
                            #define('SECRET_ID',$this->attachments[$fileext]['username']);
                            #define('SECRET_KEY',$this->attachments[$fileext]['password']);
                            #Cosapi::upload($this->upload_root.$filepath,$this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$filepath);//上传文件

                            break;
						case "oss":
						 pc_base::vendor('autoload','oss','attachment'); //加载第三方类
						 $ossClient = new OSS\OssClient($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);	 
						 $ossClient->uploadFile($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$filepath,$this->upload_root.$filepath);
						 $ossClient=NULL;						
/*						 pc_base::load_app_class('sdk','attachment',0);//载入OSS类
						 $oss_sdk_service = new ALIOSS($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);
						 $oss_sdk_service->set_debug_mode(true); //设置是否打开curl调试模式
						 $oss_sdk_service->upload_file_by_file($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].preg_replace(new_addslashes("|^".$this->upload_root."|"), "",$newfile),$newfile);		
						 $oss_sdk_service=NULL;	*/ 
						break;
						case "qn":
						pc_base::vendor('autoload','qiniu','attachment'); //加载第三方类库
						$auth = new Qiniu\Auth($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password']);	
						$token = $auth->uploadToken($this->attachments[$fileext]['bucket']);	
						$uploadMgr = new Qiniu\Storage\UploadManager();
						list($ret, $err) = $uploadMgr->putFile($token,$this->attachments[$fileext]['path'].$filepath,$this->upload_root.$filepath);	
							
						break;
							
						case 'nos':
						pc_base::vendor('autoload','nos','attachment'); //加载第三方类	
						$NosClient=new NOS\NosClient($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);
						$NosClient->multiuploadFile($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$filepath,$this->upload_root.$filepath);	
							
						break;
					}
				   if(!$this->attachments[$fileext]['stored']) @unlink($savefile);
					
				}				
				$downloadedfile = array('filename'=>$filename,'md5'=>md5_file($newfile),'filepath'=>$filepath, 'filesize'=>filesize($newfile), 'fileext'=>$fileext);
				$aid = $this->add($downloadedfile);
				$this->downloadedfiles[$aid] = $filepath;
			}
		}

		return str_replace($oldpath, $newpath,str_replace($dataurl,$datapath,$value)); //二次替换
	}	
	/**
	 * 附件删除方法
	 * @param $where 删除sql语句
	 */
	function delete($where) {
		$this->att_db = pc_base::load_model('attachment_model');
		$result = $this->att_db->select($where);
		foreach($result as $r) {
			$image = $this->upload_root.$r['filepath'];
			if(file_exists($image)) @unlink($image);
			$thumbs = glob(dirname($image).'/*'.basename($image));
			if($thumbs!==false) foreach($thumbs as $thumb) if(file_exists($thumb))@unlink($thumb);
			
			$fileext=$r['fileext'];
			
			if(!empty($this->attachments)&&is_array($this->attachments)===true&&array_key_exists($fileext,$this->attachments)){
					switch($this->attachments[$fileext]['type']){
						case "ftp":
							$ftps = pc_base::load_sys_class('ftps');
							if ($ftps->connect($this->attachments[$fileext]['host'],$this->attachments[$fileext]['username'], $this->attachments[$fileext]['password'],$this->attachments[$fileext]['port'],$this->attachments[$fileext]['pasv'], $this->attachments[$fileext]['ssl'])) {
							if(!$ftps->get_error()){
								$ftps->f_delete($this->attachments[$fileext]['path'].$r['filepath']);
								}
							$ftps->close();	
							unset($ftps);
							}
						break;
						case "cos":
                            pc_base::vendor('include','cos','attachment'); //加载第三方类
                            define('APP_ID',$this->attachments[$fileext]['appid']);
                            define('SECRET_ID',$this->attachments[$fileext]['username']);
                            define('SECRET_KEY',$this->attachments[$fileext]['password']);
                            define('API_COSAPI_END_POINT',$this->attachments[$fileext]['host']);
                            qcloudcos\Cosapi::setTimeout(180);
                            qcloudcos\Cosapi::setRegion($this->attachments[$fileext]['area']);
                            $ret =  qcloudcos\Cosapi::delFile($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$r['filepath']);
                            file_put_contents(PHPCMS_PATH.'Tags.json',"source:".array2string($ret)."\n",FILE_APPEND);
                            break;
						case "oss":
							pc_base::vendor('autoload','oss','attachment'); //加载第三方类
							 $ossClient = new OSS\OssClient($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);	 
							 #$ossClient->uploadFile($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$filepath,$this->upload_root.$filepath);
							 $ossClient->deleteObject($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$r['filepath']);
							 $ossClient=NULL;
						break;
						case "qn":
                            pc_base::vendor('autoload','qiniu','attachment'); //加载第三方类库
                            $auth = new Qiniu\Auth($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password']);
                            $bucketMgr = new Qiniu\Storage\BucketManager($auth);
                            $bucketMgr->delete($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$r['filepath']);
							#pc_base::load_app_class('qn','attachment',0);
							#$Qiniu=new SDK($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['bucket']);
							#$Qiniu->delete($this->attachments[$fileext]['path'].$r['filepath']);
							#$Qiniu=NULL;
                        break;
                        case 'nos':
                            pc_base::vendor('autoload','nos','attachment'); //加载第三方类
                            $NosClient=new NOS\NosClient($this->attachments[$fileext]['username'],$this->attachments[$fileext]['password'],$this->attachments[$fileext]['host']);
                            $NosClient->deleteObject($this->attachments[$fileext]['bucket'],$this->attachments[$fileext]['path'].$r['filepath']);
						break;
					}
				}
		}
		return $this->att_db->delete($where);
	}
	
	/**
	 * 附件添加如数据库
	 * @param $uploadedfile 附件信息
	 */
	function add($uploadedfile) {
		$this->att_db = pc_base::load_model('attachment_model');
		$uploadedfile['module'] = $this->module;
		$uploadedfile['catid'] = $this->catid;
		$uploadedfile['siteid'] = $this->siteid;
		$uploadedfile['userid'] = $this->userid;
		$uploadedfile['uploadtime'] = SYS_TIME;
		$uploadedfile['uploadip'] = ip();
		$uploadedfile['status'] = pc_base::load_config('system','attachment_stat') ? 0 : 1;
		$uploadedfile['authcode'] = md5($uploadedfile['filepath']);
		$uploadedfile['filename'] = strlen($uploadedfile['filename'])>49 ? $this->getname($uploadedfile['fileext']) : $uploadedfile['filename'];
		$uploadedfile['isimage'] = in_array($uploadedfile['fileext'], $this->imageexts) ? 1 : 0;
		
		$aid = $this->att_db->api_add($uploadedfile);//上传记录ID
		
		$this->uploadedfiles[] = $uploadedfile;
		return $aid;
	}
	
	function set_userid($userid) {
		$this->userid = $userid;
	}
	/**
	 * 获取缩略图地址..
	 * @param $image 图片路径
	 */
	function get_thumb($image){
		return str_replace('.', '_thumb.', $image);
	}


	/**
	 * 获取附件名称
	 * @param $fileext 附件扩展名
	 */
	function getname($fileext){
		return md5(date('Ymdhis').rand(100, 999)).'.'.$fileext;
	}

	/**
	 * 返回附件大小
	 * @param $filesize 图片大小
	 */
	
	function size($filesize) {
		if($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
		} elseif($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize . ' Bytes';
		}
		return $filesize;
	}
	/**
	* 判断文件是否是通过 HTTP POST 上传的
	*
	* @param	string	$file	文件地址
	* @return	bool	所给出的文件是通过 HTTP POST 上传的则返回 TRUE
	*/
	function isuploadedfile($file) {
		return is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file));
	}
	
	/**
	* 补全网址
	*
	* @param	string	$surl		源地址
	* @param	string	$absurl		相对地址
	* @param	string	$basehref	网址
	* @return	string	网址
	*/
	function fillurl($surl, $absurl, $basehref = '') {
		if($basehref != '') {
			$preurl = strtolower(substr($surl,0,6));
			if($preurl=='http:/' || $preurl=='ftp://' || $preurl=='https:' ||$preurl=='mms://' || $preurl=='rtsp:/' || $preurl=='thunde' || $preurl=='emule://'|| $preurl=='ed2k://')
			return  $surl;
			else
			return $basehref.'/'.$surl;
		}
		$i = 0;
		$dstr = '';
		$pstr = '';
		$okurl = '';
		$pathStep = 0;
		$surl = trim($surl);
		if($surl=='') return '';
		$urls = @parse_url(SITE_URL);
		$HomeUrl = $urls['host'];
		$BaseUrlPath = $HomeUrl.$urls['path'];
		$BaseUrlPath = preg_replace("/\/([^\/]*)\.(.*)$/",'/',$BaseUrlPath);
		$BaseUrlPath = preg_replace("/\/$/",'',$BaseUrlPath);
		$pos = strpos($surl,'#');
		if($pos>0) $surl = substr($surl,0,$pos);
		if($surl[0]=='/') {
			$okurl = 'http://'.$HomeUrl.'/'.$surl;
		} elseif($surl[0] == '.') {
			if(strlen($surl)<=2) return '';
			elseif($surl[0]=='/') {
				$okurl = 'http://'.$BaseUrlPath.'/'.substr($surl,2,strlen($surl)-2);
			} else {
				$urls = explode('/',$surl);
				foreach($urls as $u) {
					if($u=="..") $pathStep++;
					else if($i<count($urls)-1) $dstr .= $urls[$i].'/';
					else $dstr .= $urls[$i];
					$i++;
				}
				$urls = explode('/', $BaseUrlPath);
				if(count($urls) <= $pathStep)
				return '';
				else {
					$pstr = 'http://';
					for($i=0;$i<count($urls)-$pathStep;$i++) {
						$pstr .= $urls[$i].'/';
					}
					$okurl = $pstr.$dstr;
				}
			}
		} else {
			$preurl = strtolower(substr($surl,0,6));
			if(strlen($surl)<7)
			$okurl = 'http://'.$BaseUrlPath.'/'.$surl;
			elseif($preurl=="http:/"||$preurl=="https:"||$preurl=='ftp://' ||$preurl=='mms://' || $preurl=="rtsp:/" || $preurl=='thunde' || $preurl=='emule:'|| $preurl=='ed2k:/')
			$okurl = $surl;
			else
			$okurl = 'http://'.$BaseUrlPath.'/'.$surl;
		}
		$preurl = strtolower(substr($okurl,0,6));
		if($preurl=='ftp://' || $preurl=='mms://' || $preurl=='rtsp:/' || $preurl=='thunde' || $preurl=='emule:'|| $preurl=='ed2k:/') {
			return $okurl;
		} else {
			
			#$okurl = preg_replace('/^(http:\/\/)/i','',$okurl);
			#$okurl = preg_replace('/\/{1,}/i','/',$okurl);
			$okurl =  substr($okurl,0,2).preg_replace(array('/^(\w+):\/\//i','/\/{2,}/','/~/'), array('$1~','/','://'), substr($okurl,2));
			
			return $okurl;//'http://'.$okurl;
		}
	}

	/**
	 * 是否允许上传
	 */
	function is_allow_upload() {
        if($_groupid == 1) return true;
		$starttime = SYS_TIME-86400;
		$site_setting = $this->_get_site_setting($this->siteid);
		return ($uploads < $site_setting['upload_maxsize']);
	}
	/**
	 * 检测图片是否合法
	 */
	
	function isImage($filename){
		$types = '.gif|.jpeg|.png|.bmp';//定义检查的图片类型
		if(file_exists($filename)){
			$info = getimagesize($filename);
			$ext = image_type_to_extension($info['2']);
			return stripos($types,$ext);
		}else{
			return false;
		}
	}	
	/**
	 * 返回错误信息
	 */
	function error() {
		$UPLOAD_ERROR = array(
		0 => L('att_upload_succ'),
		1 => L('att_upload_limit_ini'),
		2 => L('att_upload_limit_filesize'),
		3 => L('att_upload_limit_part'),
		4 => L('att_upload_nofile'),
		5 => '',
		6 => L('att_upload_notemp'),
		7 => L('att_upload_temp_w_f'),
		8 => L('att_upload_create_dir_f'),
		9 => L('att_upload_dir_permissions'),
		10 => L('att_upload_limit_ext'),
		11 => L('att_upload_limit_setsize'),
		12 => L('att_upload_not_allow'),
		13 => L('att_upload_limit_time'),
		14 => L('att_upload_not_allow_scanv'),
		);
		
		return iconv(CHARSET,"utf-8",$UPLOAD_ERROR[$this->error]);
	}
	
	/**
	 * ck编辑器返回
	 * @param $fn 
	 * @param $fileurl 路径
	 * @param $message 显示信息
	 */
	
	function mkhtml($fn,$fileurl,$message) {
		$str='<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$fn.', \''.$fileurl.'\', \''.$message.'\');</script>';
		exit($str);
	}
	/**
	 * flash上传调试方法
	 * @param $id
	 */
	function uploaderror($id = 0)	{
		file_put_contents(PHPCMS_PATH.'xxx.txt', $id);
	}
	
	/**
	 * 获取站点配置信息
	 * @param  $siteid 站点id
	 */
	private function _get_site_setting($siteid) {
		$siteinfo = getcache('sitelist', 'commons');
		return string2array($siteinfo[$siteid]['setting']);
	}
	
	/**
	*	00:00:00 时间转秒
	*/
	private static function timeToSec($time) 
	{
		  $p = explode(':',$time);
		  $c = count($p);
		  if ($c>1)
		  {
			   $hour    = intval($p[0]);
			   $minute  = intval($p[1]);
			   $sec     = intval($p[2]);
		  }
		  else
		  {
		  	 	throw new Exception('error time format');
		  }
		  $secs = $hour * 3600 + $minute * 60 + $sec;
		  return $secs;	 
	}
	/**
	*	获取视频信息
	*/
	private function getVideoInfo($file){
	        $re = array();
			exec("/usr/local/ffmpeg/bin/ffmpeg -i {$file} 2>&1",$re);

	        $info = implode("\n", $re);
			
			if(preg_match("/No such file or directory/i", $info))
			{
				return false;
			}
			
		    if(preg_match("/Invalid data/i", $info)){
	            return false;
	        }
        
	        $match = array();
	        preg_match("/\d{2,}x\d+/", $info, $match);
	        list($width, $height) = explode("x",$match[0]);
        
	        $match = array();
	        preg_match("/Duration:(.*?),/", $info, $match);
			if($match)
			{
	        	$duration = date("H:i:s", strtotime($match[1]));
        	}else
			{
				$duration = NULL;
			}	
				
	        $match = array();
	        preg_match("/bitrate:(.*kb\/s)/", $info, $match);
	        $bitrate = $match[1];
        
	        if(!$width && !$height && !$duration && !$bitrate){
	            return false;
	        }else{
	            return array(
					"file" => $file,
	                "width" => $width,
	                "height" => $height,
	                "duration" => $duration,
	                "bitrate" => $bitrate,
					"secends" => $this->timeToSec($duration)
	            );
	        }
	    }
	
	
			
	private function _copy($file){
		$array=array();
		$_UserAgent=array('Baiduspider-news+(+http://www.baidu.com/search/spider.htm)','Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)','Googlebot-Image/1.0',"Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)","Sosoimagespider+(+http://help.soso.com/soso-image-spider.htm)");
		//$CURLOPT_HTTPHEADER=array(array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.8.8'),array('X-FORWARDED-FOR:123.125.71.85', 'CLIENT-IP:123.125.71.85'),array('X-FORWARDED-FOR:123.125.71.85', 'CLIENT-IP:123.125.71.85'));//随机
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$file);
		curl_setopt($curl,CURLOPT_HEADER, 0);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST, "GET");
		//curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_AUTOREFERER,1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,10);
		
		if (stripos($file, "https://") !== FALSE) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		//curl_setopt($curl, CURLOPT_HEADER, TRUE);
		//curl_setopt($curl, CURLOPT_NOBODY, TRUE);
		//echo $CURLOPT_HTTPHEADER[array_rand($CURLOPT_HTTPHEADER)];
		curl_setopt($curl,CURLOPT_USERAGENT,'Baiduspider-news+(+http://www.baidu.com/search/spider.htm)');//$_SERVER['HTTP_USER_AGENT'] //随机 $_UserAgent[array_rand($_UserAgent)]
		//curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; c8650 Build/GWK74) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1s');
		// 运行cURL，请求网页
		$array['data']=$data = curl_exec($curl);
		
		if(!curl_errno($curl)){
			$array['info']=$info = curl_getinfo($curl);
			//print_r($array['info']);
		}
		//file_put_contents(PHPCMS_PATH.'Tags.json',$file.':'.array2string($array)."\n",FILE_APPEND);
		curl_close($curl);
		return $array;//TRUE; 	返回数组	
	}
	
	
	function __destruct(){
		ini_set('memory_limit','64M');
	}
	
}
?>