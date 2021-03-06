<?php
defined('IN_PHPCMS') or exit('No permission resources.');
$session_storage = 'session_' . pc_base::load_config('system', 'session_storage');
pc_base::load_sys_class($session_storage);
if (param::get_cookie('sys_lang')) {
    define('SYS_STYLE', param::get_cookie('sys_lang'));
} else {
    define('SYS_STYLE', 'zh-cn');
}

class attachments
{

    private $att_db;

    // var $oss_enable;
    var $attachments;

    var $attach_setting;

    var $count_swfdownload_invoke = 0;
    
    private  $log_attachment;

    function __construct()
    {
        
        // logger
        $this->log_attachment= Logger::getLogger(__CLASS__);
        
        pc_base::load_app_func('global');
        $this->upload_url = pc_base::load_config('system', 'upload_url');
        $this->annex = pc_base::load_config('system', 'annex');
        $this->upload_path = pc_base::load_config('system', 'upload_path');
        $this->imgext = array(
            'jpg',
            'gif',
            'png',
            'bmp',
            'jpeg'
        );
        // 远程文件上传
        $this->attachments = getcache('attachments_var', 'attachments');
        $this->attach_setting = getcache('attach_setting', 'attachments');
        // $this->userid = $_SESSION['userid'] ? $_SESSION['userid'] : (param::get_cookie('_userid') ? param::get_cookie('_userid') : sys_auth($_POST['userid_flash'],'DECODE'));
        $this->userid = param::get_cookie('userid') ? param::get_cookie('userid') : param::get_cookie('_userid');
        $this->isadmin = $this->admin_username = $_SESSION['roleid'] ? 1 : 0;
        $this->groupid = param::get_cookie('_groupid') ? param::get_cookie('_groupid') : 8;
        
        // $this->admin_username = $_SESSION['roleid'] ? param::get_cookie('admin_username') : '';
        // 判断是否登录
        /*
         * if(empty($this->userid)){
         * showmessage(L('please_login','','member'));
         * }
         */
    }

    /**
     * 常规上传
     */
    public function upload()
    {
        $grouplist = getcache('grouplist', 'member');
        if ($this->isadmin == 0 && ! $grouplist[$this->groupid]['allowattachment'])
            return false;
        pc_base::load_sys_class('attachment', '', 0);
        
        $module = trim($_GET['module']);
        $catid = intval($_GET['catid']);
        $siteid = $this->get_siteid();
        $site_setting = get_site_setting($siteid);
        $site_allowext = $site_setting['upload_allowext'];
        $attachment = new attachment($module, $catid, $siteid);
        $attachment->set_userid($this->userid);
        $a = $attachment->upload('upload', $site_allowext);
        if ($a) {
            $filepath = $attachment->uploadedfiles[0]['filepath'];
            $fn = $attachment->uploadedfiles[0]['fn'];
            $fileext = $attachment->uploadedfiles[0]['fileext'];
            if (! empty($this->attachments) && is_array($this->attachments) === true && array_key_exists($fileext, $this->attachments)) {
                if ($this->attachments[$fileext]['type'] == 'ftp') {
                    $this->upload_json($a[0], $this->attachments[$fileext]['url'] . $filepath, $attachment->uploadedfiles[0]['filename']);
                    $attachment->mkhtml($fn, $this->attachments[$fileext]['url'] . $filepath, '');
                } else {
                    $this->upload_json($a[0], $this->attachments[$fileext]['url'] . $this->attachments[$fileext]['path'] . $filepath, $attachment->uploadedfiles[0]['filename']);
                    $attachment->mkhtml($fn, $this->attachments[$fileext]['url'] . $this->attachments[$fileext]['path'] . $filepath, '');
                }
            } else {
                $this->upload_json($a[0], $this->annex[array_rand($this->annex)] . $filepath, $attachment->uploadedfiles[0]['filename']);
                $attachment->mkhtml($fn, $this->annex[array_rand($this->annex)] . $filepath, '');
            }
        }
        $attachment = NULL;
    }

    /**
     * html5上传
     */
    public function html5upload()
    {
        $grouplist = getcache('grouplist', 'member');
        // echo trim($_GET['filed']);exit;
        if ($this->isadmin == 0 && ! $grouplist[$this->groupid]['allowattachment'])
            return false;
        pc_base::load_sys_class('attachment', '', 0);
        $module = trim($_GET['module']);
        $filed = trim($_GET['filed']);
        $catid = intval($_GET['catid']);
        
        $siteid = $this->get_siteid();
        $site_setting = get_site_setting($siteid);
        $site_allowext = $site_setting['upload_allowext'];
        $attachment = new attachment($module, $catid, $siteid);
        $attachment->set_userid($this->userid);
        $a = $attachment->upload($filed, $site_allowext);
        
        // print_r($site_allowext);exit;
        if ($a) {
            $filepath = $attachment->uploadedfiles[0]['filepath'];
            $fn = $attachment->uploadedfiles[0]['fn'];
            $fileext = $attachment->uploadedfiles[0]['fileext'];
            $this->upload_json($a[0], $this->annex[array_rand($this->annex)] . $filepath, $attachment->uploadedfiles[0]['filename']);
            $fileext = $attachment->uploadedfiles[0]['fileext'];
            
            if (! empty($this->attachments) && is_array($this->attachments) === true && array_key_exists($fileext, $this->attachments)) {
                echo $this->attachments[$fileext]['url'] . $this->attachments[$fileext]['path'] . $filepath;
            } else {
                echo $this->annex[array_rand($this->annex)] . $filepath;
            }
            
            // echo $this->annex[array_rand($this->annex)].$filepath;
        } else {
            echo '0|' . $attachment->error();
        }
        $attachment = NULL;
    }

    /**
     * swfupload上传附件
     */
    public function swfupload()
    {
        $grouplist = getcache('grouplist', 'member');
        //$this->log->info('$gruoplist:' . $grouplist);
        
        if (isset($_POST['dosubmit'])) {
            
            // 验证上传文件格式是否合规
            $siteid = get_siteid();
            $site_setting = get_site_setting($siteid);
            $site_allowext = $site_setting['upload_allowext'];
            
            
            //if ($_POST['swf_auth_key'] != md5(pc_base::load_config('system', 'auth_key') . $_POST['SWFUPLOADSESSID']) || ($_POST['isadmin'] == 0 && ! $grouplist[$_POST['groupid']]['allowattachment']))
                //exit();
                
            pc_base::load_sys_class('attachment','',0);
       
            //$this->log_attachment->info('$_POSTcatid:' . $_POST['catid']);
            //$this->log_attachment->info('$_POSTsiteid:' . $_POST['siteid']);
            
            $attachment = new attachment($_POST['module'], $_POST['catid'], $_POST['siteid']);
            
            $attachment->set_userid($_POST['userid']);
            
            //$this->log_attachment->info('userid:'.$_POST['userid']);
            
            $aids = $attachment->upload('Filedata', $_POST['filetype_post'], '', '', array(
                $_POST['thumb_width'],
                $_POST['thumb_height']
            ), $_POST['watermark_enable']);
            
            $this->log_attachment->info('$aids:' . $aids);
            $allowext_array = explode('|', $site_allowext);
            if (! in_array($attachment->uploadedfiles[0]['fileext'], $allowext_array)) {
                exit('0');
            }
            if ($aids[0]) {
                $filename = (strtolower(CHARSET) != 'utf-8') ? iconv('gbk', 'utf-8', $attachment->uploadedfiles[0]['filename']) : $attachment->uploadedfiles[0]['filename'];
                if ($attachment->uploadedfiles[0]['isimage']) {
                    $fileext = $attachment->uploadedfiles[0]['fileext'];
                    if (! empty($this->attachments) && is_array($this->attachments) === true && array_key_exists($fileext, $this->attachments)) {
                        if ($this->attachments[$fileext]['type'] == 'ftp') {
                            echo $aids[0] . ',' . $this->attachments[$fileext]['url'] . $attachment->uploadedfiles[0]['filepath'] . ',' . $attachment->uploadedfiles[0]['isimage'] . ',' . $filename; // 本地存储
                        } else {
                            echo $aids[0] . ',' . $this->attachments[$fileext]['url'] . $this->attachments[$fileext]['path'] . $attachment->uploadedfiles[0]['filepath'] . ',' . $attachment->uploadedfiles[0]['isimage'] . ',' . $filename; // 本地存储
                        }
                    } else {
                        echo $aids[0] . ',' . $this->annex[array_rand($this->annex)] . $attachment->uploadedfiles[0]['filepath'] . ',' . $attachment->uploadedfiles[0]['isimage'] . ',' . $filename; // 本地存储
                    }
                    // }
                    
                    // echo $aids[0].','.$this->upload_url.$attachment->uploadedfiles[0]['filepath'].','.$attachment->uploadedfiles[0]['isimage'].','.$filename;
                } else {
                    $fileext = $attachment->uploadedfiles[0]['fileext'];
                    if ($fileext == 'zip' || $fileext == 'rar')
                        $fileext = 'rar';
                    elseif ($fileext == 'doc' || $fileext == 'docx')
                        $fileext = 'doc';
                    elseif ($fileext == 'xls' || $fileext == 'xlsx')
                        $fileext = 'xls';
                    elseif ($fileext == 'ppt' || $fileext == 'pptx')
                        $fileext = 'ppt';
                    elseif ($fileext == 'flv')
                        $fileext = 'flv';
                    elseif ($fileext == 'mp4')
                        $fileext = 'mp4';
                    elseif ($fileext == 'swf')
                        $fileext = 'swf';
                    elseif ($fileext == 'rm' || $fileext == 'rmvb')
                        $fileext = 'rmvb';
                    else
                        $fileext = 'do';
                    
                    if (! empty($this->attachments) && is_array($this->attachments) === true && array_key_exists($fileext, $this->attachments)) {
                        if ($this->attachments[$fileext]['type'] == 'ftp') {
                            echo $aids[0] . ',' . $this->attachments[$fileext]['url'] . $attachment->uploadedfiles[0]['filepath'] . ',' . $fileext . ',' . $filename;
                        } else {
                            echo $aids[0] . ',' . $this->attachments[$fileext]['url'] . $this->attachments[$fileext]['path'] . $attachment->uploadedfiles[0]['filepath'] . ',' . $fileext . ',' . $filename;
                        }
                    } else {
                        echo $aids[0] . ',' . $this->annex[array_rand($this->annex)] . $attachment->uploadedfiles[0]['filepath'] . ',' . $fileext . ',' . $filename;
                    }
                }
                $attachment = NULL;
                exit();
            } else {
                echo '0,' . $attachment->error();
                $attachment = NULL;
                exit();
            }
        } else {
            if ($this->isadmin == 0 && ! $grouplist[$this->groupid]['allowattachment'])
                showmessage(L('att_no_permission'));
            $args = $_GET['args'];
            // print_r($args);
            $authkey = $_GET['authkey'];
            // print_r($authkey);
            
            if (upload_key($args) != $authkey)
                showmessage(L('attachment_parameter_error'));
            // print_r(getswfinit($_GET['args']));
            extract(getswfinit($_GET['args']));
            $siteid = $this->get_siteid();
            $site_setting = get_site_setting($siteid);
            $file_size_limit = sizecount($site_setting['upload_maxsize'] * 1024);
            $att_not_used = param::get_cookie('att_json');
            if (empty($att_not_used) || ! isset($att_not_used))
                $tab_status = ' class="on"';
            if (! empty($att_not_used))
                $div_status = ' hidden';
            // 获取临时未处理文件列表
            $att = $this->att_not_used();
            $userid_flash = sys_auth($this->userid, 'ENCODE');
            include $this->admin_tpl('swfupload');
        }
    }

    public function crop_upload()
    {
        R_status(404);
        if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
            $pic = $GLOBALS["HTTP_RAW_POST_DATA"];
            if (isset($_GET['width']) && ! empty($_GET['width'])) {
                $width = intval($_GET['width']);
            }
            if (isset($_GET['height']) && ! empty($_GET['height'])) {
                $height = intval($_GET['height']);
            }
            if (isset($_GET['file']) && ! empty($_GET['file'])) {
                $_GET['file'] = str_ireplace(array(
                    ';',
                    'php'
                ), '', $_GET['file']);
                
                if (is_image($_GET['file']) == false || stripos($_GET['file'], '.php') !== false)
                    exit();
                if ($this->isImage($_GET['file']) === false)
                    exit();
                if (strpos($_GET['file'], pc_base::load_config('system', 'upload_url')) !== false || strpos_arr($_GET['file'], $this->annex) !== false) {
                    $file = $_GET['file'];
                    $basename = basename($file);
                    if (strpos($basename, 'thumb_') !== false) {
                        $file_arr = explode('_', $basename);
                        $basename = array_pop($file_arr);
                    }
                    $fileext = strtolower(fileext($basename));
                    if (! in_array($fileext, array(
                        'jpg',
                        'gif',
                        'jpeg',
                        'png',
                        'bmp'
                    )))
                        exit();
                    $new_file = 'thumb_' . $width . '_' . $height . '_' . $basename;
                } else {
                    pc_base::load_sys_class('attachment', '', 0);
                    $module = trim($_GET['module']);
                    $catid = intval($_GET['catid']);
                    $siteid = $this->get_siteid();
                    $attachment = new attachment($module, $catid, $siteid);
                    $uploadedfile['filename'] = basename($_GET['file']);
                    $uploadedfile['fileext'] = strtolower(fileext($_GET['file']));
                    if (in_array($uploadedfile['fileext'], array(
                        'jpg',
                        'gif',
                        'jpeg',
                        'png',
                        'bmp'
                    ))) {
                        $uploadedfile['isimage'] = 1;
                    }
                    $file_path = $this->upload_path . date('Y/md/');
                    pc_base::load_sys_func('dir');
                    dir_create($file_path);
                    $new_file = date('Ymdhis') . rand(100, 999) . '.' . $uploadedfile['fileext']; // 将文件重新命名
                    $uploadedfile['filepath'] = date('Y/md/') . $new_file;
                    $aid = $attachment->add($uploadedfile);
                    $attachment = NULL;
                }
                $filepath = date('Y/md/');
                file_put_contents($this->upload_path . $filepath . $new_file, $pic);
            } else {
                return false;
            }
            echo $this->annex[array_rand($this->annex)] . $filepath . $new_file;
            // echo pc_base::load_config('system', 'upload_url').$filepath.$new_file;
            exit();
        }
    }

    /**
     * 删除附件
     */
    public function swfdelete()
    {
        $attachment = pc_base::load_sys_class('attachment');
        $att_del_arr = explode('|', $_GET['data']);
        foreach ($att_del_arr as $n => $att) {
            if ($att)
                $attachment->delete(array(
                    'aid' => $att,
                    'userid' => $this->userid,
                    'uploadip' => ip()
                ));
        }
    }

    /**
     * 加载图片库
     */
    public function album_load()
    {
        if (! $this->admin_username)
            return false;
        $where = $uploadtime = '';
        $this->att_db = pc_base::load_model('attachment_model');
        if ($_GET['args'])
            extract(getswfinit($_GET['args']));
        if ($_GET['dosubmit']) {
            extract($_GET['info']);
            $where = '';
            $filename = safe_replace(antisqlin($filename));
            if ($filename)
                $where = "AND `filename` LIKE '%$filename%' ";
            if ($uploadtime) {
                $start_uploadtime = strtotime($uploadtime . ' 00:00:00');
                $stop_uploadtime = strtotime($uploadtime . ' 23:59:59');
                $where .= "AND `uploadtime` >= '$start_uploadtime' AND  `uploadtime` <= '$stop_uploadtime'";
            }
            if ($where)
                $where = substr($where, 3);
        }
        pc_base::load_sys_class('form');
        $page = $_GET['page'] ? $_GET['page'] : '1';
        $infos = $this->att_db->listinfo($where, 'aid DESC', $page, 8, '', 5);
        foreach ($infos as $n => $v) {
            $ext = fileext($v['filepath']);
            if (in_array($ext, $this->imgext)) {
                $infos[$n]['src'] = $this->annex[array_rand($this->annex)] . $v['filepath'];
                // $infos[$n]['src']=$this->upload_url.$v['filepath'];
                $infos[$n]['width'] = '80';
            } else {
                $infos[$n]['src'] = file_icon($v['filepath']);
                $infos[$n]['width'] = '64';
            }
        }
        $pages = $this->att_db->pages;
        include $this->admin_tpl('album_list');
    }

    /**
     * 目录浏览模式添加图片
     */
    public function album_dir()
    {
        if (! $this->admin_username)
            return false;
        if ($_GET['args'])
            extract(getswfinit($_GET['args']));
        $dir = isset($_GET['dir']) && trim($_GET['dir']) ? str_replace(array(
            '..\\',
            '../',
            './',
            '.\\',
            '..',
            '.*'
        ), '', trim($_GET['dir'])) : '';
        $filepath = $this->upload_path . $dir;
        $list = glob($filepath . '/' . '*');
        if (! empty($list))
            rsort($list);
        $local = str_replace(array(
            PC_PATH,
            PHPCMS_PATH,
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        ), array(
            '',
            '',
            DIRECTORY_SEPARATOR
        ), $filepath);
        $url = ($dir == '.' || $dir == '') ? $this->annex[array_rand($this->annex)] : $this->annex[array_rand($this->annex)] . str_replace('.', '', $dir) . '/';
        // $url = ($dir == '.' || $dir=='') ? $this->upload_url : $this->upload_url.str_replace('.', '', $dir).'/';
        $show_header = true;
        include $this->admin_tpl('album_dir');
    }

    /**
     * 设置upload上传的json格式cookie
     */
    private function upload_json($aid, $src, $filename)
    {
        $arr['aid'] = intval($aid);
        $arr['src'] = trim($src);
        $arr['filename'] = urlencode($filename);
        $json_str = json_encode($arr);
        $att_arr_exist = param::get_cookie('att_json');
        $att_arr_exist_tmp = explode('||', $att_arr_exist);
        if (is_array($att_arr_exist_tmp) && in_array($json_str, $att_arr_exist_tmp)) {
            return true;
        } else {
            $json_str = $att_arr_exist ? $att_arr_exist . '||' . $json_str : $json_str;
            param::set_cookie('att_json', $json_str);
            return true;
        }
    }

    /**
     * 设置swfupload上传的json格式cookie
     */
    public function swfupload_json()
    {
        $arr['aid'] = intval($_GET['aid']);
        $arr['src'] = safe_replace(trim($_GET['src']));
        // print_r($arr['src']);
        $arr['filename'] = urlencode(safe_replace($_GET['filename']));
        $json_str = json_encode($arr);
        $att_arr_exist = param::get_cookie('att_json');
        $att_arr_exist_tmp = explode('||', $att_arr_exist);
        if (is_array($att_arr_exist_tmp) && in_array($json_str, $att_arr_exist_tmp)) {
            return true;
        } else {
            $json_str = $att_arr_exist ? $att_arr_exist . '||' . $json_str : $json_str;
            param::set_cookie('att_json', $json_str);
            return true;
        }
    }

    /**
     * 删除swfupload上传的json格式cookie
     */
    public function swfupload_json_del()
    {
        $arr['aid'] = intval($_GET['aid']);
        $arr['src'] = trim($_GET['src']);
        $arr['filename'] = urlencode($_GET['filename']);
        $json_str = json_encode($arr);
        $att_arr_exist = param::get_cookie('att_json');
        $att_arr_exist = str_replace(array(
            $json_str,
            '||||'
        ), array(
            '',
            '||'
        ), $att_arr_exist);
        $att_arr_exist = preg_replace('/^\|\|||\|\|$/i', '', $att_arr_exist);
        param::set_cookie('att_json', $att_arr_exist);
    }

    private function att_not_used()
    {
        $this->att_db = pc_base::load_model('attachment_model');
        // 获取临时未处理文件列表
        if ($att_json = param::get_cookie('att_json')) {
            if ($att_json)
                $att_cookie_arr = explode('||', $att_json);
            foreach ($att_cookie_arr as $_att_c)
                $att[] = json_decode($_att_c, true);
            if (is_array($att) && ! empty($att)) {
                foreach ($att as $n => $v) {
                    $ext = fileext($v['src']);
                    if (in_array($ext, $this->imgext)) {
                        $att[$n]['fileimg'] = $v['src'];
                        $att[$n]['width'] = '80';
                        $att[$n]['filename'] = urldecode($v['filename']);
                    } else {
                        $att[$n]['fileimg'] = file_icon($v['src']);
                        $att[$n]['width'] = '64';
                        $att[$n]['filename'] = urldecode($v['filename']);
                    }
                    $this->cookie_att .= '|' . $v['src'];
                }
            }
        }
        return $att;
    }

    private function isImage($filename)
    {
        $types = '.gif|.jpeg|.png|.bmp'; // 定义检查的图片类型
        if (file_exists($filename)) {
            $info = getimagesize($filename);
            $ext = image_type_to_extension($info['2']);
            return stripos($types, $ext);
        } else {
            return false;
        }
    }

    final public static function admin_tpl($file, $m = '')
    {
        $m = empty($m) ? ROUTE_M : $m;
        if (empty($m))
            return false;
        return PC_PATH . 'modules' . DIRECTORY_SEPARATOR . $m . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file . '.tpl.php';
    }

    final public static function get_siteid()
    {
        return get_siteid();
    }
}
?>