<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>���вټ���ɽ��</title>
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
        <td>DATE��</td>
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<p align="center"><b>���вټ���ɽ��</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border="0" cellpadding=1 width=700px  bgcolor="#808080">
<tr>
  <td nowrap align="center" class=header_cell width=15%>
   ���ʥ�����
  </td>
  <td nowrap align="left" class=header_cell width=35%>
    <input type="text" class="hankaku" name="commodity_code" value="{$commodity_code}" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="commodity_ref" value="����">
    {$commodity_name}
  </td>
  <TD nowrap align="center" class=header_cell width=25%>
    ����(YYYYMMDD)
  </TD>
  <TD nowrap class=header_cell width=25%>
    <input type="text" class="hankaku" name="date_from" value="{$date_from}" size="10" maxlength="8" onkeypress="javascript:digitonly();" > -
    <input type="text" class="hankaku" name="date_to" value="{$date_to}" size="10" maxlength="8" onkeypress="javascript:digitonly();">
    <input type="submit" name="check" value="�����׳��ϡ�" style="width:80px;">
  </TD>
</tr>

<tr>
  <td colspan=2 class=header_cell align=center> ���߽в�ñ��&nbsp; {$commodity_unit_price} &nbsp; ��</td>
  <td colspan=2 class=header_cell align=center> ���̤�ñ�� &nbsp; {$commodity_unit_name} </td>
</tr>

<tr>
  <td nowrap align="center" colspan=4  class=header_cell>
  <table border=0 cellpadding=2 width=100% bgcolor="#808080">
  <tr>
   <td width=16% class=header_cell> ���Ϻ߸� </td> <td width=16% class=data_cell align=right>{$stock_start_amount}</td>    <td width=16% class=data_cell align=right> {$stock_start_price} </td>
   <td width=16% class=header_cell> ���ٹ�� </td> <td width=16% class=data_cell align=right>{$arrival_total_amount} </td> <td width=16% class=data_cell align=right> {$arrival_total_price}</td>
  </tr>

  <tr>
   <td width=16% class=header_cell> ��λ�߸� </td> <td class=data_cell align=right>{$stock_end_amount}</td>       <td class=data_cell align=right> {$stock_end_price} </td>
   <td width=16% class=header_cell> �вٹ�� </td> <td class=data_cell align=right>{$shipment_total_amount} </td> <td class=data_cell align=right> {$shipment_total_price}</td>
  </tr>

  <tr>
   <td width=16% class=header_cell> ���߸� </td> <td class=data_cell align=right>{$stock_current_amount}</td>    <td class=data_cell align=right> {$stock_current_price} </td>
   <td width=16% class=header_cell> �����⺹�׹� </td> <td  class=data_cell colspan=2 align=right>{$balance}</td>
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
      <td class="header_cell" nowrap width="5%">��</td>
      <td class="header_cell" nowrap align="center">����</td>
      <td class="header_cell" align="center" colspan=2>�в�</td>
      <td class="header_cell" align="center" colspan=2>����</td>
    </tr>
    
    <!-- ��������롼�� -->
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
  <input type="submit" value="��˥塼�����" name="cancel">
  </form>

</table>
</body>



</html>

