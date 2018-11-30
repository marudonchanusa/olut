<?php /* Smarty version 2.6.9, created on 2005-09-28 18:58:47
         compiled from ShipmentDisplayForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'ShipmentDisplayForm.tpl', 21, false),array('modifier', 'escape', 'ShipmentDisplayForm.tpl', 108, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>出荷伝票表示</title>
<link rel="stylesheet" href="css/shipment.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body class="body">
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
<p align="center"><b>出荷伝票表示</b>
<form method="POST" method=post>

<hr size="0">
<center>
<table border="0" width="90%">
<tr><td colspan="2">

<TABLE border=0 width=10 cellpadding=2>

<tr>
<td nowrap>伝票番号</td>
<td nowrap>
<input type="text" name="slip_no" class="hankaku" value="<?php echo $this->_tpl_vars['slip_no']; ?>
" size=10 maxlength=7 onkeypress="javascript:digitonly();">
<input type="submit" name="find" value=" 表示開始 ">
<input type="submit" name="prev" value="　前　">
<input type="submit" name="next" value="　次　">

</td>
</tr>

<TR>
  <TD nowrap align="right">店舗</TD>
  <td nowrap><?php echo $this->_tpl_vars['store_name']; ?>
</td>
</tr>
<TR>
<TD nowrap align="right">部門<BR></TD>
<TD nowrap><?php echo $this->_tpl_vars['store_section_name']; ?>

</TD>

<TR>
<TD nowrap align="right">
日付
</TD>
<TD nowrap colspan="2">
<?php if ($this->_tpl_vars['year'] != ""):  echo $this->_tpl_vars['year']; ?>
年<?php echo $this->_tpl_vars['month']; ?>
月<?php echo $this->_tpl_vars['date']; ?>
日
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
      <td class="header_cell"  width="15%">区分</td>
      <td class="header_cell" width="15%">メモ</td>
    </tr>
    
    <!-- start of block -->
        
    <?php $this->assign('n', 0); ?>
    <?php unset($this->_sections['item']);
$this->_sections['item']['name'] = 'item';
$this->_sections['item']['start'] = (int)0;
$this->_sections['item']['loop'] = is_array($_loop=6) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
        <?php echo ((is_array($_tmp=$this->_tpl_vars['commodity_code'][$this->_tpl_vars['n']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" width="10%">      
        <?php echo ((is_array($_tmp=$this->_tpl_vars['commodity_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" width="10%" align=right>
        <?php echo ((is_array($_tmp=$this->_tpl_vars['amount'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" width="10%" align=center><?php echo ((is_array($_tmp=$this->_tpl_vars['unit_name'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
      <td nowrap class="data_cell" width="10%" align=right>
        <?php echo ((is_array($_tmp=$this->_tpl_vars['unit_price'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" width="10%" align=right>
        <?php echo ((is_array($_tmp=$this->_tpl_vars['total_price'][$this->_sections['item']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" width="15%" align=center><?php echo $this->_tpl_vars['act_flag'][$this->_sections['item']['index']]; ?>
</td>
      <td nowrap class="data_cell" width="15%"><?php echo $this->_tpl_vars['memo'][$this->_sections['item']['index']]; ?>
</td>
    </tr>
    <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
    <?php endfor; endif; ?>
    <!-- end of block -->
    
    <tr>
      <td nowrap class="header_cell" align=right colspan=6>総合計：</td>
      <td nowrap class="data_cell" align=right><?php echo $this->_tpl_vars['grand_total']; ?>
</td>
      <td nowrap class="data_cell" width="15%">&nbsp;</td>
      <td nowrap class="data_cell" width="15%">&nbsp;</td>
      
    </tr>
  </table>
</td>
</table>

</div>

</td>
<tr><td colspan="2">
  <hr size="1">
</td>
</table>  
</form>

<hr size="1">
<FORM ACTION="OlutShipmentMenu.php">
<INPUT TYPE="SUBMIT" VALUE="　終　了　"><BR>
</FORM>
<center>
</td>
</table>
</center>
</body>
</html>