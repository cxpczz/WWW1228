<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<div class="pad_10">
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
		<tr>
		<th width="80">ID</th>
		<th align="left" ><?php echo L('attachments_name')?></th>
        <th align="left" ><?php echo L('Storagetype')?></th>
        <th align="left" ><?php echo L('site_att_allow_ext')?></th>
		<th align="left" ><?php echo L('server_address')?></th>
		<th align="left" ><?php echo L("username")?></th>
        <th align="left" ><?php echo L("bucket")?></th>
		<th width="150"><?php echo L('operations_manage')?></th>
		</tr>
        </thead>
<tbody>
<?php 
if(is_array($list)):
//print_r($list);
	foreach($list as $v):
?>
<tr>
<td width="80" align="center"><?php echo $v['id']?></td>
<td><?php echo $v['name']?></td>
<td><?php echo $this->source[$v['type']]?></td>
<td><?php echo $v['ext']?></td>
<td><?php echo $v['host']?></td>
<td><?php echo $v['username']?></td>
<td><?php echo $v['bucket']?></td>
<td align="center" ><a href="javascript:edit(<?php echo $v['id']?>, '<?php echo new_addslashes($v['name'])?>')"><?php echo L('edit')?></a> | <a href="<?php echo U('attachment/setting/del',array('id'=>$v['id']))?>" onclick="return confirm('<?php echo new_addslashes(L('confirm', array('message'=>$v['name'])))?>')"><?php echo L('delete')?></a></td>
</tr>
<?php 
	endforeach;
endif;
?>
</tbody>
</table>
</div>
</div>
<div id="pages">	
	<?php
	if(!empty($pages)&&is_array($pages)){
		echo "<a class=\"a1\">$pages[page_item]条</a>";	
		echo "<a href='".$pages['previouspage']['pageurl']."' class='a1'>上一页</a>";
		if($pages['currpage'] != 1) echo "<a href='".$pages['fristpage']['pageurl']."'>".$pages['fristpage']['page']."</a>";
		if($pages['currpage']>6) echo "……";
		foreach($pages['pagedata'] as $k=>$p){
			if(isset($p['curr_page'])){
				echo "<span>$p[page]</span>";
			}else{
				echo "<a href='$p[pageurl]'>$p[page]</a>";
			}
		}
		if($pages['currpage'] < $pages['pages']-5) echo "……";
		if($pages['currpage'] != $pages['pages']) echo "<a href='".$pages['lastpage']['pageurl']."'>".$pages['lastpage']['page']."</a>";
		echo "<a href='".$pages['nextpage']['pageurl']."' class='a1'>下一页</a>";
	}
	
	?>
     </div>
<script type="text/javascript">
<!--
function edit(id, name) {
	window.top.art.dialog({id:'edit'}).close();
	window.top.art.dialog({title:'<?php echo L('attachments_edit')?>《'+name+'》',id:'edit',iframe:'<?php echo U('attachment/setting/edit')?>&id='+id,width:'700',height:'700'}, function(){var d = window.top.art.dialog({id:'edit'}).data.iframe;var form = d.document.getElementById('dosubmit');form.click();return false;}, function(){window.top.art.dialog({id:'edit'}).close()});
}
//-->
</script>
</body>
</html>