<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>�߸˳�ǧ</title>
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
        <td>DATE��</td>
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<p align="center"><b>�߸˳�ǧ</b>


<hr size="0" align="center">
<center>
<table border="0">
<tr><td>

<TABLE border="0" cellpadding=2 width=600px>
<tr>
  <td nowrap align="right">
   ���ʥ�����
  </td>
  <td nowrap align="left">
    <input type="text" class="hankaku" name="commodity_code" value="{$commodity_code}" size="8" maxlength="5" onkeypress="javascript:digitonly();">
    <input type="submit" name="commodity_ref" value="����">
    {$commodity_name}
  </td>
</tr>

<TR>
  <TD nowrap align="right">
    ����
  </TD>
  <TD nowrap>
    {if $year eq '' }
    <input type="text" class="hankaku" name="year" value="{$smarty.now|date_format:"%Y"}" size="6" maxlength="4" onkeypress="javascript:digitonly();" >ǯ
    <input type="text" class="hankaku" name="month" value="{$smarty.now|date_format:"%m"}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >��
    <input type="text" class="hankaku" name="date" value="{$smarty.now|date_format:"%d"}" size="2" maxlength="2" onkeypress="javascript:digitonly();" >��
    {else}
    <input type="text" class="hankaku" name="year" value="{$year}" size="6" maxlength="8" onkeypress="javascript:digitonly();" >ǯ
    <input type="text" class="hankaku" name="month" value="{$month}" size="2" maxlength="2" onkeypress="javascript:digitonly();">��
    <input type="text" class="hankaku" name="date" value="{$date}" size="2" maxlength="2" onkeypress="javascript:digitonly();">��
    {/if}   
    
  </TD>
</tr>
<tr>
  <td nowrap align="center"colspan=2>
  <input type="submit" name="check" value="���������ϡ�" style="width:80px;">
  <input type="submit" name="prev" value="��������" style="width:80px;">
  <input type="submit" name="next" value="��������" style="width:80px;">
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
      <td class="header_cell" nowrap width="5%">��</td>
      <td class="header_cell" nowrap align="center">�Ҹ�̾</td>
      <td class="header_cell" width="20%"  align="center">�߸�</td>
      <td class="header_cell" width="20%"  align="center">����</td>
      <td class="header_cell" width="20%"  align="center">�и�</td>
    </tr>
    
    <!-- ��������롼�� -->
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
      <td nowrap class="data_cell" align="right" colspan=2>���Ҹ˹��
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
  <input type="submit" value="��˥塼�����" name="cancel" >
  </form>

</table>
</body>



</html>

