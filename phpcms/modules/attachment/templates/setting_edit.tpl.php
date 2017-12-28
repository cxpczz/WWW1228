<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({formid:"myform",autotip:true,onerror:function(msg,obj){window.top.art.dialog({content:msg,lock:true,width:'200',height:'50'}, function(){this.close();$(obj).focus();})}});
		$("#name").formValidator({onshow:"<?php echo L('input').L('attachments_name')?>",onfocus:"<?php echo L('input').L('attachments_name')?>"}).inputValidator({min:1,onerror:"<?php echo L('input').L('attachments_name')?>"}).ajaxValidator({type : "get",url : "<?php echo U('attachment/setting/public_name',array('id'=>$id));?>",data :"",datatype : "html",async:'false',success : function(data){	if( data == "1" ){return true;}else{return false;}},buttons: $("#dosubmit"),onerror : "<?php echo L('attachments_name').L('exists')?>",onwait : "<?php echo L('connecting')?>"}).defaultPassed();
		$("#host").formValidator({onshow:"<?php echo L('input').L('server_address')?>",onfocus:"<?php echo L('input').L('server_address')?>"}).inputValidator({min:1,onerror:"<?php echo L('input').L('server_address')?>"});
		$("#port").formValidator({onshow:"<?php echo L('input').L('server_port')?>",onfocus:"<?php echo L('input').L('server_port')?>"}).inputValidator({min:1,onerror:"<?php echo L('input').L('server_port')?>"}).regexValidator({datatype:'enum',regexp:'intege1',onerror:'<?php echo L('server_ports_must_be_integers')?>'});
		$("#username").formValidator({onshow:"<?php echo L('input').L('username')?>",onfocus:"<?php echo L('input').L('username')?>"}).inputValidator({min:1,onerror:"<?php echo L('input').L('username')?>"});
		$("#password").formValidator({onshow:"<?php echo L('input').L('password')?>",onfocus:"<?php echo L('input').L('password')?>"}).inputValidator({min:1,onerror:"<?php echo L('input').L('password')?>"});
		
	})
//-->
</script>
<div class="pad-10">
<form action="<?php echo U('attachment/setting/edit',array('id'=>$id));?>" method="post" id="myform">
<fieldset>
	<legend><?php echo L('basic_configuration')?></legend>
	<table width="100%"  class="table_form">
  <tr>
    <th width="80"><?php echo L('attachments_name')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="name" id="name" size="30" value="<?php echo $data['name']?>" /></td>
  </tr>
</table>
</fieldset>
<div class="bk15"></div>
<fieldset>
	<legend><?php echo L('ftp_server')?></legend>
	<table width="100%"  class="table_form">
   <tr>
    <th><?php echo L('Storagetype')?>：</th>
    <td class="y-bg"><?php echo form::select($this->source,$data['type'],'name="type" ',L('please_select').L('Storagetype'));?></td>
  </tr>
   <tr>
    <th><?php echo L('site_att_allow_ext')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="ext" id="ext" size="30" value="<?php echo $data['ext']?>" /></td>
  </tr>
  <tr>
    <th width="80"><?php echo L('server_address')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="host" id="host" size="40" value="<?php echo $data['host']?>" /></td>
  </tr>
   <tr>
    <th width="80"><?php echo L("appid")?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="appid" id="appid" size="30" value="<?php echo $data['appid']?>" /></td>
  </tr>  
   <tr>
    <th width="80"><?php echo L("server_port")?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="port" id="port" size="30" value="<?php echo $data['port']?>" /></td>
  </tr>
  <tr>
    <th><?php echo L('username')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="username" id="username" size="40" value="<?php echo $data['username']?>" /></td>
  </tr>
    <tr>
    <th><?php echo L('password')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="password" id="password" size="40"  value="<?php echo $data['password']?>"/></td>
  </tr>
  <tr>
    <th><?php echo L('bucket')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="bucket" id="bucket" size="40" value="<?php echo $data['bucket']?>" /></td>
  </tr>
        <tr>
            <th><?php echo L('area')?>：</th>
            <td class="y-bg"><input type="text" class="input-text" name="area" id="area" size="40" value="<?php echo $data['area']?>" /></td>
        </tr>
        <tr>
    <th><?php echo L('path')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="path" id="path" size="30" value="<?php echo $data['path']?>" /></td>
  </tr>
    <tr>
    <th><?php echo L('url')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="url" id="url" size="40" value="<?php echo $data['url']?>" /></td>
  </tr>
   <tr>
    <th><?php echo L('stored')?>：</th>
    <td class="y-bg"><label><input type="checkbox" class="inputcheckbox" name="stored" value="1" id="stored" size="30" <?php if ($data['stored']){echo ' checked';}?> /><?php echo L('yes')?></label></td>
  </tr>
  <tr>
    <th><?php echo L('passive_mode')?>：</th>
    <td class="y-bg"><label><input type="checkbox" class="inputcheckbox" name="pasv" id="pasv" value="1" size="30"<?php if ($data['pasv']){echo ' checked';}?> /><?php echo L('yes')?></label></td>
  </tr>
    <tr>
    <th><?php echo L('ssl_connection')?>：</th>
    <td class="y-bg"><label><input type="checkbox" class="inputcheckbox" name="ssl" id="ssl" value="1" size="30"<?php if ($data['ssl']){echo ' checked';}?> <?php if(!$this->ssl){ echo 'disabled';}?> /><?php echo L('yes')?></label> <?php if(!$this->ssl){ echo '<span style="color:red">'.L('your_server_will_not_support_the_ssl_connection').'</a>';}?></td>
  </tr>

    <tr id="test"<?php if($data['type']!='ftp'){?> style="display:none;"<?php } ?>>
    <th><?php echo L('test_connections')?>：</th>
    <td class="y-bg"><input type="button" class="button" onclick="ftp_test()" value="<?php echo L('test_connections')?>" /></td>
  </tr>
</table>
</fieldset>
<div class="bk15"></div>
    <input type="submit" class="dialog" id="dosubmit" name="dosubmit" value="<?php echo L('submit')?>" />

<script type="text/javascript">
<!--
function ftp_test() {
	if(!$.formValidator.isOneValid('host')) {
		$('#host').focus();
		return false;
	}
	if(!$.formValidator.isOneValid('port')) {
		$('#port').focus();
		return false;
	}
	if(!$.formValidator.isOneValid('username')) {
		$('#username').focus();return false;
	}
	if(!$.formValidator.isOneValid('password')) {
		$('#password').focus();return false;
	}
	var host = $('#host').val();
	var port = $('#port').val();
	var username = $('#username').val();
	var password = $('#password').val();
	var pasv = $("input[type='checkbox'][name='pasv']:checked").val();
	var ssl = $("input[type='checkbox'][name='ssl']:checked").val();
	$.get("<?php echo U('attachment/setting/public_test_ftp');?>",{host:host,port:port,username:username,password:password,pasv:pasv,ssl:ssl}, function(data){
		if (data==1){
			alert('<?php echo L('ftp_server_connections_success')?>');
		} else {
			alert(data);
		}
	})
}
//-->
</script>
</form>

</div>
</body>
</html>