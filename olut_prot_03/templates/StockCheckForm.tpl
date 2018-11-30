<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>在庫確認</title>
<link rel="stylesheet" href="css/stock_check.css" type="text/css" />
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
<p align="center"><b>在庫確認</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border="0" cellpadding=2 width=600px>
<tr>
  <td nowrap align="right">
   商品コード
  </td>
  <td nowrap align="left">
    <input type="text" class="hankaku" name="commodity_code" value="{$commodity_code}" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="commodity_ref" value="参照">
    {$commodity_name}
  </td>
</tr>

<TR>
  <TD nowrap align="right">
    日付
  </TD>
  <TD nowrap>
    {if $year eq '' }
    <input type="text" class="hankaku" name="year" value="{$smarty.now|date_format:"%Y"}" size="6" maxlength="4" onkeypress="javascript:digitonly();" >年
    <input type="text" class="hankaku" name="month" value="{$smarty.now|date_format:"%m"}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >月
    <input type="text" class="hankaku" name="date" value="{$smarty.now|date_format:"%d"}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >日
    {else}
    <input type="text" class="hankaku" name="year" value="{$year}" size="6" maxlength="8" onkeypress="javascript:digitonly();" >年
    <input type="text" class="hankaku" name="month" value="{$month}" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="{$date}" size="2" maxlength="2" onkeypress="javascript:digitonly();">日
    {/if}   
    
  </TD>
</tr>
<tr>
  <td nowrap align="center"colspan=2>
  <input type="submit" name="check" value="　検索開始　" style="width:80px;">
  <input type="submit" name="prev" value="　前日　" style="width:80px;">
  <input type="submit" name="next" value="　翌日　" style="width:80px;">
  </td>
  
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
      <td class="header_cell" nowrap align="center">倉庫名</td>
      <td class="header_cell" width="20%"  align="center">在庫</td>
      <td class="header_cell" width="20%"  align="center">入庫</td>
      <td class="header_cell" width="20%"  align="center">出庫</td>
    </tr>
    
    <!-- ここからループ -->
    {assign var="n" value=1}
    {foreach from=$warehouse_codes item=id}

    <tr>
      <td nowrap class="left_number_cell" align="right" width="5%">&nbsp;{$n}</td>
      <td nowrap class="data_cell" >{$warehouse_names[$id]|escape}
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        {$stocks[$id]|escape}
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        {$arrivals[$id]|escape}
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
       {$shipments[$id]|escape}
      </td>
    </tr>
    {assign var="n" value=$n+1}
    {/foreach}
    
    <!-- total -->
    <tr>
      <td nowrap class="data_cell" align="right" colspan=2>全倉庫合計
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        {$stocks_total|escape}
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
        {$arrivals_total|escape}
      </td>
      <td nowrap class="data_cell" align="right" width="20%">
       {$shipments_total|escape}
      </td>
    </tr>
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
  <input type="submit" value="メニューへ戻る" name="cancel" >
  </form>

</table>
</body>



</html>

