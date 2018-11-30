<?php /* Smarty version 2.6.9, created on 2005-08-25 18:35:19
         compiled from ArrivalOfGoodsSelectionForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'ArrivalOfGoodsSelectionForm.tpl', 26, false),)), $this); ?>
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
        <td><font size="3"><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
</font></td>
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
    <?php if ($this->_tpl_vars['error'] != ""): ?>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td bgcolor="yellow" colspan="2">
          <?php echo $this->_tpl_vars['error']; ?>

        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <?php endif; ?>
      <tr>
        <td align="right">入荷日(YYYYMMDD)</td>
        <td>
          <input type="text" class="hankaku" name="date_from" value="<?php echo $this->_tpl_vars['date_from']; ?>
" size="12" maxlength="8">
          -
          <input type="text" class="hankaku" name="date_to" value="<?php echo $this->_tpl_vars['date_to']; ?>
" size="12" maxlength="8"> 
          &nbsp;</td>     
        <td valign="bottom"> 
          <input type="submit" name="find" value="抽出">   
        </td>
      </tr>
    </table>
  </div>

<table>
<tr><td>

  <hr size="1">

  <table border="0" cellpadding="3" cellspacing="0">
  <?php if ($this->_tpl_vars['count'] > 0): ?>
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
    <?php endif; ?>
    <!-- ここからループ -->
    <?php $this->assign('n', 0); ?>
    <?php unset($this->_sections['item']);
$this->_sections['item']['name'] = 'item';
$this->_sections['item']['start'] = (int)0;
$this->_sections['item']['loop'] = is_array($_loop=$this->_tpl_vars['count']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['item']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['item']['show'] = true;
$this->_sections['item']['max'] = $this->_sections['item']['loop'];
if ($this->_sections['item']['start'] < 0)
    $this->_sections['item']['start'] = max($this->_sections['item']['step'] > 0 ? 0 : -1, $this->_sections['item']['loop'] + $this->_sections['item']['start']);
else
    $this->_sections['item']['start'] = min($this->_sections['item']['start'], $this->_sections['item']['step'] > 0 ? $this->_sections['item']['loop'] : $this->_sections['item']['loop']-1);
if ($this->_sections['item']['show']) {
    $this->_sections['item']['total'] = min(ceil(($this->_sections['item']['step'] > 0 ? $this->_sections['item']['loop'] - $this->_sections['item']['start'] : $this->_sections['item']['start']+1)/abs($this->_sections['item']['step'])), $this->_sections['item']['max']);
    if ($this->_sections['item']['total'] == 0)
        $this->_sections['item']['show'] = false;
} else
    $this->_sections['item']['total'] = 0;
if ($this->_sections['item']['show']):

            for ($this->_sections['item']['index'] = $this->_sections['item']['start'], $this->_sections['item']['iteration'] = 1;
                 $this->_sections['item']['iteration'] <= $this->_sections['item']['total'];
                 $this->_sections['item']['index'] += $this->_sections['item']['step'], $this->_sections['item']['iteration']++):
$this->_sections['item']['rownum'] = $this->_sections['item']['iteration'];
$this->_sections['item']['index_prev'] = $this->_sections['item']['index'] - $this->_sections['item']['step'];
$this->_sections['item']['index_next'] = $this->_sections['item']['index'] + $this->_sections['item']['step'];
$this->_sections['item']['first']      = ($this->_sections['item']['iteration'] == 1);
$this->_sections['item']['last']       = ($this->_sections['item']['iteration'] == $this->_sections['item']['total']);
?>
    <tr>
      <td nowrap bgcolor="#FFFFFF"><input type="radio" name="arg" value="select_<?php echo $this->_tpl_vars['slip_no'][$this->_sections['item']['index']]; ?>
" ></td>
      <td nowrap bgcolor="#FFFFFF"><?php echo $this->_tpl_vars['date'][$this->_sections['item']['index']]; ?>
</td>
      <td nowrap bgcolor="#FFFFFF"><?php echo $this->_tpl_vars['slip_no'][$this->_sections['item']['index']]; ?>
</td>
      <td nowrap bgcolor="#FFFFFF"><?php echo $this->_tpl_vars['commodity_name'][$this->_sections['item']['index']]; ?>
</td>
      <td nowrap bgcolor="#FFFFFF"><?php echo $this->_tpl_vars['vendor_name'][$this->_sections['item']['index']]; ?>
</td>
    </tr>
    <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
    <?php endfor; endif; ?>

  </table>
  <hr size="1">
</td>
</table>  

<?php if ($this->_tpl_vars['count'] > 0): ?>
  <hr size="1">
  <input type="submit" name="modify" value="　修正　" style="width:80px;">
  <input type="submit" name="delete" value="　削除　" style="width:80px;" onclick="return confirm('削除して宜しいですか？');">
  
<?php endif; ?>

  <hr size="1">
  </form>
  <form method=post action="OlutMainMenu.php">
  <input type="submit" value="終了" name="cancel">
  </form>

</center>

</td>
</table>
</center>
</body>

</html>