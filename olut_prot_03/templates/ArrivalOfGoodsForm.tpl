<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>������ɼ����</title>
<link rel="stylesheet" href="css/arrival_of_goods.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>
<script>
history.forward();
</script>

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
        <td>DATE��</td>
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<p align="center"><b>������ɼ����</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border=0 width=10 cellpadding=2>
<TR>
  <TD nowrap align="right">
    ����
  </TD>
  <TD nowrap>
    {if $year eq '' }
    <input type="text" class="hankaku" name="year" value="{$smarty.now|date_format:"%Y"}" size="6" maxlength="4" onkeypress="javascript:digitonly();"  {$readonly}>ǯ
    <input type="text" class="hankaku" name="month" value="{$smarty.now|date_format:"%m"}" size="2" maxlength="2" onkeypress="javascript:digitonly();"  {$readonly}>��
    <input type="text" class="hankaku" name="date" value="{$smarty.now|date_format:"%d"}" size="2" maxlength="2" onkeypress="javascript:digitonly();"  {$readonly}>��
    {else}
    <input type="text" class="hankaku" name="year" value="{$year}" size="6" maxlength="8" onkeypress="javascript:digitonly();"  {$readonly}>ǯ
    <input type="text" class="hankaku" name="month" value="{$month}" size="2" maxlength="2" onkeypress="javascript:digitonly();"  {$readonly}>��
    <input type="text" class="hankaku" name="date" value="{$date}" size="2" maxlength="2" onkeypress="javascript:digitonly();"  {$readonly}>��
    {/if}   
  </TD>
</tr>
<tr>
  <td nowrap align="right">
   ��ɼ�ֹ�
  </td>
  <td nowrap align="left">
    <input type="text" class="hankaku" name="slip_no" value="{$slip_no}" size="8" maxlength="7" onkeypress="javascript:digitonly();" {$readonly}>
  </td>

</tr>
<tr>
  <td nowrap align="right">
   �Ҹ�
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
      <td class="header_cell" nowrap width="1%">��</td>
      <td class="header_cell" nowrap width="10%" align="center">����No</td>
      <td class="header_cell" width="10%"  align="center">����̾</td>
      <td class="header_cell" width="10%"  align="center">���������</td>
      <td class="header_cell" width="30%"  align="center">���������̾</td>
      <td class="header_cell" width="5%"    align="center">����</td>
      <td class="header_cell" nowrap width="10%" align="center">ñ��</td>
      <td class="header_cell" nowrap width="5%" align="center">ñ��</td>
      <td class="header_cell" width="10%"  align="center">���</td>
      <td class="header_cell" width="10%" align="center">��ʧ��ʬ</td>
      <td class="header_cell" width="10%" align="center">���˶�ʬ</td>
      <td class="header_cell" width="10%" align="center">���</td>
      
    </tr>
    
    <!-- ��������롼�� -->
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
      <td nowrap class="data_cell" width="5%">
        <input type="text" class="money" name="unit_price_{$n}" value="{$unit_price[item]|escape}" size="8" maxlength="8" onkeypress="javascript:moneyonly();">
      </td>
      <td nowrap class="data_cell" width="60">
        <input type="text" class="money" name="total_price_{$n}" value="{$total_price[item]|escape}" size="8" maxlength="9" onkeypress="javascript:digit_and_sign();">
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
  <input type="submit" name="check" value="����ǧ��" style="width:80px;">
  <INPUT TYPE="SUBMIT" VALUE="��¸" name="save" style="width:80px;">
  <hr size="1">
  </form>
  <form method=post action="OlutMainMenu.php">
  <input type="submit" value="��˥塼�����" name="cancel">
  </form>

</table>
</body>



</html>

