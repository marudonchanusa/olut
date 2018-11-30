<?php /* Smarty version 2.6.9, created on 2005-10-19 14:07:00
         compiled from ArrivalOfGoodsForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'ArrivalOfGoodsForm.tpl', 24, false),array('modifier', 'escape', 'ArrivalOfGoodsForm.tpl', 121, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>入荷伝票入力</title>
<link rel="stylesheet" href="css/arrival_of_goods.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body class="body">

<form method="post" name="olut">

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
<p align="center"><b>入荷伝票入力</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border=0 width=10 cellpadding=2>
<TR>
  <TD nowrap align="right">
    日付
  </TD>
  <TD nowrap>
    <?php if ($this->_tpl_vars['year'] == ''): ?>
    <input type="text" class="hankaku" name="year" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
" size="6" maxlength="4" onkeypress="javascript:digitonly();"  <?php echo $this->_tpl_vars['readonly']; ?>
>年
    <input type="text" class="hankaku" name="month" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m")); ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();"  <?php echo $this->_tpl_vars['readonly']; ?>
>月
    <input type="text" class="hankaku" name="date" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d") : smarty_modifier_date_format($_tmp, "%d")); ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();"  <?php echo $this->_tpl_vars['readonly']; ?>
>日
    <?php else: ?>
    <input type="text" class="hankaku" name="year" value="<?php echo $this->_tpl_vars['year']; ?>
" size="6" maxlength="8" onkeypress="javascript:digitonly();"  <?php echo $this->_tpl_vars['readonly']; ?>
>年
    <input type="text" class="hankaku" name="month" value="<?php echo $this->_tpl_vars['month']; ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();"  <?php echo $this->_tpl_vars['readonly']; ?>
>月
    <input type="text" class="hankaku" name="date" value="<?php echo $this->_tpl_vars['date']; ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();"  <?php echo $this->_tpl_vars['readonly']; ?>
>日
    <?php endif; ?>   
  </TD>
</tr>
<tr>
  <td nowrap align="right">
   伝票番号
  </td>
  <td nowrap align="left">
    <input type="text" class="hankaku" name="slip_no" value="<?php echo $this->_tpl_vars['slip_no']; ?>
" size="8" maxlength="7" onkeypress="javascript:digitonly();" <?php echo $this->_tpl_vars['readonly']; ?>
>
  </td>

</tr>
<tr>
  <td nowrap align="right">
   倉庫
  </td>
  <td nowrap align="left">
  <?php if ($this->_tpl_vars['readonly'] != ''): ?>
    <select name="warehouse_code" disabled> <?php echo $this->_tpl_vars['warehouse_list']; ?>
 </select>
  <?php else: ?>
    <select name="warehouse_code"> <?php echo $this->_tpl_vars['warehouse_list']; ?>
 </select>
  <?php endif; ?>
  </td>

</tr>
</TABLE>
  <hr size="1">

</td>
<tr><td>

<div align="center">

<table border=0 bgcolor="#808080" cellpadding="2">

<?php if ($this->_tpl_vars['error'] != ""): ?>
    <tr>
        <td bgcolor="yellow" colspan="2">
          <?php echo $this->_tpl_vars['error']; ?>

        </td>
    </tr>
<?php endif; ?>
    
<tr><td>

  <table border="0" cellpadding="1">
    <tr>
      <td class="header_cell" nowrap width="1%">　</td>
      <td class="header_cell" nowrap width="10%" align="center">商品No</td>
      <td class="header_cell" width="10%"  align="center">商品名</td>
      <td class="header_cell" width="10%"  align="center">仕入取引先</td>
      <td class="header_cell" width="30%"  align="center">仕入取引先名</td>
      <td class="header_cell" width="5%"    align="center">数量</td>
      <td class="header_cell" nowrap width="10%" align="center">単位</td>
      <td class="header_cell" nowrap width="5%" align="center">単価</td>
      <td class="header_cell" width="10%"  align="center">金額</td>
      <td class="header_cell" width="10%" align="center">支払区分</td>
      <td class="header_cell" width="10%" align="center">入庫区分</td>
      <td class="header_cell" width="10%" align="center">メモ</td>
      
    </tr>
    
    <!-- ここからループ -->
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
      <td nowrap class="data_cell" align="left" width="100">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['commodity_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" align="center" width="10%">
        <input type="text" class="hankaku" name="vendor_code_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['vendor_code'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="8" maxlength="5" onkeypress="javascript:digitonly();">
        <input type="submit" name="vendor_ref_<?php echo $this->_tpl_vars['n']; ?>
" value="H"> 
      </td>
      <td nowrap class="data_cell" width="30%"><?php echo ((is_array($_tmp=$this->_tpl_vars['vendor_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
      <td nowrap class="data_cell" width="5%"><input type="text" class="money" name="amount_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['amount'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="4" maxlength="8" onkeypress="javascript:moneyonly();"></td>
      <td nowrap class="data_cell" width="60"> <?php echo ((is_array($_tmp=$this->_tpl_vars['unit_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
      <td nowrap class="data_cell" width="5%" align=right>
        <input type="hidden" name="unit_price_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['unit_price'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="8" maxlength="8">
        <?php echo $this->_tpl_vars['unit_price'][$this->_sections['item']['index']]; ?>

      </td>
      <td nowrap class="data_cell" width="60">

        <input type="text" class="money" name="total_price_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['total_price'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="8" maxlength="9" onBlur="javascript:blur_submit(this.form,this);"  onkeypress="javascript:digit_and_sign();" >
        

      </td>
      <td nowrap class="data_cell" width="10%"><select name="payment_flag_<?php echo $this->_tpl_vars['n']; ?>
"><?php echo $this->_tpl_vars['payment_flag_list'][$this->_sections['item']['index']]; ?>
</select></td>
      <td nowrap class="data_cell" width="10%"><select name="act_flag_<?php echo $this->_tpl_vars['n']; ?>
"><?php echo $this->_tpl_vars['act_flag_list'][$this->_sections['item']['index']]; ?>
</select></td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" name="memo_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['memo'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="20" maxlength="255">
      </td>
      
    </tr>
    <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
    <?php endfor; endif; ?>

  </table>
</td>
</table>

  </div>

</td>
<tr><td>

  <hr size="1">
</td>
</table>  
  <input type="hidden" name="modify_mode" value="<?php echo $this->_tpl_vars['modify_mode']; ?>
">
  <input type="hidden" name="blur_flag" value="">
  <input type="submit" name="check" value="　確認　" style="width:80px;">
  <INPUT TYPE="SUBMIT" VALUE="保存" name="save" style="width:80px;">
  <hr size="1">
  <!-- </form>
  <form method=post action="OlutArrivalOfGoodsMenu.php">
  -->
  <input type="button" value="メニューへ戻る" name="cancel" onclick="javascript:check_menuback_for_arrival(this.form);">
  </form>

</table>
</body>



</html>
