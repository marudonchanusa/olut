<?php /* Smarty version 2.6.9, created on 2005-10-08 07:34:53
         compiled from VendorMasterForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'VendorMasterForm.tpl', 23, false),array('modifier', 'escape', 'VendorMasterForm.tpl', 63, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title>取引先マスタメンテナンス</title>
<link rel="stylesheet" href="css/master.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body>

<center>
<table border="0" width="90%">
<tr>
<td valign="top">
</td>
<td align="right">
    <table border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td>DATE：</td>
        <td><font size="3"><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<center>
<b>取引先マスタメンテナンス</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>表示選択</B><br>
<input type="submit" name="clear" value="新規登録">
<input type="submit" name="prev" value="←前表示">
<input type="submit" name="next" value="次表示→"> 
 取引先コード：<input type="text" class="hankaku" name="scode" value=""  size="10" maxlength="5">
<input type="submit" name="search" value="表示">
</td>
</table>
<hr size="0">

<table bgcolor="#808080">

    <?php if ($this->_tpl_vars['error'] != ""): ?>
        <tr>
            <td bgcolor="yellow" colspan="2">
              <?php echo $this->_tpl_vars['error']; ?>

            </td>
        </tr>
    <?php endif; ?>
    
<tr><td>

  <table border="0" cellpadding="5">
    <tr>
      <td nowrap class="name_cell">取引先コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['code'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="6" maxlength="5" onkeypress="javascript:digitonly();" <?php if ($this->_tpl_vars['post']['code'] != ''): ?> readonly <?php endif; ?>></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">チェックデジット</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="ckd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['ckd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">取引先名称</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">取引先名（カナ）</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name_k'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">内部コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="isdcd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['isdcd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="8" maxlength="6" onkeypress="javascript:digitonly();"></td>
    </tr>
    <tr>
    
      <td nowrap class="name_cell">電話番号</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="tel" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['tel'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="12" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">ファックス</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="fax" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['fax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="12" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">郵便番号</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="zip" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['zip'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="7" onkeypress="javascript:digitonly();"></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">住所</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="address" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['address'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="50" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">メールアドレス</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="mail_address" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['mail_address'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="50" maxlength="255"></td>
    </tr>
    <tr>
      <td nowrap class="name_cell">ウエブアドレス</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="web_url" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['web_url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="50" maxlength="255"></td>
    </tr>
        
  </table>
</td>
</table>
  <br>
  <input type="submit" name="reload" value="　リロード　" <?php if ($this->_tpl_vars['post']['code'] == ''): ?> disabled <?php endif; ?>>
  <input type="submit" name="save" value="　保　存　">     
  <input type="submit" name="delete" value="  削　除　" onclick="return confirm('削除して宜しいですか？');" <?php if ($this->_tpl_vars['post']['code'] == ''): ?> disabled <?php endif; ?>>   
<hr size="1">
</form>
<form method=post action="OlutMasterMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る" >
</FORM>

</center>

</td>
</table>
</center>

