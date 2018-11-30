<?php /* Smarty version 2.6.9, created on 2005-10-15 13:40:27
         compiled from ShipmentDetailReportForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'ShipmentDetailReportForm.tpl', 19, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>荷渡明細書</title>

</head>
<body bgcolor="#99CCFF" link="#000080" vlink="#000080">
<center>
<table border="0" width="90%">
<tr>
<td valign="top">
</td>
<td align="right">
    <table border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td>DATE:</td>
        <td><font size="3"><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<center>
<b>荷渡明細書</b>
<form method="POST">     

<hr size="0">

  <br>

    <table border="0" cellpadding="5" cellspacing="0">
    <?php if ($this->_tpl_vars['error'] != ""): ?>
        <tr>
            <td bgcolor="yellow" colspan="2">
              <?php echo $this->_tpl_vars['error']; ?>

            </td>
        </tr>
    <?php endif; ?>
      <tr>
        <td>出荷月</td>
        <td>
        <select name="target_year"><?php echo $this->_tpl_vars['target_year_list']; ?>
</select>
        <select name="target_month"><?php echo $this->_tpl_vars['target_month_list']; ?>
</select>
        </td>     
      </tr>
      <tr>
        <td>店舗コード</td>
        <td>
          <input type="text" class="hankaku" name="code_from" value="" size="12" maxlength="5">&nbsp;-
          <input type="text" class="hankaku" name="code_to"   value="" size="12" maxlength="5"> 
        </td>     
      </tr>
    </table>


<hr size="0">
<br>
  <input type="submit" name="print_out" value="印刷開始">
<!--
  <input type="submit" name="print_out" value="印刷開始" onclick="javascript:window.open('/eclipse/olut/ShipmentDetailReport.php','','toolbar=no,menubar=no,resizable=yes');">     
-->
<br>
</form>
<hr size="1">
</table>  

<FORM ACTION="OlutPrintMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る" onclick="javascript:window.close();"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>
