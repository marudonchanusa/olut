<?php /* Smarty version 2.6.9, created on 2005-09-28 19:28:50
         compiled from CommodityMasterForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'CommodityMasterForm.tpl', 23, false),array('modifier', 'escape', 'CommodityMasterForm.tpl', 63, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title> ���ʥޥ������ƥʥ�</title>
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
        <td>DATE��</td>
        <td><font size="3"><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<center>
<b>���ʥޥ������ƥʥ�</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>ɽ������</B><br>
<input type="submit" name="clear" value="������Ͽ">
<input type="submit" name="prev" value="����ɽ��">
<input type="submit" name="next" value="��ɽ����"> 
 ���ʥ����ɡ�<input type="text" class="hankaku" name="scode" value=""  size="10" maxlength="5">
<input type="submit" name="search" value="ɽ��">
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
      <td nowrap class="name_cell">���ʥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['code'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="6" maxlength="5" onkeypress="javascript:digitonly();" <?php if ($this->_tpl_vars['post']['code'] != ''): ?> readonly <?php endif; ?>></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">����̾��</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">����̾�Ρʥ��ʡ�</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name_k'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">ʪή�����ʬ</td>
      <td nowrap class="input_cell"><select name="dist_flag"><?php echo $this->_tpl_vars['dist_flag_list']; ?>
</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">����ñ��</td>
      <td nowrap class="input_cell"><select name="unit_code"><?php echo $this->_tpl_vars['unit_code_list']; ?>
</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">����ñ��</td>
      <td nowrap class="input_cell"><select name="order_unit_code"><?php echo $this->_tpl_vars['order_unit_code_list']; ?>
</select></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">����ñ�������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="order_amount" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['order_amount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="6" maxlength="5"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����å�ñ��</td>
      <td nowrap class="input_cell"><select name="lot_unit_code"><?php echo $this->_tpl_vars['lot_unit_code_list']; ?>
</select></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����å������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="lot_amount" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['lot_amount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="6" maxlength="5"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">��������вٸ�</td>
      <td nowrap class="input_cell"><select name="ship_section_code"><?php echo $this->_tpl_vars['ship_section_code_list']; ?>
</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">�����ʲ��ܥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="accd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['accd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="4" maxlength="2"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����ʺ��ܥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="itmcd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['itmcd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="4" maxlength="2"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">����ʬ�ॳ����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="com_class_code" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['com_class_code'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="9" maxlength="5" onkeypress="javascript:digitonly();"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">�ؼ����ʬ</td>
      <td nowrap class="input_cell"><select name="order_sheet_flag"><?php echo $this->_tpl_vars['order_sheet_flag_list']; ?>
</select></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">�߸˰����ʬ</td>
      <td nowrap class="input_cell"><select name="stock_flag"><?php echo $this->_tpl_vars['stock_flag_list']; ?>
</select></td>
    </tr>
    <tr>
      <td nowrap class="name_cell">���߽в�ñ��</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="current_unit_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['current_unit_price'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="12" maxlength="10" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">���в�ñ��</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="next_unit_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['current_unit_price'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="12" maxlength="10" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">JAN������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="jancd" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['jancd'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="30" maxlength="20" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">���׽����ե饰</td>
      <td nowrap class="input_cell"><select name="depletion_flag"> <?php echo $this->_tpl_vars['depletion_flag_list']; ?>
" </select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">��������</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="note" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['note'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="50" maxlength="255"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">��������(���ʡ�</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="note_k" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['note_k'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="50" maxlength="255"></td>
    </tr>
       
  </table>
</td>
</table>
  <br>
  <input type="submit" name="reload" value="������ɡ�" <?php if ($this->_tpl_vars['post']['code'] == ''): ?> disabled <?php endif; ?>>
  <input type="submit" name="save" value="���ݡ�¸��">     
  <input type="submit" name="delete" value="  �����" onclick="return confirm('������Ƶ������Ǥ�����');" <?php if ($this->_tpl_vars['post']['code'] == ''): ?> disabled <?php endif; ?>>   
<hr size="1">
</form>
<form method=post action="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="��˥塼�����" >
</FORM>

</center>

</td>
</table>
</center>

