<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>入荷伝票入力</title>
<link rel="stylesheet" href="css/arrival_of_goods.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body class="body">

<form method="post" name="olut">

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
<p align="center"><b>入荷伝票入力</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border=0 width=10 cellpadding=2>
<TR>
  <TD nowrap align="right">
    日付
  </TD>
  <TD nowrap>
    {if $year eq '' }
    <input type="text" class="hankaku" name="year" value="{$smarty.now|date_format:"%Y"}" size="6" maxlength="4" onkeypress="javascript:digitonly();" >年
    <input type="text" class="hankaku" name="month" value="{$smarty.now|date_format:"%m"}" size="2" maxlength="2" onkeypress="javascript:digitonly();">月
    <input type="text" class="hankaku" name="date" value="{$smarty.now|date_format:"%d"}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >日
    {else}
    <input type="text" class="hankaku" name="year" value="{$year}" size="6" maxlength="8" onkeypress="javascript:digitonly();"  >年
    <input type="text" class="hankaku" name="month" value="{$month}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >月
    <input type="text" class="hankaku" name="date" value="{$date}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >日
    {/if}   
  </TD>
</tr>
<tr>
  <td nowrap align="right">
   伝票番号
  </td>
  <td nowrap align="left">
    <input type="text" class="hankaku" name="slip_no" value="{$slip_no}" size="8" maxlength="7" onkeypress="javascript:digitonly();" >
    {if $modify_mode eq true}
      <INPUT TYPE="SUBMIT" VALUE="検索" name="find" style="width:80px;">
      <INPUT TYPE="SUBMIT" VALUE="前" name="prev" style="width:80px;">
      <INPUT TYPE="SUBMIT" VALUE="次" name="next" style="width:80px;">
    {/if}
  </td>

</tr>
<tr>
  <td nowrap align="right">
   倉庫
  </td>
  <td nowrap align="left">
  {if $readonly ne ''}
    <select name="warehouse_code" disabled> {$warehouse_list} </select>
  {else}
    <select name="warehouse_code"> {$warehouse_list} </select>
  {/if}
  </td>

</tr>
</TABLE>
  <hr size="1">

</td>
<tr><td>

<div align="center">

<table border=0 bgcolor="#808080" cellpadding="2">

{if $error ne ""}
    <tr>
        <td bgcolor="yellow" colspan="2">
          {$error}
        </td>
    </tr>
{/if}
    
<tr><td>

  <table border="0" cellpadding="1">
    <tr>
      <td class="header_cell" nowrap width="1%">　</td>
      <td class="header_cell" nowrap width="10%" align="center">商品No</td>
      <td class="header_cell" width="10%"  align="center">商品名</td>
      <td class="header_cell" width="10%"  align="center">仕入取引先</td>
      <td class="header_cell" width="30%"  align="center">仕入取引先名</td>
      <td class="header_cell" width="5%"    align="center">数量</td>
      <td class="header_cell" nowrap width="10%" align="center">単位</td>
      <td class="header_cell" nowrap width="5%" align="center">単価</td>
      <td class="header_cell" width="10%"  align="center">金額</td>
      <td class="header_cell" width="10%" align="center">支払区分</td>
      <td class="header_cell" width="10%" align="center">入庫区分</td>
      <td class="header_cell" width="10%" align="center">メモ</td>
      
    </tr>
    
    <!-- ここからループ -->
    {assign var="n" value=0}
    {section name=item start=0 loop=10 step=1}

    <tr>
      <td nowrap class="left_number_cell" align="right" width="1%">&nbsp;{$smarty.section.item.index+1}</td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" class="hankaku" name="commodity_code_{$n}" value="{$commodity_code[item]|escape}" size="7" maxlength="5" onkeypress="javascript:digitonly();">
        <input type="submit" name="commodity_ref_{$n}" value="H"> 
      </td>
      <td nowrap class="data_cell" align="left" width="100">
        {$commodity_name[item]|escape}
      </td>
      <td nowrap class="data_cell" align="center" width="10%">
        <input type="text" class="hankaku" name="vendor_code_{$n}" value="{$vendor_code[item]|escape}" size="8" maxlength="5" onkeypress="javascript:digitonly();">
        <input type="submit" name="vendor_ref_{$n}" value="H"> 
      </td>
      <td nowrap class="data_cell" width="30%">{$vendor_name[item]|escape}</td>
      <td nowrap class="data_cell" width="5%"><input type="text" class="money" name="amount_{$n}" value="{$amount[item]|escape}" size="4" maxlength="8" onkeypress="javascript:moneyonly();"></td>
      <td nowrap class="data_cell" width="60"> {$unit_name[item]|escape}</td>
      <td nowrap class="data_cell" width="5%" align=right>
        <input type="hidden" name="unit_price_{$n}" value="{$unit_price[item]|escape}" size="8" maxlength="8">
        {$unit_price[item]}
      </td>
      <td nowrap class="data_cell" width="60">

        <input type="text" class="money" name="total_price_{$n}" value="{$total_price[item]|escape}" size="8" maxlength="9" onBlur="javascript:blur_submit(this.form,this);"  onkeypress="javascript:digit_and_sign();" >
        

      </td>
      <td nowrap class="data_cell" width="10%"><select name="payment_flag_{$n}">{$payment_flag_list[item]}</select></td>
      <td nowrap class="data_cell" width="10%"><select name="act_flag_{$n}">{$act_flag_list[item]}</select></td>
      <td nowrap class="data_cell" width="10%">
        <input type="text" name="memo_{$n}" value="{$memo[item]|escape}" size="20" maxlength="255">
      </td>
      
    </tr>
    {assign var="n" value=$n+1}
    {/section}

  </table>
</td>
</table>

  </div>

</td>
<tr><td>

  <hr size="1">
</td>
</table>  
  <input type="hidden" name="modify_mode" value="{$modify_mode}">
  <input type="hidden" name="blur_flag" value="">
  <input type="submit" name="check" value="　確認　" style="width:80px;" {$button_state}>
  <INPUT TYPE="SUBMIT" VALUE="保存" name="save" style="width:80px;" {$button_state}>
  {if $modify_mode eq true}
    <INPUT TYPE="SUBMIT" VALUE="削除" name="delete" style="width:80px;" onclick="return confirm('削除して宜しいですか？');" {$button_state}>
  {/if}  
  <hr size="1">
<!--  </form> -->
  <form method=post action="OlutArrivalOfGoodsMenu.php">
  <input type="button" value="メニューへ戻る" name="cancel" onclick="javascript:check_menuback_for_arrival(this.form);">
  </form>

</table>
</body>



</html>

