<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title> Ǽ�ʽ�ư���</title>

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
<b>Ǽ�ʽ�ư���</b>
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
        <td>�в���ɼ�ֹ�</td>
        <td>
          <input type="text" class="hankaku" name="code_from" value="{$code_from}" size="12" maxlength="7">&nbsp;-
          <input type="text" class="hankaku" name="code_to"   value="{$code_to}" size="12" maxlength="7"> 
        </td>     
      </tr>
    </table>

<hr size="0">
<br>
  <input type="submit" name="print_out" value="��������">     
<br>
</form>

<hr size="1">
<FORM ACTION="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="��˥塼�����"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>
