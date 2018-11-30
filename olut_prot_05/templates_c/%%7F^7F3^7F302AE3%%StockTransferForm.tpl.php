<?php /* Smarty version 2.6.9, created on 2005-10-19 13:18:36
         compiled from StockTransferForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'StockTransferForm.tpl', 21, false),array('modifier', 'escape', 'StockTransferForm.tpl', 95, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>入荷移動入力</title>
<link rel="stylesheet" href="css/shipment.css" type="text/css" />
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
<p align="center"><b>入荷移動入力</b>
<form method="POST">

<hr size="0">
<center>
<table border="0" width="90%">
<tr><td colspan="2">

<TABLE border=0 cellpadding=2>
<tr>
 <td  nowrap colspan=2> <b><?php echo $this->_tpl_vars['warehouse_name_from']; ?>
</b> から <b><?php echo $this->_tpl_vars['warehouse_name_to']; ?>
</b> への倉庫移動入力 </td>
</tr>

<TR>
<TD nowrap width=20%>
日付
</TD>
<TD nowrap colspan="2">
    <?php if ($this->_tpl_vars['year'] == ''): ?>
    <input type="text" class="hankaku" name="year" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
" size="6" maxlength="4" onkeypress="javascript:digitonly();">年
    <input type="text" class="hankaku" name="month" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m")); ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d") : smarty_modifier_date_format($_tmp, "%d")); ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();">日
    <?php else: ?>
    <input type="text" class="hankaku" name="year" value="<?php echo $this->_tpl_vars['year']; ?>
" size="6" maxlength="8" onkeypress="javascript:digitonly();">年
    <input type="text" class="hankaku" name="month" value="<?php echo $this->_tpl_vars['month']; ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="<?php echo $this->_tpl_vars['date']; ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();">日
    <?php endif; ?>  
</TD>
</tr>

</TABLE>
<hr size="1">

</td>
<tr><td>

<div align="center">

<table border=0 bgcolor="#808080" cellpadding="1" width="90%">

<?php if ($this->_tpl_vars['error'] != ""): ?>
    <tr>
        <td bgcolor="yellow" colspan="2">
          <?php echo $this->_tpl_vars['error']; ?>

        </td>
    </tr>
<?php endif; ?>

<tr><td>

  <table border="0" cellpadding="2" width="100%">
    <tr>
      <td nowrap class="header_cell" width="1%">　</td>
      <td nowrap class="header_cell" width="10%">商品No</td>
      <td class="header_cell"  width="100">商品名</td>
      <td class="header_cell"  width="10%">数量</td>
      <td nowrap class="header_cell" width="10%">単位</td>
      <td nowrap class="header_cell" width="10%">単価</td>
      <td class="header_cell" width="10%" nowrap>金額</td>
    </tr>
    
    <!-- start of block -->
        
    <?php $this->assign('n', 0); ?>
    <?php unset($this->_sections['item']);
$this->_sections['item']['name'] = 'item';
$this->_sections['item']['start'] = (int)0;
$this->_sections['item']['loop'] = is_array($_loop=10) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
      <td nowrap class="left_number_cell" align="right" width="1%">&nbsp;<?php echo $this->_sections['item']['index']+1; ?>
</td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" class="hankaku" name="commodity_code_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['commodity_code'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="7" maxlength="5" onkeypress="javascript:digitonly();">
        <input type="submit" name="commodity_ref_<?php echo $this->_tpl_vars['n']; ?>
" value="H"> 
      </td>
      <td nowrap class="data_cell"><?php echo ((is_array($_tmp=$this->_tpl_vars['commodity_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
      <td nowrap class="data_cell"" width="10%" align="center">
        <input type="text" class="money" name="amount_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['amount'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="4" maxlength="8" onkeypress="javascript:digit_and_sign();">
      </td>
      <td nowrap class="data_cell" width="10%" align="center">
      <?php echo ((is_array($_tmp=$this->_tpl_vars['unit_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" width="10%" align="right">
      <input type="text" class="money" name="unit_price_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['unit_price'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="8" maxlength="8" onkeypress="javascript:moneyonly();">
      </td>
      <td nowrap class="data_cell" width="10%" align="center">
         <input type="text" class="money" name="total_price_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['total_price'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="8" maxlength="9" onkeypress="javascript:digit_and_sign();">
      </td>
    </tr>
    <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
    <?php endfor; endif; ?>
    <!-- end of block -->
  </table>
</td>
</table>

</div>

</td>
<tr><td colspan="2">
  <hr size="1">
</td>
</table>  
<br>
  <input type="submit" name="check" value="　確認　" style="width:80px;">
  <input type="submit" name="move" value="　移動　" style="width:80px;">
</form>

<hr size="1">
<FORM ACTION="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メイン・メニューへ戻る"><BR>
</FORM>
<center>
</td>
</table>
</center>
</body>
</html>