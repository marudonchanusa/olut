<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>出荷伝票入力</title>
<link rel="stylesheet" href="css/shipment.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body class="body">
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
<p align="center"><b>出荷伝票入力</b>
<form method="POST" method=post>

<hr size="0">
<center>
<table border="0" width="90%">
<tr><td colspan="2">

<TABLE border=0 width=10 cellpadding=2>
<TR>
  <TD nowrap align="right">店舗</TD>
  <TD nowrap>
    <input type="text" class="hankaku" name="store_code" value="{$store_code}" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="store_ref" value="参照">
  </TD>
  <td nowrap>{$store_name}</td>
</tr>
<TR>
<TD nowrap align="right">部門<BR></TD>
<TD nowrap>
  <select name="store_section_code">{$store_section_list}</select>
</TD>

<TR>
<TD nowrap align="right">
日付
</TD>
<TD nowrap colspan="2">
    {if $year eq '' }
    <input type="text" class="hankaku" name="year" value="{$smarty.now|date_format:"%Y"}" size="6" maxlength="4" onkeypress="javascript:digitonly();">年
    <input type="text" class="hankaku" name="month" value="{$smarty.now|date_format:"%m"}" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="{$smarty.now|date_format:"%d"}" size="2" maxlength="2" onkeypress="javascript:digitonly();">日
    {else}
    <input type="text" class="hankaku" name="year" value="{$year}" size="6" maxlength="8" onkeypress="javascript:digitonly();">年
    <input type="text" class="hankaku" name="month" value="{$month}" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="{$date}" size="2" maxlength="2" onkeypress="javascript:digitonly();">日
    {/if}  
</TD>
</tr>

<tr>
<td nowrap>伝票番号</td>
<td nowrap><input type="text" name="slip_no" class="hankaku" value="{$slip_no}" size=10 maxlength=7 onkeypress="javascript:digitonly();"></td>
</tr>

</TABLE>
<hr size="1">

</td>
<tr><td>

<div align="center">

<table border=0 bgcolor="#808080" cellpadding="1" width="90%">

{if $error ne ""}
    <tr>
        <td bgcolor="yellow" colspan="2">
          {$error}
        </td>
    </tr>
{/if}

<tr><td>

  <table border="0" cellpadding="2" width="100%">
    <tr>
      <td nowrap class="header_cell" width="1%">　</td>
      <td nowrap class="header_cell" width="10%">商品No</td>
      <td class="header_cell"  width="100">商品名</td>
      <td class="header_cell"  width="10%">数量</td>
      <td nowrap class="header_cell" width="10%">単位</td>
      <td nowrap class="header_cell" width="10%">単価</td>
      <td class="header_cell" width="10%" nowrap>金額</td>
      <td class="header_cell"  width="15%">区分</td>
      <td class="header_cell" width="15%">メモ</td>
    </tr>
    
    <!-- start of block -->
        
    {assign var="n" value=0}
    {section name=item start=0 loop=6 step=1}
    <tr>
      <td nowrap class="left_number_cell" align="right" width="1%">&nbsp;{$smarty.section.item.index+1}</td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" class="hankaku" name="commodity_code_{$n}" value="{$commodity_code[item]|escape}" size="7" maxlength="5" onkeypress="javascript:digitonly();">
        <input type="submit" name="commodity_ref_{$n}" value="H">
      </td>
      <td nowrap class="data_cell" width="100">{$commodity_name[item]|escape}</td>
      <td nowrap class="data_cell"" width="10%">
        <input type="text" class="money" name="amount_{$n}" value="{$amount[item]|escape}" size="4" maxlength="8" onkeypress="javascript:moneyonly();">
      </td>
      <td nowrap class="data_cell" width="10%">{$unit_name[item]|escape}</td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" class="money" name="unit_price_{$n}" value="{$unit_price[item]|escape}" size="8" maxlength="8" onkeypress="javascript:moneyonly();">
      </td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" class="money" name="total_price_{$n}" value="{$total_price[item]|escape}" size="8" maxlength="9" onkeypress="javascript:digit_and_sign();">
      </td>
      <td nowrap class="data_cell" width="15%"><select name="act_flag_{$n}">{$act_flag_list[item]}</select></td>
      <td nowrap class="data_cell" width="15%"><input type="text" name="memo_{$n}" value="{$memo[item]}" size=20 maxlength="255"></td>
    </tr>
    {assign var="n" value=$n+1}
    {/section}
    <!-- end of block -->
  </table>
</td>
</table>

</div>

</td>
<tr><td colspan="2">
  <hr size="1">
</td>
</table>  
  <br>
  <input type="submit" name="check" value="　確認　">
  <input type="submit" name="save" value="　保存　">
</form>

<hr size="1">
<FORM ACTION="OlutShipmentMenu.php">
<INPUT TYPE="SUBMIT" VALUE="  メニューへ戻る  "><BR>
</FORM>
<center>
</td>
</table>
</center>
</body>
</html>