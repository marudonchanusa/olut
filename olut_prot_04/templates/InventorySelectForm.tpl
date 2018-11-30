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
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
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
             {$year_list}
            </select> 年
             <select name="month">
             {$month_list}
             </select>月
        </td>     
        <td valign="bottom"> 
        </td>
      </tr>
      <tr>
        <td>資材内部署</td>
        <td>
        <select name="shipment_section_code">
        {$shipment_section_list}
        </select>
        </td>     
        <td rowspan="2" valign="bottom"> 
        </td>
      </tr>
      <tr>
        <td>倉庫（金額）</td>
        <td>
        <select name="warehouse_code">
          {$warehouse_list}
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

<INPUT TYPE="SUBMIT" VALUE="メイン・メニューへ戻る">

</FORM>

</center>

</td>
</table>
</center>
</body>
</html>

