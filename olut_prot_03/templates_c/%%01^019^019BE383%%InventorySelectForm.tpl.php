<?php /* Smarty version 2.6.9, created on 2005-09-28 19:30:01
         compiled from InventorySelectForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'InventorySelectForm.tpl', 16, false),)), $this); ?>
<html>
<head>
<title>棚卸入力選択</title>

</head>
<body bgcolor="#FFFFFF" link="#000080" vlink="#000080">
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
<b>棚卸入力選択</b>
<form method="POST">     
<hr size="0">

    <table border="0" cellpadding="5" cellspacing="0">
      <tr>
        <td>棚卸年月</td>
        <td><select name="year">
             <?php echo $this->_tpl_vars['year_list']; ?>

            </select> 年
             <select name="month">
             <?php echo $this->_tpl_vars['month_list']; ?>

             </select>月
        </td>     
        <td valign="bottom"> 
        </td>
      </tr>
      <tr>
        <td>資材内部署</td>
        <td>
        <select name="shipment_section_code">
        <?php echo $this->_tpl_vars['shipment_section_list']; ?>

        </select>
        </td>     
        <td rowspan="2" valign="bottom"> 
        </td>
      </tr>
      <tr>
        <td>倉庫（金額）</td>
        <td>
        <select name="warehouse_code">
          <?php echo $this->_tpl_vars['warehouse_list']; ?>

        </select> 
        </td>     
      </tr>
    </table>
    
<hr size="0">
<br>
  <input type="submit" name="start" value="　開始　">
</form>
<hr size="1">
　
<FORM ACTION="OlutMainMenu.php">

<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る">

</FORM>

</center>

</td>
</table>
</center>
</body>
</html>
