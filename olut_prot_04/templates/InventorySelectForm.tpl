<html>
<head>
<title>ê����������</title>

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
        <td>DATE��</td>
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<center>
<b>ê����������</b>
<form method="POST">     
<hr size="0">

    <table border="0" cellpadding="5" cellspacing="0">
      <tr>
        <td>ê��ǯ��</td>
        <td><select name="year">
             {$year_list}
            </select> ǯ
             <select name="month">
             {$month_list}
             </select>��
        </td>     
        <td valign="bottom"> 
        </td>
      </tr>
      <tr>
        <td>���������</td>
        <td>
        <select name="shipment_section_code">
        {$shipment_section_list}
        </select>
        </td>     
        <td rowspan="2" valign="bottom"> 
        </td>
      </tr>
      <tr>
        <td>�Ҹˡʶ�ۡ�</td>
        <td>
        <select name="warehouse_code">
          {$warehouse_list}
        </select> 
        </td>     
      </tr>
    </table>
    
<hr size="0">
<br>
  <input type="submit" name="start" value="�����ϡ�">
</form>
<hr size="1">
��
<FORM ACTION="OlutMainMenu.php">

<INPUT TYPE="SUBMIT" VALUE="�ᥤ�󡦥�˥塼�����">

</FORM>

</center>

</td>
</table>
</center>
</body>
</html>

