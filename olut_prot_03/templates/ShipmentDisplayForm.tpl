<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>出荷伝票表示</title>
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
<p align="center"><b>出荷伝票表示</b>
<form method="POST" method=post>

<hr size="0">
<center>
<table border="0" width="90%">
<tr><td colspan="2">

<TABLE border=0 width=10 cellpadding=2>

<tr>
<td nowrap>伝票番号</td>
<td nowrap>
<input type="text" name="slip_no" class="hankaku" value="{$slip_no}" size=10 maxlength=7 onkeypress="javascript:digitonly();">
<input type="submit" name="find" value=" 表示開始 ">
<input type="submit" name="prev" value="　前　">
<input type="submit" name="next" value="　次　">

</td>
</tr>

<TR>
  <TD nowrap align="right">店舗</TD>
  <td nowrap>{$store_name}</td>
</tr>
<TR>
<TD nowrap align="right">部門<BR></TD>
<TD nowrap>{$store_section_name}
</TD>

<TR>
<TD nowrap align="right">
日付
</TD>
<TD nowrap colspan="2">
{if $year ne ""}
{$year}年{$month}月{$date}日
{/if}
</TD>
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
        {$commodity_code[$n]|escape}
      </td>
      <td nowrap class="data_cell" width="10%">      
        {$commodity_name[item]|escape}
      </td>
      <td nowrap class="data_cell" width="10%" align=right>
        {$amount[item]|escape}
      </td>
      <td nowrap class="data_cell" width="10%" align=center>{$unit_name[item]|escape}</td>
      <td nowrap class="data_cell" width="10%" align=right>
        {$unit_price[item]|escape}
      </td>
      <td nowrap class="data_cell" width="10%" align=right>
        {$total_price[item]|escape}
      </td>
      <td nowrap class="data_cell" width="15%" align=center>{$act_flag[item]}</td>
      <td nowrap class="data_cell" width="15%">{$memo[item]}</td>
    </tr>
    {assign var="n" value=$n+1}
    {/section}
    <!-- end of block -->
    
    <tr>
      <td nowrap class="header_cell" align=right colspan=6>総合計：</td>
      <td nowrap class="data_cell" align=right>{$grand_total}</td>
      <td nowrap class="data_cell" width="15%">&nbsp;</td>
      <td nowrap class="data_cell" width="15%">&nbsp;</td>
      
    </tr>
  </table>
</td>
</table>

</div>

</td>
<tr><td colspan="2">
  <hr size="1">
</td>
</table>  
</form>

<hr size="1">
<FORM ACTION="OlutShipmentMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る"><BR>
</FORM>
<center>
</td>
</table>
</center>
</body>
</html>