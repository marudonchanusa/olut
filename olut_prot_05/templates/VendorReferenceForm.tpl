<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title>�����ޥ�������</title>
</head>
<body link="#FF0000" vlink="#FF0000">

<form method="post">

<center>
<table border="0" width="90%">
<tr>
<td>
<center>�����ޥ�������</center>
<hr size="1">
<div align="center">
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="����"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="����"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="����"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="����"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="�ʹ�"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="�Ϲ�"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="�޹�"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="���"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="���"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="���"></td>
    </tr>
  </table>
</div>


<div align="center">
  <hr size="1">
<table border="0" cellpadding="3" cellspacing="0" bgcolor="#808080">
  <tr><td>

  <table border="0" width="600">
    <tr>
      <td nowrap bgcolor="#FFCCCC" width="1">��</td>
      <td nowrap bgcolor="#FFCCCC" width="1">��</td>
      <td bgcolor="#FFCCCC" width="60">������</td>
      <td bgcolor="#FFCCCC" width="190">�����̾</td>
      <td bgcolor="#FFCCCC" width="1">��</td>
      <td bgcolor="#FFCCCC" width="1">��</td>
      <td nowrap bgcolor="#FFCCCC" width="1">��</td>
      <td bgcolor="#FFCCCC" width="60">������</td>
      <td bgcolor="#FFCCCC" width="200%">�����̾</td>
    </tr>

    {assign var="count" value=0}
    {section name=item start=0 loop=$master_display_lines step=1}
    
    {if $count is even}
    <tr>
      <td nowrap bgcolor="#FFFFFF" width="1">{$smarty.section.item.index+1}</td>
      <td nowrap bgcolor="#FFFFFF" width="1"><input type="radio" name="vendor_code" value="{$post[item][0]}"></td>
      <td nowrap bgcolor="#FFFFFF" width="60">{$post[item][0]|escape}</td>
      <td nowrap bgcolor="#FFFFFF" width="190">{$post[item][1]|escape}</td>
      <td nowrap bgcolor="#FFFFFF" width="1">��</td>
      {else}
      
      <td nowrap bgcolor="#FFFFFF" width="1">{$smarty.section.item.index+1}</td>
      <td nowrap bgcolor="#FFFFFF" width="1"><input type="radio" name="vendor_code" value="{$post[item][0]|escape}"></td>
      <td nowrap bgcolor="#FFFFFF" width="60">{$post[item][0]|escape}��</td>
      <td nowrap bgcolor="#FFFFFF" width="200%">{$post[item][1]|escape}��</td>
    </tr>
    {/if}
    {assign var="count" value=$count+1}

    {/section}

  </table>
  </td>
  </table>
  <hr size="1">
  <input type="submit" name="prev" value="��<<����">   
  <input type="submit" name="select" value="������">
  <input type="submit" name="next" value = "����>>��">   
<br>
<hr size="1">

<FONT SIZE=-1><INPUT TYPE="submit" name="cancel" VALUE="����󥻥�"><BR></FONT>

</center>
</FORM>
</body>
