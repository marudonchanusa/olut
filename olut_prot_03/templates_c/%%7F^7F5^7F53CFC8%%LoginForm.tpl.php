<?php /* Smarty version 2.6.9, created on 2005-09-16 09:36:47
         compiled from LoginForm.tpl */ ?>
<HTML>
<HEAD>
<TITLE>Login</TITLE>
</HEAD>
<BODY BGCOLOR="#FFFFCC">
<CENTER>
<FORM METHOD="POST">
<BR>
<h1><font face="Arial,Impact"> Olut Login </font></h1>

<TABLE BORDER="5" CELLPADDING="10">
<TR><TD align=center BGCOLOR="#000080">
<B><font color="#FFFFFF">��������</font></B>

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
<TR><TD>ô���ԥ�����<BR>
</TD>
<TD VALIGN=TOP>
<INPUT TYPE="TEXT" NAME="user_id" SIZE="12" MAXLENGTH="8" value="<?php echo $this->_tpl_vars['user_id']; ?>
" style="width:90px;"><BR>
</TD>

<TR><TD>
    <p align="right">�ѥ����<BR>
    </p>
</TD>
<CENTER>
<CENTER>
<TD VALIGN=TOP>
<INPUT TYPE="password" NAME="password" SIZE="8" MAXLENGTH="8" style="width:90px;"><BR>
</TD>
</TABLE>
<BR>
<INPUT TYPE="SUBMIT" VALUE="������" name="login" style="width:80px;">��<INPUT TYPE="reset" VALUE="���ꥢ" style="width:80px;"><BR>  
</FORM>
</TD>
</TABLE>

</CENTER>

<BR><BR>

</BODY>
</HTML>
