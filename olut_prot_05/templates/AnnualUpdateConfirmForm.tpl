<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>ǯ������</title>

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
<b>ǯ������</b>
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
    {else}
        <tr>
            <td colspan="2">
            4ǯ�ʾ�����{$count}�쥳���ɤ�ȥ�󥶥�����󤫤������ޤ���<br>
            ǯ��������¹Ԥ��ޤ�����
            </td>
        </tr>
        <tr>
            <td colspan="2" align=center>
              <input type="submit" name="annual_update" value="ǯ�������¹�">
            </td>
        </tr>
        
    {/if}
    </table>
</form>

<hr size="1">
<FORM ACTION="OlutDataMenu.php">
<INPUT TYPE="SUBMIT" VALUE="��˥塼�����"><BR>
</FORM>
</center>

</td>
</table>
</center>
</body>
</html>

