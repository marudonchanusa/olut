<?php /* Smarty version 2.6.9, created on 2005-08-07 12:40:49
         compiled from StoreReferenceForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'StoreReferenceForm.tpl', 62, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="content-language" content="ja">
<title>店舗マスタ参照</title>
</head>
<body link="#FF0000" vlink="#FF0000">

<form method="post">

<center>
<table border="0" width="90%">
<tr>
<td>
<center>店舗マスタ参照</center>
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
      <td bgcolor="#FFCCCC" width="190">店舗名</td>
      <td bgcolor="#FFCCCC" width="1">　</td>
      <td bgcolor="#FFCCCC" width="1">　</td>
      <td nowrap bgcolor="#FFCCCC" width="1">　</td>
      <td bgcolor="#FFCCCC" width="60">コード</td>
      <td bgcolor="#FFCCCC" width="200%">店舗名</td>
    </tr>

    <?php $this->assign('count', 0); ?>
    <?php unset($this->_sections['item']);
$this->_sections['item']['name'] = 'item';
$this->_sections['item']['start'] = (int)0;
$this->_sections['item']['loop'] = is_array($_loop=$this->_tpl_vars['master_display_lines']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
    
    <?php if (!(1 & $this->_tpl_vars['count'])): ?>
    <tr>
      <td nowrap bgcolor="#FFFFFF" width="1"><?php echo $this->_sections['item']['index']+1; ?>
</td>
      <td nowrap bgcolor="#FFFFFF" width="1"><input type="radio" name="store_code" value="<?php echo $this->_tpl_vars['post'][$this->_sections['item']['index']][0]; ?>
"></td>
      <td nowrap bgcolor="#FFFFFF" width="60"><?php echo ((is_array($_tmp=$this->_tpl_vars['post'][$this->_sections['item']['index']][0])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
      <td nowrap bgcolor="#FFFFFF" width="190"><?php echo ((is_array($_tmp=$this->_tpl_vars['post'][$this->_sections['item']['index']][1])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
      <td nowrap bgcolor="#FFFFFF" width="1">　</td>
      <?php else: ?>
      
      <td nowrap bgcolor="#FFFFFF" width="1"><?php echo $this->_sections['item']['index']+1; ?>
</td>
      <td nowrap bgcolor="#FFFFFF" width="1"><input type="radio" name="store_code" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post'][$this->_sections['item']['index']][0])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"></td>
      <td nowrap bgcolor="#FFFFFF" width="60"><?php echo ((is_array($_tmp=$this->_tpl_vars['post'][$this->_sections['item']['index']][0])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
　</td>
      <td nowrap bgcolor="#FFFFFF" width="200%"><?php echo ((is_array($_tmp=$this->_tpl_vars['post'][$this->_sections['item']['index']][1])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
　</td>
    </tr>
    <?php endif; ?>
    <?php $this->assign('count', $this->_tpl_vars['count']+1); ?>

    <?php endfor; endif; ?>

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