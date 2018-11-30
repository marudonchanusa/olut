<?php /* Smarty version 2.6.9, created on 2005-09-28 19:28:58
         compiled from StoreMasterForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'StoreMasterForm.tpl', 23, false),array('modifier', 'escape', 'StoreMasterForm.tpl', 63, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title> 店舗マスタメンテナンス</title>
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
<b>店舗マスタメンテナンス</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>表示選択</B><br>
<input type="submit" name="clear" value="新規登録">
<input type="submit" name="prev" value="←前表示">
<input type="submit" name="next" value="次表示→"> 
 取引先コード：<input type="text" class="hankaku" name="search_code" value=""  size="10" maxlength="5">
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
      <td nowrap class="name_cell">店舗コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['code'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="6" maxlength="5" onkeypress="javascript:digitonly();" <?php if ($this->_tpl_vars['post']['code'] != ''): ?> readonly <?php endif; ?>></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">チェックデジット</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="ckd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['ckd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">標準店舗コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="scode" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['scode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="6" maxlength="5" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">チェックデジット</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="sckd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['ckd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">店舗内部コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="isdcd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['isdcd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="18" maxlength="15" onkeypress="javascript:digitonly();"></td>
    </tr>    
    
    <tr>
      <td nowrap class="name_cell">店舗名称</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">店舗名称（カナ）</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name_k'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="50"></td>
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
      <td nowrap class="name_cell">請求書印字フラグ</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="print_flag" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['print_flag'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">開店年月日(YYYYMMDD)</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="open_date" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['open_date'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="9" maxlength="8" onkeypress="javascript:digitonly();"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">閉店年月日(YYYYMMDD)</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="close_date" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['close_date'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="9" maxlength="8" onkeypress="javascript:digitonly();"></td>
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
    
    <tr>
      <td colspan=2 >
      <table border="0" cellpadding="5" cellspacing=0 width=100%>
      <!-- ここからループ -->
      <?php $this->assign('n', 0); ?>
      <?php unset($this->_sections['item']);
$this->_sections['item']['name'] = 'item';
$this->_sections['item']['start'] = (int)0;
$this->_sections['item']['loop'] = is_array($_loop=5) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['item']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['item']['show'] = true;
$this->_sections['item']['max'] = $this->_sections['item']['loop'];
if ($this->_sections['item']['start'] < 0)
    $this->_sections['item']['start'] = max($this->_sections['item']['step'] > 0 ? 0 : -1, $this->_sections['item']['loop'] + $this->_sections['item']['start']);
else
    $this->_sections['item']['start'] = min($this->_sections['item']['start'], $this->_sections['item']['step'] > 0 ? $this->_sections['item']['loop'] : $this->_sections['item']['loop']-1);
if ($this->_sections['item']['show']) {
    $this->_sections['item']['total'] = min(ceil(($this->_sections['item']['step'] > 0 ? $this->_sections['item']['loop'] - $this->_sections['item']['start'] : $this->_sections['item']['start']+1)/abs($this->_sections['item']['step'])), $this->_sections['item']['max']);
    if ($this->_sections['item']['total'] == 0)
        $this->_sections['item']['show'] = false;
} else
    $this->_sections['item']['total'] = 0;
if ($this->_sections['item']['show']):

            for ($this->_sections['item']['index'] = $this->_sections['item']['start'], $this->_sections['item']['iteration'] = 1;
                 $this->_sections['item']['iteration'] <= $this->_sections['item']['total'];
                 $this->_sections['item']['index'] += $this->_sections['item']['step'], $this->_sections['item']['iteration']++):
$this->_sections['item']['rownum'] = $this->_sections['item']['iteration'];
$this->_sections['item']['index_prev'] = $this->_sections['item']['index'] - $this->_sections['item']['step'];
$this->_sections['item']['index_next'] = $this->_sections['item']['index'] + $this->_sections['item']['step'];
$this->_sections['item']['first']      = ($this->_sections['item']['iteration'] == 1);
$this->_sections['item']['last']       = ($this->_sections['item']['iteration'] == $this->_sections['item']['total']);
?>
      <tr>
        <td nowrap class="name_cell" width=25%>店舗内部門<?php echo $this->_tpl_vars['n']+1; ?>
</td>
        <td nowrap class="input_cell" width=25%><select name="store_section_<?php echo $this->_tpl_vars['n']; ?>
"><?php echo $this->_tpl_vars['store_section_list'][$this->_tpl_vars['n']]; ?>
</select></td>
        <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
        <td nowrap class="name_cell" width=25%>店舗内部門<?php echo $this->_tpl_vars['n']+1; ?>
</td>
        <td nowrap class="input_cell" width=25%><select name="store_section_<?php echo $this->_tpl_vars['n']; ?>
"><?php echo $this->_tpl_vars['store_section_list'][$this->_tpl_vars['n']]; ?>
</select></td>
        <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
        <td nowrap class="name_cell" width=25%>店舗内部門<?php echo $this->_tpl_vars['n']+1; ?>
</td>
        <td nowrap class="input_cell" width=25%><select name="store_section_<?php echo $this->_tpl_vars['n']; ?>
"><?php echo $this->_tpl_vars['store_section_list'][$this->_tpl_vars['n']]; ?>
</select></td>
        <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>        
      </tr>
      <?php endfor; endif; ?>
      </table>
      </td>
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
<form method=post action="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る" >
</FORM>

</center>

</td>
</table>
</center>

