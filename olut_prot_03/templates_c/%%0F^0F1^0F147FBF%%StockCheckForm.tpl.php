<?php /* Smarty version 2.6.9, created on 2005-09-28 19:29:25
         compiled from StockCheckForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'StockCheckForm.tpl', 24, false),array('modifier', 'escape', 'StockCheckForm.tpl', 110, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>在庫確認</title>
<link rel="stylesheet" href="css/stock_check.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body class="body">

<form method="post">

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
<p align="center"><b>在庫確認</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border="0" cellpadding=2 width=600px>
<tr>
  <td nowrap align="right">
   商品コード
  </td>
  <td nowrap align="left">
    <input type="text" class="hankaku" name="commodity_code" value="<?php echo $this->_tpl_vars['commodity_code']; ?>
" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="commodity_ref" value="参照">
    <?php echo $this->_tpl_vars['commodity_name']; ?>

  </td>
</tr>

<TR>
  <TD nowrap align="right">
    日付
  </TD>
  <TD nowrap>
    <?php if ($this->_tpl_vars['year'] == ''): ?>
    <input type="text" class="hankaku" name="year" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
" size="6" maxlength="4" onkeypress="javascript:digitonly();" >年
    <input type="text" class="hankaku" name="month" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m")); ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();" >月
    <input type="text" class="hankaku" name="date" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d") : smarty_modifier_date_format($_tmp, "%d")); ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();" >日
    <?php else: ?>
    <input type="text" class="hankaku" name="year" value="<?php echo $this->_tpl_vars['year']; ?>
" size="6" maxlength="8" onkeypress="javascript:digitonly();" >年
    <input type="text" class="hankaku" name="month" value="<?php echo $this->_tpl_vars['month']; ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="<?php echo $this->_tpl_vars['date']; ?>
" size="2" maxlength="2" onkeypress="javascript:digitonly();">日
    <?php endif; ?>   
    
  </TD>
</tr>
<tr>
  <td nowrap align="center"colspan=2>
  <input type="submit" name="check" value="　検索開始　" style="width:80px;">
  <input type="submit" name="prev" value="　前日　" style="width:80px;">
  <input type="submit" name="next" value="　翌日　" style="width:80px;">
  </td>
  
</tr>
</TABLE>
  <hr size="1">

</td>
<tr><td>

<div align="center">

<table border=0 bgcolor="#808080" cellpadding="2" width=100%>

<?php if ($this->_tpl_vars['error'] != ""): ?>
    <tr>
        <td bgcolor="yellow" colspan="2">
          <?php echo $this->_tpl_vars['error']; ?>

        </td>
    </tr>
<?php endif; ?>
    
<tr><td>

  <table border="0" cellpadding="1" width=100%>
    <tr>
      <td class="header_cell" nowrap width="5%">　</td>
      <td class="header_cell" nowrap align="center">倉庫名</td>
      <td class="header_cell" width="20%"  align="center">在庫</td>
      <td class="header_cell" width="20%"  align="center">入庫</td>
      <td class="header_cell" width="20%"  align="center">出庫</td>
    </tr>
    
    <!-- ここからループ -->
    <?php $this->assign('n', 1); ?>
    <?php $_from = $this->_tpl_vars['warehouse_codes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id']):
?>

    <tr>
      <td nowrap class="left_number_cell" align="right" width="5%">&nbsp;<?php echo $this->_tpl_vars['n']; ?>
</td>
      <td nowrap class="data_cell" ><?php echo ((is_array($_tmp=$this->_tpl_vars['warehouse_names'][$this->_tpl_vars['id']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['stocks'][$this->_tpl_vars['id']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['arrivals'][$this->_tpl_vars['id']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" align="right" width="20%">
       <?php echo ((is_array($_tmp=$this->_tpl_vars['shipments'][$this->_tpl_vars['id']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
    </tr>
    <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
    <?php endforeach; endif; unset($_from); ?>
    
    <!-- total -->
    <tr>
      <td nowrap class="data_cell" align="right" colspan=2>全倉庫合計
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['stocks_total'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['arrivals_total'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      <td nowrap class="data_cell" align="right" width="20%">
       <?php echo ((is_array($_tmp=$this->_tpl_vars['shipments_total'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
    </tr>
  </table>
</td>
</table>

  </div>

</td>
<tr><td>

  <hr size="1">
</td>
</table>  
  <hr size="1">
  </form>
  <form method=post action="OlutMainMenu.php">
  <input type="submit" value="メニューへ戻る" name="cancel" >
  </form>

</table>
</body>



</html>
