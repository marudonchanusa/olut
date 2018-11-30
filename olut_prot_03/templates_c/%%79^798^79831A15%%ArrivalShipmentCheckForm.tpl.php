<?php /* Smarty version 2.6.9, created on 2005-09-28 19:32:39
         compiled from ArrivalShipmentCheckForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'ArrivalShipmentCheckForm.tpl', 24, false),array('modifier', 'escape', 'ArrivalShipmentCheckForm.tpl', 123, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>入出荷実績表示</title>
<link rel="stylesheet" href="css/arrival_shipment_check.css" type="text/css" />
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
<p align="center"><b>入出荷実績表示</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border="0" cellpadding=1 width=700px  bgcolor="#808080">
<tr>
  <td nowrap align="center" class=header_cell width=15%>
   商品コード
  </td>
  <td nowrap align="left" class=header_cell width=35%>
    <input type="text" class="hankaku" name="commodity_code" value="<?php echo $this->_tpl_vars['commodity_code']; ?>
" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="commodity_ref" value="参照">
    <?php echo $this->_tpl_vars['commodity_name']; ?>

  </td>
  <TD nowrap align="center" class=header_cell width=25%>
    期間(YYYYMMDD)
  </TD>
  <TD nowrap class=header_cell width=25%>
    <input type="text" class="hankaku" name="date_from" value="<?php echo $this->_tpl_vars['date_from']; ?>
" size="10" maxlength="8" onkeypress="javascript:digitonly();" > -
    <input type="text" class="hankaku" name="date_to" value="<?php echo $this->_tpl_vars['date_to']; ?>
" size="10" maxlength="8" onkeypress="javascript:digitonly();">
    <input type="submit" name="check" value="　集計開始　" style="width:80px;">
  </TD>
</tr>

<tr>
  <td colspan=2 class=header_cell align=center> 現在出荷単価&nbsp; <?php echo $this->_tpl_vars['commodity_unit_price']; ?>
 &nbsp; 円</td>
  <td colspan=2 class=header_cell align=center> 数量の単位 &nbsp; <?php echo $this->_tpl_vars['commodity_unit_name']; ?>
 </td>
</tr>

<tr>
  <td nowrap align="center" colspan=4  class=header_cell>
  <table border=0 cellpadding=2 width=100% bgcolor="#808080">
  <tr>
   <td width=16% class=header_cell> 開始在庫 </td> <td width=16% class=data_cell align=right><?php echo $this->_tpl_vars['stock_start_amount']; ?>
</td>    <td width=16% class=data_cell align=right> <?php echo $this->_tpl_vars['stock_start_price']; ?>
 </td>
   <td width=16% class=header_cell> 入荷合計 </td> <td width=16% class=data_cell align=right><?php echo $this->_tpl_vars['arrival_total_amount']; ?>
 </td> <td width=16% class=data_cell align=right> <?php echo $this->_tpl_vars['arrival_total_price']; ?>
</td>
  </tr>

  <tr>
   <td width=16% class=header_cell> 終了在庫 </td> <td class=data_cell align=right><?php echo $this->_tpl_vars['stock_end_amount']; ?>
</td>       <td class=data_cell align=right> <?php echo $this->_tpl_vars['stock_end_price']; ?>
 </td>
   <td width=16% class=header_cell> 出荷合計 </td> <td class=data_cell align=right><?php echo $this->_tpl_vars['shipment_total_amount']; ?>
 </td> <td class=data_cell align=right> <?php echo $this->_tpl_vars['shipment_total_price']; ?>
</td>
  </tr>

  <tr>
   <td width=16% class=header_cell> 現在庫 </td> <td class=data_cell align=right><?php echo $this->_tpl_vars['stock_current_amount']; ?>
</td>    <td class=data_cell align=right> <?php echo $this->_tpl_vars['stock_current_price']; ?>
 </td>
   <td width=16% class=header_cell> 期間内差益高 </td> <td  class=data_cell colspan=2 align=right><?php echo $this->_tpl_vars['balance']; ?>
</td>
  </tr>
  
  </table>
  
  </td>
</tr>
  
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
      <td class="header_cell" nowrap align="center">日付</td>
      <td class="header_cell" align="center" colspan=2>出荷</td>
      <td class="header_cell" align="center" colspan=2>入荷</td>
    </tr>
    
    <!-- ここからループ -->
    <?php $this->assign('n', 1); ?>
    <?php $_from = $this->_tpl_vars['line_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id']):
?>

    <tr>
      <td nowrap class="left_number_cell" align="right" width="5%">&nbsp;<?php echo $this->_tpl_vars['n']; ?>
</td>
      <td nowrap class="data_cell" ><?php echo ((is_array($_tmp=$this->_tpl_vars['id']->date)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>

      <td nowrap class="data_cell" align="right" width="20%">
       <?php echo ((is_array($_tmp=$this->_tpl_vars['id']->shipment_amount)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>

      <td nowrap class="data_cell" align="right" width="20%">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['id']->shipment_price)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
      
      <td nowrap class="data_cell" align="right" width="20%">
       <?php echo ((is_array($_tmp=$this->_tpl_vars['id']->arrival_amount)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>

      <td nowrap class="data_cell" align="right" width="20%">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['id']->arrival_price)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

      </td>
    </tr>
    <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
    <?php endforeach; endif; unset($_from); ?>
    
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
  <input type="submit" value="メニューへ戻る" name="cancel">
  </form>

</table>
</body>



</html>
