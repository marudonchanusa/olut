<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title> ���ʥޥ������ƥʥ�</title>
<link rel="stylesheet" href="css/master.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<body>

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
<center>
<b>���ʥޥ������ƥʥ�</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>ɽ������</B><br>
<input type="submit" name="clear" value="������Ͽ">
<input type="submit" name="prev" value="����ɽ��">
<input type="submit" name="next" value="��ɽ����"> 
 ���ʥ����ɡ�<input type="text" class="hankaku" name="scode" value=""  size="10" maxlength="5">
<input type="submit" name="search" value="ɽ��">
</td>
</table>
<hr size="0">

<table bgcolor="#808080">

    {if $error ne ""}
        <tr>
            <td bgcolor="yellow" colspan="2">
              {$error}
            </td>
        </tr>
    {/if}
    
<tr><td>

  <table border="0" cellpadding="5">
    <tr>
      <td nowrap class="name_cell">���ʥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="{$post.code|escape}" size="6" maxlength="5" onkeypress="javascript:digitonly();" {if $post.code ne ''} readonly {/if}></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">����̾��</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="{$post.name|escape}" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">����̾�Ρʥ��ʡ�</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="{$post.name_k|escape}" size="30" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">ʪή�����ʬ</td>
      <td nowrap class="input_cell"><select name="dist_flag">{$dist_flag_list}</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">����ñ��</td>
      <td nowrap class="input_cell"><select name="unit_code">{$unit_code_list}</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">����ñ��</td>
      <td nowrap class="input_cell"><select name="order_unit_code">{$order_unit_code_list}</select></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">����ñ�������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="order_amount" value="{$post.order_amount|escape}" size="6" maxlength="5"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����å�ñ��</td>
      <td nowrap class="input_cell"><select name="lot_unit_code">{$lot_unit_code_list}</select></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����å������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="lot_amount" value="{$post.lot_amount|escape}" size="6" maxlength="5"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">��������вٸ�</td>
      <td nowrap class="input_cell"><select name="ship_section_code">{$ship_section_code_list}</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">�����ʲ��ܥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="accd" value="{$post.accd|escape}" size="4" maxlength="2"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����ʺ��ܥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="itmcd" value="{$post.itmcd|escape}" size="4" maxlength="2"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">����ʬ�ॳ����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="com_class_code" value="{$post.com_class_code|escape}" size="9" maxlength="5" onkeypress="javascript:digitonly();"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">�ؼ����ʬ</td>
      <td nowrap class="input_cell"><select name="order_sheet_flag">{$order_sheet_flag_list}</select></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">�߸˰����ʬ</td>
      <td nowrap class="input_cell"><select name="stock_flag">{$stock_flag_list}</select></td>
    </tr>
    <tr>
      <td nowrap class="name_cell">���߽в�ñ��</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="current_unit_price" value="{$post.current_unit_price|escape}" size="12" maxlength="10" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">���в�ñ��</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="next_unit_price" value="{$post.current_unit_price|escape}" size="12" maxlength="10" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">JAN������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="jancd" value="{$post.jancd|escape}" size="30" maxlength="20" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">���׽����ե饰</td>
      <td nowrap class="input_cell"><select name="depletion_flag"> {$depletion_flag_list}" </select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">��������</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="note" value="{$post.note|escape}" size="50" maxlength="255"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">��������(���ʡ�</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="note_k" value="{$post.note_k|escape}" size="50" maxlength="255"></td>
    </tr>
       
  </table>
</td>
</table>
  <br>
  <input type="submit" name="reload" value="������ɡ�" {if $post.code eq ''} disabled {/if}>
  <input type="submit" name="save" value="���ݡ�¸��">     
  <input type="submit" name="delete" value="  �����" onclick="return confirm('������Ƶ������Ǥ�����');" {if $post.code eq ''} disabled {/if}>   
<hr size="1">
</form>
<form method=post action="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="��˥塼�����" >
</FORM>

</center>

</td>
</table>
</center>


