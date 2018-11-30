<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title> 納品書再印刷</title>

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
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<center>
<b>納品書再印刷</b>
<form method="POST">     

<hr size="0">

  <br>

    <table border="0" cellpadding="5" cellspacing="0">
    {if $error ne ""}
        <tr>
            <td bgcolor="yellow" colspan="2">
              {$error}
            </td>
        </tr>
    {/if}
      <tr>
        <td>出荷伝票番号</td>
        <td>
          <input type="text" class="hankaku" name="code_from" value="{$code_from}" size="12" maxlength="7">&nbsp;-
          <input type="text" class="hankaku" name="code_to"   value="{$code_to}" size="12" maxlength="7"> 
        </td>     
      </tr>
    </table>

<hr size="0">
<br>
  <input type="submit" name="print_out" value="印刷開始">     
<br>
</form>

<hr size="1">
<FORM ACTION="OlutPrintMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る" onclick="javascript:window.close();"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>

