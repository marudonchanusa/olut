<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title>取引先マスタ参照</title>
</head>
<body link="#FF0000" vlink="#FF0000">

<form method="post">

<center>
<table border="0" width="90%">
<tr>
<td>
<center>取引先マスタ参照</center>
<hr size="1">
<div align="center">
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="あ行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="か行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="さ行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="た行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="な行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="は行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="ま行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="や行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="ら行"></td>
      <td width="10%" align="center"><input type="submit" name="item_letter" value="わ行"></td>
    </tr>
  </table>
</div>


<div align="center">
  <hr size="1">
<table border="0" cellpadding="3" cellspacing="0" bgcolor="#808080">
  <tr><td>

  <table border="0" width="600">
    <tr>
      <td nowrap bgcolor="#FFCCCC" width="1">　</td>
      <td nowrap bgcolor="#FFCCCC" width="1">　</td>
      <td bgcolor="#FFCCCC" width="60">コード</td>
      <td bgcolor="#FFCCCC" width="190">取引先名</td>
      <td bgcolor="#FFCCCC" width="1">　</td>
      <td bgcolor="#FFCCCC" width="1">　</td>
      <td nowrap bgcolor="#FFCCCC" width="1">　</td>
      <td bgcolor="#FFCCCC" width="60">コード</td>
      <td bgcolor="#FFCCCC" width="200%">取引先名</td>
    </tr>

    {assign var="count" value=0}
    {section name=item start=0 loop=$master_display_lines step=1}
    
    {if $count is even}
    <tr>
      <td nowrap bgcolor="#FFFFFF" width="1">{$smarty.section.item.index+1}</td>
      <td nowrap bgcolor="#FFFFFF" width="1"><input type="radio" name="vendor_code" value="{$post[item][0]}"></td>
      <td nowrap bgcolor="#FFFFFF" width="60">{$post[item][0]|escape}</td>
      <td nowrap bgcolor="#FFFFFF" width="190">{$post[item][1]|escape}</td>
      <td nowrap bgcolor="#FFFFFF" width="1">　</td>
      {else}
      
      <td nowrap bgcolor="#FFFFFF" width="1">{$smarty.section.item.index+1}</td>
      <td nowrap bgcolor="#FFFFFF" width="1"><input type="radio" name="vendor_code" value="{$post[item][0]|escape}"></td>
      <td nowrap bgcolor="#FFFFFF" width="60">{$post[item][0]|escape}　</td>
      <td nowrap bgcolor="#FFFFFF" width="200%">{$post[item][1]|escape}　</td>
    </tr>
    {/if}
    {assign var="count" value=$count+1}

    {/section}

  </table>
  </td>
  </table>
  <hr size="1">
  <input type="submit" name="prev" value="　<<前　">   
  <input type="submit" name="select" value="　選択　">
  <input type="submit" name="next" value = "　後>>　">   
<br>
<hr size="1">

<FONT SIZE=-1><INPUT TYPE="submit" name="cancel" VALUE="キャンセル"><BR></FONT>

</center>
</FORM>
</body>
