<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title> 商品マスタメンテナンス</title>
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
<b>商品マスタメンテナンス</b>
<form method="POST">

<hr size="0">
<table border=0>
<tr>

<td>
<B>表示選択</B><br>
<input type="submit" name="clear" value="新規登録">
<input type="submit" name="prev" value="←前表示">
<input type="submit" name="next" value="次表示→"> 
 商品コード：<input type="text" class="hankaku" name="scode" value=""  size="10" maxlength="5">
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
      <td nowrap class="name_cell">商品コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="code" value="{$post.code|escape}" size="6" maxlength="5" onkeypress="javascript:digitonly();" {if $post.code ne ''} readonly {/if}></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">商品名称</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name" value="{$post.name|escape}" size="30" maxlength="40"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">商品名称（カナ）</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="name_k" value="{$post.name_k|escape}" size="30" maxlength="50"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">物流指定区分</td>
      <td nowrap class="input_cell"><select name="dist_flag">{$dist_flag_list}</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">管理単位</td>
      <td nowrap class="input_cell"><select name="unit_code">{$unit_code_list}</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">受注単位</td>
      <td nowrap class="input_cell"><select name="order_unit_code">{$order_unit_code_list}</select></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">受注単位内数量</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="order_amount" value="{$post.order_amount|escape}" size="6" maxlength="5"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">受注ロット単位</td>
      <td nowrap class="input_cell"><select name="lot_unit_code">{$lot_unit_code_list}</select></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">受注ロット内数量</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="lot_amount" value="{$post.lot_amount|escape}" size="6" maxlength="5"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">本部部門出荷元</td>
      <td nowrap class="input_cell"><select name="ship_section_code">{$ship_section_code_list}</select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">用度品科目コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="accd" value="{$post.accd|escape}" size="4" maxlength="2"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">用度品細目コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="itmcd" value="{$post.itmcd|escape}" size="4" maxlength="2"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">商品分類コード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="com_class_code" value="{$post.com_class_code|escape}" size="9" maxlength="5" onkeypress="javascript:digitonly();"></td>
    </tr>
    
     <tr>
      <td nowrap class="name_cell">指示書区分</td>
      <td nowrap class="input_cell"><select name="order_sheet_flag">{$order_sheet_flag_list}</select></td>
    </tr>
        
    <tr>
      <td nowrap class="name_cell">在庫引落区分</td>
      <td nowrap class="input_cell"><select name="stock_flag">{$stock_flag_list}</select></td>
    </tr>
    <tr>
      <td nowrap class="name_cell">現在出荷単価</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="current_unit_price" value="{$post.current_unit_price|escape}" size="12" maxlength="10" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">来月出荷単価</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="next_unit_price" value="{$post.current_unit_price|escape}" size="12" maxlength="10" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">JANコード</td>
      <td nowrap class="input_cell"><input type="text" class="hankaku" name="jancd" value="{$post.jancd|escape}" size="30" maxlength="20" onkeypress="javascript:moneyonly();"></td>
    </tr>

    <tr>
      <td nowrap class="name_cell">減耗処理フラグ</td>
      <td nowrap class="input_cell"><select name="depletion_flag"> {$depletion_flag_list}" </select></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">商品備考</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="note" value="{$post.note|escape}" size="50" maxlength="255"></td>
    </tr>
    
    <tr>
      <td nowrap class="name_cell">商品備考(カナ）</td>
      <td nowrap class="input_cell"><input type="text" class="zenkaku" name="note_k" value="{$post.note_k|escape}" size="50" maxlength="255"></td>
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


