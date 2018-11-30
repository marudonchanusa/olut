<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>入荷伝票修正選択</title>
<link rel="stylesheet" href="css/arrival_of_goods.css" type="text/css" />
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>
<!-- 
<script>
history.forward();
</script> -->

<body class="body">
<body bgcolor="#CCFFCC" link="#000080" vlink="#000080">
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
<b>入荷伝票修正選択</b>
<form method="POST">     
<hr size="0">

  <div align="center">
    <table border="0" cellpadding="0" cellspacing="0">
    {if $error ne ""}
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td bgcolor="yellow" colspan="2">
          {$error}
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    {/if}
      <tr>
        <td align="right">入荷日(YYYYMMDD)</td>
        <td>
          <input type="text" class="hankaku" name="date_from" value="{$date_from}" size="12" maxlength="8">
          -
          <input type="text" class="hankaku" name="date_to" value="{$date_to}" size="12" maxlength="8"> 
          &nbsp;</td>     
        <td valign="bottom"> 
          <input type="submit" name="find" value="　抽　出　">   
{if $count > 0}
          <input type="submit" name="modify" value="　修　正　" style="width:80px;">
          <input type="submit" name="delete" value="　削　除　" style="width:80px;" onclick="return confirm('削除して宜しいですか？');">
{/if}          
        </td>
      </tr>
    </table>
  </div>

<table>
<tr><td>

  <hr size="1">

  <table border="0" cellpadding="3" cellspacing="0">
  {if $count > 0 }
    <tr>
      <td nowrap bgcolor="#FFCCCC">　</td>
      <td bgcolor="#FFCCCC">
        入荷日
      </td>
      <td bgcolor="#FFCCCC" align="center">
        伝票番号
      </td>
      <td bgcolor="#FFCCCC" align="left">
        商品名
      </td>
      <td bgcolor="#FFCCCC" align="left">
        仕入先名
      </td>
    </tr>
    {/if}
    <!-- ここからループ -->
    {assign var="n" value=0}
    {section name=item start=0 loop=$count step=1}
    <tr>
      <td nowrap bgcolor="#FFFFFF"><input type="radio" name="arg" value="select_{$arg[item]}" ></td>
      <td nowrap bgcolor="#FFFFFF">{$date[item]}</td>
      <td nowrap bgcolor="#FFFFFF">{$slip_no[item]}</td>
      <td nowrap bgcolor="#FFFFFF">{$commodity_name[item]}</td>
      <td nowrap bgcolor="#FFFFFF">{$vendor_name[item]}</td>
    </tr>
    {assign var="n" value=$n+1}
    {/section}

  </table>
  <hr size="1">
</td>
</table>  

  <hr size="1">
  </form>
  <form method=post action="OlutArrivalOfGoodsMenu.php">
  <input type="submit" value="メニューへ戻る" name="cancel">
  </form>

</center>

</td>
</table>
</center>
</body>

</html>
