<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>店票毎荷渡明細書</title>

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
<b>店票毎荷渡明細書</b>
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
        <td>出荷月</td>
        <td>
        <select name="target_year">{$target_year_list}</select>
        <select name="target_month">{$target_month_list}</select>
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
<br>
</form>
<hr size="1">
</table>  

<FORM ACTION="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>

