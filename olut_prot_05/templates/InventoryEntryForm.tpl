<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>ê������ </title>
<link rel="stylesheet" href="css/inventory_entry.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>
<body>
<center>
<form method="POST">

<table border="0" width="90%">
<tr>
<td valign="top"><br>
</td>
<td align="right">
    <table border="0" cellpadding="0" cellspacing="0" height="18">
      <tr>
        <td height="18">DATE��</td>
        <td height="18"><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<p align="center"><b>{$target_year}ǯ{$target_month}��ê������</b>
<hr size="0">

  <div align="center">
    <center>

<table border=0 cellpadding="4">
<tr><td>
<div align="center">
  <table border="1" cellpadding="3" cellspacing="0" bgcolor="#FFFFCC" bordercolor="#800000" height="51">
    <tr>
      <td nowrap height="15">���������</td>
      <td nowrap height="15">{$ship_section_name}</td>
    </tr>
    <tr>
      <td nowrap height="16">�Ҹˡʶ�ۡ�</td>
      <td nowrap height="16">{$warehouse_name}</td>
    </tr>
  </table>
</div>
</td>
  <td nowrap valign="bottom">
����
</td>
  <td nowrap valign="bottom">
<B>ɽ������</B><br>
<font size=-1>
<input type="submit" name="prev" value="����ɽ��">
<input type="submit" name="next" value="��ɽ����">           
 &nbsp;        
</font>
</td>
  <td nowrap>
������
</td>
  <td valign="bottom" nowrap>
���ʥ����ɡ�<input type="text" class="hankaku" name="search_from" value="{$search_from}"  size="7" maxlength="5">  
<input type="submit" name="search" value="ɽ��">
</td>

{if $new_inventory_mode==false}
<td valign="bottom" nowrap>
  <input type="submit" name="new_inventory" value="�����ɲ�">
</td>
{/if}

</tr>
</table>
    </center>
  </div>
<hr size="0">

  <div align="center">
  <table border="0" cellpadding="3" cellspacing="0" bgcolor="#808080">
  <tr><td>
  <table border="0" width="600">
  
   {if $error ne ""}
    <tr>
        <td bgcolor="yellow" colspan="8">
          {$error}
        </td>
    </tr>
  {/if} 
  
    {if $commodity_count > 0 || $new_inventory_mode==true }
    <tr>
      <td class="header_cell" width="80">���ʥ�����</td>
      <td class="header_cell" nowrap width="280">����̾</td>
      <td class="header_cell" width="120" nowrap>
        <p align="center">�׻�����</p>
      </td>
      <td class="header_cell" width="120">
        <p align="center">�º߿���</p>
      </td>
      <td class="header_cell" width="120">
        <p align="center">�׻�ñ��</p>
      </td>      
      <td class="header_cell" width="120">
        <p align="center">�׻��߸˶��</p>
      </td>  
      <td class="header_cell" width="120">
        <p align="center">�߸˶��</p>
      </td>      
      {if $new_inventory_mode==false }
      <td class="header_cell" width="120">
        <p align="center">��ۼ�����</p>
      </td> 
      {/if}
     </tr>
    {/if}
    
    {if $new_inventory_mode == true}

      <!-- start add new line -->
      <tr>
        <td nowrap class="data_cell" width="80" align="center">
          <nobr>
          <input type="text" name="commodity_code"  value="{$commodity_code}" maxlength=7>
          <input type="submit" name="commodity_ref" value="H">
          </nobr>
        </td>
        <td nowrap class="data_cell" width="280">{$commodity_name}</td>

        <td nowrap class="data_cell" width="100">
          &nbsp;
        </td>

        <td nowrap class="data_cell" width="120" align="center">
          <div align="center">
            <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="60%">
                <input type="text" class="amount" name="inventory_amount" value="{$inventory_amount}" size="10" maxlength="6" onkeypress="javascript:digit_and_sign();">
              </td>
              <td width="40%" nowrap>{$unit_name|trim}</td>
            </tr>
            </table>
          </div>
        </td>
      
        <td nowrap class="data_cell" width="100">
        &nbsp;
        </td>
      
        <td nowrap class="data_cell" width="100">
        &nbsp;
        </td>
      
        <td nowrap class="data_cell" width="100" align="center">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="100%">
              <input type="text" class="amount" name="inventory_price" value="{$inventory_price}" size="12" maxlength="11" onkeypress="javascript:digit_and_sign();">
            </td>            
          </tr>
          </table>
          </div>
        </td>
      </tr>    
    
    {else}
    
      <!--- line start for normal operation -->
      {assign var="n" value=0 }
      {section name=index start=0 loop=$commodity_count step=1}

      <tr>
        <td nowrap class="data_cell" width="80" align="center">{$commodity_codes[index]}</td>
        <td nowrap class="data_cell" width="250">{$commodity_names[index]}</td>

        <td nowrap class="data_cell" width="100">
          <div align="center">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td align=right width="60%">{$amounts[index]}</td>
                <td width="40%">{$unit_names[index]|trim}</td>
              </tr>
            </table>
          </div>
        </td>

        <td nowrap class="data_cell" width="120" align="center">
          <div align="center">
            <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="60%">
                <input type="text" class="amount" name="inventory_amount_{$n}" value="{$inventory_amount[index]}" size="10" maxlength="6" onkeypress="javascript:digit_and_sign();">
              </td>
              <td width="40%" nowrap>{$unit_names[index]|trim}</td>
            </tr>
            </table>
          </div>
        </td>
      
        <td nowrap class="data_cell" width="100">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td align=right width="70%">{$unit_price[index]}</td>
              <td width="30%">��</td>
            </tr>
          </table>
        </div>
        </td>
      
        <td nowrap class="data_cell" width="100">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td align=right width="70%">{$total_price[index]}</td>
              <td width="30%">��</td>
            </tr>
          </table>
        </div>
        </td>
      
        <td nowrap class="data_cell" width="100" align="center">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="100%">
              <input type="text" class="amount" name="inventory_price_{$n}" value="{$inventory_price[index]}" size="12" maxlength="11" onkeypress="javascript:digit_and_sign();">
            </td>            
          </tr>
          </table>
          </div>
        </td>
        
        <td nowrap class="data_cell" width="100" align="center">
        {if $calc_flag[index] == '1'}
          <input type="checkbox" name="calc_flag_{$n}" checked>
        {else}
          <input type="checkbox" name="calc_flag_{$n}">        
        {/if}
          
        </td>
      
      </tr>
      {assign var="n" value=$n+1}
      {/section}
      <!-- line end -->
      {/if}
    
  </table>
  </td>
    </tr>
 </table>

<hr size="0">
<br>
  <center>  
  {if $new_inventory_mode==true}
  <input type="submit" name="check_new_commodity" value="����ǧ��"> 
  <input type="submit" name="save_new_commodity" value="����¸��"> 
  {else}
  <input type="submit" name="calc" value="���Ʒ׻���">
  <input type="submit" name="save" value="����¸��">
  {/if}
  </center>
</form>
<hr size="1">
<FORM ACTION="InventoryEntry.php">
  <center>
  <INPUT TYPE="SUBMIT" VALUE="���Ҹ��������롡">
  </center>
</FORM>

</td>
</table>
</body>
