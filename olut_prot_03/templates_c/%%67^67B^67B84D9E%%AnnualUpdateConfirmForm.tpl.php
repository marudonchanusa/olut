<?php /* Smarty version 2.6.9, created on 2005-10-04 11:42:22
         compiled from AnnualUpdateConfirmForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'AnnualUpdateConfirmForm.tpl', 19, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>年次更新</title>

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
<b>年次更新</b>
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
    <?php else: ?>
        <tr>
            <td colspan="2">
            2年以上前の<?php echo $this->_tpl_vars['count']; ?>
レコードをトランザクションから削除します。<br>
            年次更新を実行しますか？
            </td>
        </tr>
        <tr>
            <td colspan="2" align=center>
              <input type="submit" name="annual_update" value="年次更新実行">
            </td>
        </tr>
        
    <?php endif; ?>
    </table>
</form>

<hr size="1">
<FORM ACTION="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メイン・メニューへ戻る"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>
