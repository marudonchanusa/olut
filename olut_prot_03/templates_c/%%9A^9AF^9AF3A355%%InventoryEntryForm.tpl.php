<?php /* Smarty version 2.6.9, created on 2005-09-28 18:10:18
         compiled from InventoryEntryForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'InventoryEntryForm.tpl', 22, false),array('modifier', 'trim', 'InventoryEntryForm.tpl', 141, false),)), $this); ?>
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>棚卸入力 </title>
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
        <td height="18">DATE：</td>
        <td height="18"><font size="3"><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
</font></td>
      </tr>
    </table>
</td>
<tr>
<td colspan=2>
<p align="center"><b><?php echo $this->_tpl_vars['target_year']; ?>
年<?php echo $this->_tpl_vars['target_month']; ?>
月棚卸入力</b>
<hr size="0">

  <div align="center">
    <center>

<table border=0 cellpadding="4">
<tr><td>
<div align="center">
  <table border="1" cellpadding="3" cellspacing="0" bgcolor="#FFFFCC" bordercolor="#800000" height="51">
    <tr>
      <td nowrap height="15">資材内部署</td>
      <td nowrap height="15"><?php echo $this->_tpl_vars['ship_section_name']; ?>
</td>
    </tr>
    <tr>
      <td nowrap height="16">倉庫（金額）</td>
      <td nowrap height="16"><?php echo $this->_tpl_vars['warehouse_name']; ?>
</td>
    </tr>
  </table>
</div>
</td>
  <td nowrap valign="bottom">
　　
</td>
  <td nowrap valign="bottom">
<B>表示選択</B><br>
<font size=-1>
<input type="submit" name="prev" value="←前表示">
<input type="submit" name="next" value="次表示→">           
 &nbsp;        
</font>
</td>
  <td nowrap>
　　　
</td>
  <td valign="bottom" nowrap>
商品コード：<input type="text" class="hankaku" name="search_from" value="<?php echo $this->_tpl_vars['search_from']; ?>
"  size="7" maxlength="5">  
<input type="submit" name="search" value="表示">
</td>

<?php if ($this->_tpl_vars['new_inventory_mode'] == false): ?>
<td valign="bottom" nowrap>
  <input type="submit" name="new_inventory" value="新規追加">
</td>
<?php endif; ?>

</tr>
</table>
    </center>
  </div>
<hr size="0">

  <div align="center">
  <table border="0" cellpadding="3" cellspacing="0" bgcolor="#808080">
  <tr><td>
  <table border="0" width="600">
  
   <?php if ($this->_tpl_vars['error'] != ""): ?>
    <tr>
        <td bgcolor="yellow" colspan="8">
          <?php echo $this->_tpl_vars['error']; ?>

        </td>
    </tr>
  <?php endif; ?> 
  
    <?php if ($this->_tpl_vars['commodity_count'] > 0 || $this->_tpl_vars['new_inventory_mode'] == true): ?>
    <tr>
      <td class="header_cell" width="80">商品コード</td>
      <td class="header_cell" nowrap width="280">商品名</td>
      <td class="header_cell" width="120" nowrap>
        <p align="center">計算数量</p>
      </td>
      <td class="header_cell" width="120">
        <p align="center">実在数量</p>
      </td>
      <td class="header_cell" width="120">
        <p align="center">計算単価</p>
      </td>      
      <td class="header_cell" width="120">
        <p align="center">計算在庫金額</p>
      </td>  
      <td class="header_cell" width="120">
        <p align="center">在庫金額</p>
      </td>      
      <td class="header_cell" width="120">
        <p align="center">金額手入力</p>
      </td> 
     </tr>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['new_inventory_mode'] == true): ?>

      <!-- start add new line -->
      <tr>
        <td nowrap class="data_cell" width="80" align="center">
          <nobr>
          <input type="text" name="commodity_code"  value="<?php echo $this->_tpl_vars['commodity_code']; ?>
" maxlength=7>
          <input type="submit" name="commodity_ref" value="H">
          </nobr>
        </td>
        <td nowrap class="data_cell" width="280"><?php echo $this->_tpl_vars['commodity_name']; ?>
</td>

        <td nowrap class="data_cell" width="100">
          &nbsp;
        </td>

        <td nowrap class="data_cell" width="120" align="center">
          <div align="center">
            <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="60%">
                <input type="text" class="amount" name="inventory_amount" value="<?php echo $this->_tpl_vars['inventory_amount']; ?>
" size="10" maxlength="6" onkeypress="javascript:digit_and_sign();">
              </td>
              <td width="40%" nowrap><?php echo ((is_array($_tmp=$this->_tpl_vars['unit_name'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)); ?>
</td>
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
              <input type="text" class="amount" name="inventory_price" value="<?php echo $this->_tpl_vars['inventory_price']; ?>
" size="12" maxlength="11" onkeypress="javascript:digit_and_sign();">
            </td>            
          </tr>
          </table>
          </div>
        </td>
      </tr>    
    
    <?php else: ?>
    
      <!--- line start for normal operation -->
      <?php $this->assign('n', 0); ?>
      <?php unset($this->_sections['index']);
$this->_sections['index']['name'] = 'index';
$this->_sections['index']['start'] = (int)0;
$this->_sections['index']['loop'] = is_array($_loop=$this->_tpl_vars['commodity_count']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['index']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['index']['show'] = true;
$this->_sections['index']['max'] = $this->_sections['index']['loop'];
if ($this->_sections['index']['start'] < 0)
    $this->_sections['index']['start'] = max($this->_sections['index']['step'] > 0 ? 0 : -1, $this->_sections['index']['loop'] + $this->_sections['index']['start']);
else
    $this->_sections['index']['start'] = min($this->_sections['index']['start'], $this->_sections['index']['step'] > 0 ? $this->_sections['index']['loop'] : $this->_sections['index']['loop']-1);
if ($this->_sections['index']['show']) {
    $this->_sections['index']['total'] = min(ceil(($this->_sections['index']['step'] > 0 ? $this->_sections['index']['loop'] - $this->_sections['index']['start'] : $this->_sections['index']['start']+1)/abs($this->_sections['index']['step'])), $this->_sections['index']['max']);
    if ($this->_sections['index']['total'] == 0)
        $this->_sections['index']['show'] = false;
} else
    $this->_sections['index']['total'] = 0;
if ($this->_sections['index']['show']):

            for ($this->_sections['index']['index'] = $this->_sections['index']['start'], $this->_sections['index']['iteration'] = 1;
                 $this->_sections['index']['iteration'] <= $this->_sections['index']['total'];
                 $this->_sections['index']['index'] += $this->_sections['index']['step'], $this->_sections['index']['iteration']++):
$this->_sections['index']['rownum'] = $this->_sections['index']['iteration'];
$this->_sections['index']['index_prev'] = $this->_sections['index']['index'] - $this->_sections['index']['step'];
$this->_sections['index']['index_next'] = $this->_sections['index']['index'] + $this->_sections['index']['step'];
$this->_sections['index']['first']      = ($this->_sections['index']['iteration'] == 1);
$this->_sections['index']['last']       = ($this->_sections['index']['iteration'] == $this->_sections['index']['total']);
?>

      <tr>
        <td nowrap class="data_cell" width="80" align="center"><?php echo $this->_tpl_vars['commodity_codes'][$this->_sections['index']['index']]; ?>
</td>
        <td nowrap class="data_cell" width="250"><?php echo $this->_tpl_vars['commodity_names'][$this->_sections['index']['index']]; ?>
</td>

        <td nowrap class="data_cell" width="100">
          <div align="center">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td align=right width="60%"><?php echo $this->_tpl_vars['amounts'][$this->_sections['index']['index']]; ?>
</td>
                <td width="40%"><?php echo ((is_array($_tmp=$this->_tpl_vars['unit_names'][$this->_sections['index']['index']])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)); ?>
</td>
              </tr>
            </table>
          </div>
        </td>

        <td nowrap class="data_cell" width="120" align="center">
          <div align="center">
            <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="60%">
                <input type="text" class="amount" name="inventory_amount_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo $this->_tpl_vars['inventory_amount'][$this->_sections['index']['index']]; ?>
" size="10" maxlength="6" onkeypress="javascript:digit_and_sign();">
              </td>
              <td width="40%" nowrap><?php echo ((is_array($_tmp=$this->_tpl_vars['unit_names'][$this->_sections['index']['index']])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)); ?>
</td>
            </tr>
            </table>
          </div>
        </td>
      
        <td nowrap class="data_cell" width="100">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td align=right width="70%"><?php echo $this->_tpl_vars['unit_price'][$this->_sections['index']['index']]; ?>
</td>
              <td width="30%">円</td>
            </tr>
          </table>
        </div>
        </td>
      
        <td nowrap class="data_cell" width="100">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td align=right width="70%"><?php echo $this->_tpl_vars['total_price'][$this->_sections['index']['index']]; ?>
</td>
              <td width="30%">円</td>
            </tr>
          </table>
        </div>
        </td>
      
        <td nowrap class="data_cell" width="100" align="center">
        <div align="center">
          <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="100%">
              <input type="text" class="amount" name="inventory_price_<?php echo $this->_tpl_vars['n']; ?>
" value="<?php echo $this->_tpl_vars['inventory_price'][$this->_sections['index']['index']]; ?>
" size="12" maxlength="11" onkeypress="javascript:digit_and_sign();">
            </td>            
          </tr>
          </table>
          </div>
        </td>
        
        <td nowrap class="data_cell" width="100" align="center">
        <?php if ($this->_tpl_vars['calc_flag'][$this->_sections['index']['index']] == '1'): ?>
          <input type="checkbox" name="calc_flag_<?php echo $this->_tpl_vars['n']; ?>
" checked>
        <?php else: ?>
          <input type="checkbox" name="calc_flag_<?php echo $this->_tpl_vars['n']; ?>
">        
        <?php endif; ?>
          
        </td>
      
      </tr>
      <?php $this->assign('n', $this->_tpl_vars['n']+1); ?>
      <?php endfor; endif; ?>
      <!-- line end -->
      <?php endif; ?>
    
  </table>
  </td>
    </tr>
 </table>

<hr size="0">
<br>
  <center>  
  <?php if ($this->_tpl_vars['new_inventory_mode'] == true): ?>
  <input type="submit" name="check_new_commodity" value="　確認　"> 
  <input type="submit" name="save_new_commodity" value="　保存　"> 
  <?php else: ?>
  <input type="submit" name="calc" value="　再計算　">
  <input type="submit" name="save" value="　保存　">
  <?php endif; ?>
  </center>
</form>
<hr size="1">
<FORM ACTION="OlutMainMenu.php">
  <center>
  <INPUT TYPE="SUBMIT" VALUE="　メニューへ戻る　">
  </center>
</FORM>

</td>
</table>
</body>