<?php
defined('IN_PHPCMS') or exit('The resource access forbidden.');
pc_base::load_app_class('admin','admin',0);
pc_base::load_sys_class('form','',0);
//附件设置类
class setting extends admin {
    private $db,$source;
    public $ssl = 0;
    public function __construct() {
        parent::__construct();
        $this->db = pc_base::load_model('setting_model','attachment');
        $this->source=array('ftp'=>'远程FTP存储','oss'=>'阿里云OSS','cos'=>'腾讯COS','qn'=>'七牛云存储','nos'=>'网易云存储');
       # print_r($this->source);
        if (function_exists('ftp_ssl_connect')) {
            $this->ssl = 1;
        }
    }
    public function init() {
        $list = $this->db->select();
        //print_r($list);
        $big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\''.U('attachment/setting/add').'\', title:\''.L('attachments_add').'\', width:\'700\', height:\'700\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', L('attachments_add'));
        include $this->admin_tpl('setting_list');
    }

    public function add() {
        if (isset($_POST['dosubmit'])) {
            $name = isset($_POST['name']) && trim($_POST['name']) ? trim($_POST['name']) : showmessage(L('attachments_name').L('empty'));
            $host = isset($_POST['host']) && trim($_POST['host']) ? trim($_POST['host']) : showmessage(L('server_address').L('empty'));
            $port = isset($_POST['port']) && intval($_POST['port']) ? intval($_POST['port']) : showmessage(L('server_port').L('empty'));
            $username = isset($_POST['username']) && trim($_POST['username']) ? trim($_POST['username']) : showmessage(L('username').L('empty'));
            $password = isset($_POST['password']) && trim($_POST['password']) ? trim($_POST['password']) : showmessage(L('password').L('empty'));
            $path = isset($_POST['path']) && trim($_POST['path']) ? trim($_POST['path']) : "";
            $type = isset($_POST['type']) && trim($_POST['type']) ? trim($_POST['type']) : showmessage(L('Storagetype').L('empty'));

            $ext = isset($_POST['ext']) && trim($_POST['ext']) ? trim($_POST['ext']) : showmessage(L('ext').L('empty'));
            $url = isset($_POST['url']) && trim($_POST['url']) ? trim($_POST['url']) : showmessage(L('url').L('empty'));
            $pasv = isset($_POST['pasv']) && trim($_POST['pasv']) ? trim($_POST['pasv']) : 0;
            $ssl = isset($_POST['ssl']) && trim($_POST['ssl']) ? trim($_POST['ssl']) : 0;
            $stored = isset($_POST['stored']) && trim($_POST['stored']) ? trim($_POST['stored']) :1;
            $bucket = isset($_POST['bucket']) && trim($_POST['bucket']) ? trim($_POST['bucket']) : '';
            $area = isset($_POST['area']) && trim($_POST['area']) ? trim($_POST['area']) : '';
            $appid = isset($_POST['appid']) && trim($_POST['appid']) ? trim($_POST['appid']) : '';
            if ($this->db->get_one(array("name"=>$name))) {
                showmessage(L('attachments_name').L('exists'));
            }
            if ($this->db->insert(array('name'=>$name,'host'=>$host,'port'=>$port,'area'=>$area,'username'=>$username, 'password'=>$password, 'path'=>$path, 'pasv'=>$pasv, 'ssl'=>$ssl, 'ext'=>$ext,'appid'=>$appid,'type'=>$type,'bucket'=>$bucket, 'url'=>$url,'stored'=>$stored))) {

                //更新缓存
                $this->setcache();
                showmessage(L('operation_success'), '', '', 'add');
            } else {
                showmessage(L('operation_failure'));
            }
        }


        $show_header = $show_validator = true;
        include $this->admin_tpl('setting_add');
    }

    public function setcache(){
        $list = $this->db->select();
        $array = array();
        foreach ($list as $r) {
            if($r['ext']!==''){
                $ext=explode('|',$r['ext']);
                foreach ($ext as $e) {
                    unset($r['ext']);
                    $array[$e]=$r;
                }
            }
        }
        setcache('attachments_var',$array,'attachments');
        unset($array);
        foreach ($list as $r) {
            $array[]=$r['url'];
        }
        setcache('attach_setting',$array,'attachments');
    }


    public function edit() {
        $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : showmessage(L('illegal_parameters'), HTTP_REFERER);
        if ($data = $this->db->get_one(array('id'=>$id))) {
            if (isset($_POST['dosubmit'])) {
                $name = isset($_POST['name']) && trim($_POST['name']) ? trim($_POST['name']) : showmessage(L('attachments_name').L('empty'));
                $host = isset($_POST['host']) && trim($_POST['host']) ? trim($_POST['host']) : showmessage(L('server_address').L('empty'));
                $port = isset($_POST['port']) && intval($_POST['port']) ? intval($_POST['port']) : showmessage(L('server_port').L('empty'));
                $username = isset($_POST['username']) && trim($_POST['username']) ? trim($_POST['username']) : showmessage(L('username').L('empty'));
                $password = isset($_POST['password']) && trim($_POST['password']) ? trim($_POST['password']) : showmessage(L('password').L('empty'));
                $path = isset($_POST['path']) && trim($_POST['path']) ? trim($_POST['path']) : "";
                $type = isset($_POST['type']) && trim($_POST['type']) ? trim($_POST['type']) : showmessage(L('Storagetype').L('empty'));
                $ext = isset($_POST['ext']) && trim($_POST['ext']) ? trim($_POST['ext']) : showmessage(L('ext').L('empty'));
                $url = isset($_POST['url']) && trim($_POST['url']) ? trim($_POST['url']) : showmessage(L('url').L('empty'));
                $pasv = isset($_POST['pasv']) && trim($_POST['pasv']) ? trim($_POST['pasv']) : 0;
                $ssl = isset($_POST['ssl']) && trim($_POST['ssl']) ? trim($_POST['ssl']) : 0;
                $stored = isset($_POST['stored']) && trim($_POST['stored']) ? trim($_POST['stored']) : 1;
                $bucket = isset($_POST['bucket']) && trim($_POST['bucket']) ? trim($_POST['bucket']) : '';
                $area = isset($_POST['area']) && trim($_POST['area']) ? trim($_POST['area']) : '';
                $appid = isset($_POST['appid']) && trim($_POST['appid']) ? trim($_POST['appid']) : '';
                if ($data['name'] != $name && $this->db->get_one(array("name"=>$name))) {
                    showmessage(L('attachments_name').L('exists'));
                }
                if ($this->db->update(array('name'=>$name,'host'=>$host,'port'=>$port,'area'=>$area,'username'=>$username, 'password'=>$password, 'path'=>$path, 'pasv'=>$pasv, 'ssl'=>$ssl, 'ext'=>$ext,'type'=>$type,'appid'=>$appid,'bucket'=>$bucket, 'url'=>$url, 'stored'=>$stored), array('id'=>$id))) {

                    //更新缓存
                    $this->setcache();

                    showmessage(L('operation_success'), '', '', 'edit');
                } else {
                    showmessage(L('operation_failure'));
                }
            }
            $show_header = $show_validator = true;
            include $this->admin_tpl('setting_edit');
        } else {
            showmessage(L('notfound'), HTTP_REFERER);
        }
    }

    public function public_name() {
        $name = isset($_GET['name']) && trim($_GET['name']) ? (pc_base::load_config('system', 'charset') == 'gbk' ? iconv('utf-8', 'gbk', trim($_GET['name'])) : trim($_GET['name'])) : exit('0');
        $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : '';
        $data = array();
        if ($id) {
            $data = $this->db->get_one(array('id'=>$id), 'name');
            if (!empty($data) && $data['name'] == $name) {
                exit('1');
            }
        }
        if ($this->db->get_one(array('name'=>$name), 'id')) {
            exit('0');
        } else {
            exit('1');
        }
    }

    public function del() {
        $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : showmessage(L('illegal_parameters'), HTTP_REFERER);
        if ($this->db->get_one(array('id'=>$id))) {
            if ($this->db->delete(array('id'=>$id))) {
                //更新缓存
                $this->setcache();
                showmessage(L('operation_success'), HTTP_REFERER);
            } else {
                showmessage(L('operation_failure'), HTTP_REFERER);
            }
        } else {
            showmessage(L('notfound'), HTTP_REFERER);
        }
    }

    public function public_test_ftp() {
        $host = isset($_GET['host']) && trim($_GET['host']) ? trim($_GET['host']) : exit('0');
        $port = isset($_GET['port']) && intval($_GET['port']) ? intval($_GET['port']) : exit('0');
        $username = isset($_GET['username']) && trim($_GET['username']) ? trim($_GET['username']) : exit('0');
        $password = isset($_GET['password']) && trim($_GET['password']) ? trim($_GET['password']) : exit('0');
        $pasv = isset($_GET['pasv']) && trim($_GET['pasv']) ? trim($_GET['pasv']) : 0;
        $ssl = isset($_GET['ssl']) && trim($_GET['ssl']) ? trim($_GET['ssl']) : 0;
        $ftp = pc_base::load_sys_class('ftps');
        if ($ftp->connect($host, $username, $password, $port,$pasv,$ssl, 25)) {
            if ($ftp->link_time > 15) {
                exit(L('ftp_connection_a_long_time'));
            }
            exit('1');
        } else {
            exit(L('can_ftp_server_connections'));
        }
    }
}