<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>業者別仕入一覧表</title>

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
<b>業者別仕入一覧表</b>
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
        <td>出荷日(YYYYMMDD)</td>
        <td>
        <input type="text" class="hankaku" name="date_from" value="{$date_from}" size="12" maxlength="8">&nbsp;-
        <input type="text" class="hankaku" name="date_to" value="{$date_to}" size="12" maxlength="8"> 
        </td>     
      </tr>
      <tr>
        <td>取引先コード</td>
        <td>
          <input type="text" class="hankaku" name="code_from" value="" size="12" maxlength="5">&nbsp;-
          <input type="text" class="hankaku" name="code_to"   value="" size="12" maxlength="5"> 
        </td>     
      </tr>
    </table>

<hr size="0">
<br>
  <input type="submit" name="print_out" value="印刷開始">     
<br>
</form>

<hr size="1">
<FORM ACTION="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>

