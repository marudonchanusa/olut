<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title>取引先マスタメンテナンス</title>
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
        <td>DATE：</td>
        <td><font size="3">{$smarty.now|date_format:"%Y/%m/%d"}</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<center>
<b>取引先マスタメンテナンス</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>表示選択</B><br>
<input type="submit" name="clear" value="新規登録">
<input type="submit" name="prev" value="←前表示">
<input type="submit" name="next" value="次表示→"> 
 取引先コード：<input type="text" class="hankaku" name="scode" value=""  size="10" maxlength="5">
<input type="submit" name="search" value="表示">
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
      <td nowrap class="name_cell">取引先コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="{$post.code|escape}" size="6" maxlength="5" onkeypress="javascript:digitonly();" {if $post.code ne ''} readonly {/if}></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">チェックデジット</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="ckd" value="{$post.ckd|escape}" size="1" maxlength="1" onkeypress="javascript:digitonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">取引先名称</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="{$post.name|escape}" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">取引先名（カナ）</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="{$post.name_k|escape}" size="30" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">内部コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="isdcd" value="{$post.isdcd|escape}" size="8" maxlength="6" onkeypress="javascript:digitonly();"></td>
    </tr>
    <tr>
    
      <td nowrap class="name_cell">電話番号</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="tel" value="{$post.tel|escape}" size="30" maxlength="12" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">ファックス</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="fax" value="{$post.fax|escape}" size="30" maxlength="12" onkeypress="javascript:digitonly();"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">郵便番号</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="zip" value="{$post.zip|escape}" size="30" maxlength="7" onkeypress="javascript:digitonly();"></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">住所</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="address" value="{$post.address|escape}" size="50" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">メールアドレス</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="mail_address" value="{$post.mail_address|escape}" size="50" maxlength="255"></td>
    </tr>
    <tr>
      <td nowrap class="name_cell">ウエブアドレス</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="web_url" value="{$post.web_url|escape}" size="50" maxlength="255"></td>
    </tr>
        
  </table>
</td>
</table>
  <br>
  <input type="submit" name="reload" value="　リロード　" {if $post.code eq ''} disabled {/if}>
  <input type="submit" name="save" value="　保　存　">     
  <input type="submit" name="delete" value="  削　除　" onclick="return confirm('削除して宜しいですか？');" {if $post.code eq ''} disabled {/if}>   
<hr size="1">
</form>
<form method=post action="OlutMainMenu.php">
<INPUT TYPE="SUBMIT" VALUE="メニューへ戻る" >
</FORM>

</center>

</td>
</table>
</center>


