<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title> Ź�ޥޥ������ƥʥ�</title>
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
<b>Ź�ޥޥ������ƥʥ�</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>ɽ������</B><br>
<input type="submit" name="clear" value="������Ͽ">
<input type="submit" name="prev" value="����ɽ��">
<input type="submit" name="next" value="��ɽ����"> 
 ����襳���ɡ�<input type="text" class="hankaku" name="search_code" value=""  size="10" maxlength="5">
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
      <td nowrap class="name_cell">Ź�ޥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="{$post.code|escape}" size="6" maxlength="5" onkeypress="javascript:digitonly();" {if $post.code ne ''} readonly {/if}></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����å��ǥ��å�</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="ckd" value="{$post.ckd|escape}" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">ɸ��Ź�ޥ�����</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="scode" value="{$post.scode|escape}" size="6" maxlength="5" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����å��ǥ��å�</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="sckd" value="{$post.ckd|escape}" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">Ź������������</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="isdcd" value="{$post.isdcd|escape}" size="18" maxlength="15" onkeypress="javascript:digitonly();"></td>
    </tr>    
    
    <tr>
      <td nowrap class="name_cell">Ź��̾��</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="{$post.name|escape}" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">Ź��̾�Ρʥ��ʡ�</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="{$post.name_k|escape}" size="30" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">�����ֹ�</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="tel" value="{$post.tel|escape}" size="30" maxlength="12" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">�ե��å���</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="fax" value="{$post.fax|escape}" size="30" maxlength="12" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">͹���ֹ�</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="zip" value="{$post.zip|escape}" size="30" maxlength="7" onkeypress="javascript:digitonly();"></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">����</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="address" value="{$post.address|escape}" size="50" maxlength="50"></td>
    </tr>

     <tr>
      <td nowrap class="name_cell">���������ե饰</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="print_flag" value="{$post.print_flag|escape}" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">��Źǯ����(YYYYMMDD)</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="open_date" value="{$post.open_date|escape}" size="9" maxlength="8" onkeypress="javascript:digitonly();"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">��Źǯ����(YYYYMMDD)</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="close_date" value="{$post.close_date|escape}" size="9" maxlength="8" onkeypress="javascript:digitonly();"></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">�᡼�륢�ɥ쥹</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="mail_address" value="{$post.mail_address|escape}" size="50" maxlength="255"></td>
    </tr>
    <tr>
      <td nowrap class="name_cell">�����֥��ɥ쥹</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="web_url" value="{$post.web_url|escape}" size="50" maxlength="255"></td>
    </tr>
    
    <tr>
      <td colspan=2 >
      <table border="0" cellpadding="5" cellspacing=0 width=100%>
      <!-- ��������롼�� -->
      {assign var="n" value=0}
      {section name=item start=0 loop=5 step=1}
      <tr>
        <td nowrap class="name_cell" width=25%>Ź��������{$n+1}</td>
        <td nowrap class="input_cell" width=25%><select name="store_section_{$n}">{$store_section_list[$n]}</select></td>
        {assign var="n" value=$n+1}
        <td nowrap class="name_cell" width=25%>Ź��������{$n+1}</td>
        <td nowrap class="input_cell" width=25%><select name="store_section_{$n}">{$store_section_list[$n]}</select></td>
        {assign var="n" value=$n+1}
        <td nowrap class="name_cell" width=25%>Ź��������{$n+1}</td>
        <td nowrap class="input_cell" width=25%><select name="store_section_{$n}">{$store_section_list[$n]}</select></td>
        {assign var="n" value=$n+1}        
      </tr>
      {/section}
      </table>
      </td>
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


