<?php /* Smarty version 2.6.9, created on 2005-10-01 19:13:45
         compiled from UserRegistrationForm.tpl */ ?>
<HTML>
<HEAD>
<meta http-equiv="Content-Type" Content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<TITLE>���ѼԿ�����Ͽ</TITLE>
<link rel="stylesheet" href="css/generic.css" type="text/css" />
</HEAD>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<BODY BGCOLOR="#FFFFCC">
<CENTER>
<FORM METHOD="POST">
<BR>

<TABLE BORDER="5" CELLPADDING="10">
<TR><TD align=center BGCOLOR="#000080">
<B><font color="#FFFFFF">���ѼԿ�����Ͽ</font></B>

</TD>

<TR><TD>
<CENTER>
<TABLE BORDER=0 CELLPADDING="5">
<?php if ($this->_tpl_vars['error'] != ""): ?>
    <tr>
        <td bgcolor="yellow" colspan="2">
          <?php echo $this->_tpl_vars['error']; ?>

        </td>
    </tr>
<?php endif; ?>

<TR>
  <TD>���桼����ID</TD>
  <TD VALIGN=TOP><INPUT TYPE="text" class="hankaku" NAME="new_userid" value="<?php echo $this->_tpl_vars['new_userid']; ?>
"SIZE="32" MAXLENGTH="8" ></TD>
</tr>

<TR>
  <TD>���ѥ����</TD>
  <TD VALIGN=TOP><INPUT TYPE="password" class="hankaku" NAME="new_password" SIZE="12" MAXLENGTH="8" ></TD>
</tr>

<tr>
  <TD>
    <p align="right">�ѥ���ɳ�ǧ</p>
  </TD>
  <TD VALIGN=TOP>
    <INPUT TYPE="password" class="hankaku" NAME="verify_password" SIZE="12" MAXLENGTH="8"><BR>
  </TD>
</tr>

<tr>
  <TD>
    <p align="right">����ɽ���Կ�</p>
  </TD>
  <TD VALIGN=TOP>
    <INPUT TYPE="text" NAME="screen_lines" class="hankaku" SIZE="5" MAXLENGTH="2" value="<?php echo $this->_tpl_vars['screen_lines']; ?>
" onkeypress="javascript:digitonly();"><BR>
  </TD>
</tr>

</TABLE>
<BR>
<INPUT TYPE="SUBMIT" VALUE="���������С�Ͽ" name="password_change" style="width:120px;">��<INPUT TYPE="reset" VALUE="���ꥢ" style="width:80px;"><BR>  
</FORM>
</TD>
</TABLE>
<p>
<a href="OlutMainMenu.php">�ᥤ�󡦥�˥塼�����</a>
</p>
</CENTER>

<BR><BR>

</BODY>
</HTML>

