<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>入出荷実績表示</title>
<link rel="stylesheet" href="css/arrival_shipment_check.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body class="body">

<form method="post">

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
<p align="center"><b>入出荷実績表示</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border="0" cellpadding=1 width=700px  bgcolor="#808080">
<tr>
  <td nowrap align="center" class=header_cell width=15%>
   商品コード
  </td>
  <td nowrap align="left" class=header_cell width=35%>
    <input type="text" class="hankaku" name="commodity_code" value="{$commodity_code}" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="commodity_ref" value="参照">
    {$commodity_name}
  </td>
  <TD nowrap align="center" class=header_cell width=25%>
    期間(YYYYMMDD)
  </TD>
  <TD nowrap class=header_cell width=25%>
    <input type="text" class="hankaku" name="date_from" value="{$date_from}" size="10" maxlength="8" onkeypress="javascript:digitonly();" > -
    <input type="text" class="hankaku" name="date_to" value="{$date_to}" size="10" maxlength="8" onkeypress="javascript:digitonly();">
    <input type="submit" name="check" value="　集計開始　" style="width:80px;">
  </TD>
</tr>

<tr>
  <td colspan=2 class=header_cell align=center> 現在出荷単価&nbsp; {$commodity_unit_price} &nbsp; 円</td>
  <td colspan=2 class=header_cell align=center> 数量の単位 &nbsp; {$commodity_unit_name} </td>
</tr>

<tr>
  <td nowrap align="center" colspan=4  class=header_cell>
  <table border=0 cellpadding=2 width=100% bgcolor="#808080">
  <tr>
   <td width=16% class=header_cell> 開始在庫 </td> <td width=16% class=data_cell align=right>{$stock_start_amount}</td>    <td width=16% class=data_cell align=right> {$stock_start_price} </td>
   <td width=16% class=header_cell> 入荷合計 </td> <td width=16% class=data_cell align=right>{$arrival_total_amount} </td> <td width=16% class=data_cell align=right> {$arrival_total_price}</td>
  </tr>

  <tr>
   <td width=16% class=header_cell> 終了在庫 </td> <td class=data_cell align=right>{$stock_end_amount}</td>       <td class=data_cell align=right> {$stock_end_price} </td>
   <td width=16% class=header_cell> 出荷合計 </td> <td class=data_cell align=right>{$shipment_total_amount} </td> <td class=data_cell align=right> {$shipment_total_price}</td>
  </tr>

  <tr>
   <td width=16% class=header_cell> 現在庫 </td> <td class=data_cell align=right>{$stock_current_amount}</td>    <td class=data_cell align=right> {$stock_current_price} </td>
   <td width=16% class=header_cell> 期間内差益高 </td> <td  class=data_cell colspan=2 align=right>{$balance}</td>
  </tr>
  
  </table>
  
  </td>
</tr>
  
</tr>


</TABLE>
  <hr size="1">

</td>
<tr><td>

<div align="center">

<table border=0 bgcolor="#808080" cellpadding="2" width=100%>

{if $error ne ""}
    <tr>
        <td bgcolor="yellow" colspan="2">
          {$error}
        </td>
    </tr>
{/if}
    
<tr><td>

  <table border="0" cellpadding="1" width=100%>
    <tr>
      <td class="header_cell" nowrap width="5%">　</td>
      <td class="header_cell" nowrap align="center">日付</td>
      <td class="header_cell" align="center" colspan=2>出荷</td>
      <td class="header_cell" align="center" colspan=2>入荷</td>
    </tr>
    
    <!-- ここからループ -->
    {assign var="n" value=1}
    {foreach from=$line_info item=id}

    <tr>
      <td nowrap class="left_number_cell" align="right" width="5%">&nbsp;{$n}</td>
      <td nowrap class="data_cell" >{$id->date|escape}
      </td>

      <td nowrap class="data_cell" align="right" width="20%">
       {$id->shipment_amount|escape}
      </td>

      <td nowrap class="data_cell" align="right" width="20%">
        {$id->shipment_price|escape}
      </td>
      
      <td nowrap class="data_cell" align="right" width="20%">
       {$id->arrival_amount|escape}
      </td>

      <td nowrap class="data_cell" align="right" width="20%">
        {$id->arrival_price|escape}
      </td>
    </tr>
    {assign var="n" value=$n+1}
    {/foreach}
    
  </table>
</td>
</table>

  </div>

</td>
<tr><td>

  <hr size="1">
</td>
</table>  
  <hr size="1">
  </form>
  <form method=post action="OlutMainMenu.php">
  <input type="submit" value="メニューへ戻る" name="cancel">
  </form>

</table>
</body>



</html>

