<?php /* Smarty version 2.6.9, created on 2005-10-06 20:44:27
         compiled from LoginForm.tpl */ ?>
<HTML>
<HEAD>
<TITLE>Login</TITLE>
</HEAD>
<script language="javascript" src="js/olut.js"></script>

<BODY BGCOLOR="#FFFFCC">
<CENTER>
<FORM METHOD="POST" name="login" target="main_menu">
<BR>
<h1><font face="Arial,Impact"> Olut Login </font></h1>

<TABLE BORDER="5" CELLPADDING="10">
<TR><TD align=center BGCOLOR="#000080">
<B><font color="#FFFFFF">ログイン</font></B>

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
<TR><TD>担当者コード<BR>
</TD>
<TD VALIGN=TOP>
<INPUT TYPE="TEXT" NAME="user_id" SIZE="12" MAXLENGTH="8" value="<?php echo $this->_tpl_vars['user_id']; ?>
" style="width:90px;"><BR>
</TD>

<TR><TD>
    <p align="right">パスワード<BR>
    </p>
</TD>
<CENTER>
<CENTER>
<TD VALIGN=TOP>
<INPUT TYPE="password" NAME="password" SIZE="8" MAXLENGTH="8" style="width:90px;"><BR>
</TD>
</TABLE>
<BR>
<INPUT TYPE="submit" VALUE="送　信" name="login" style="width:80px;" onclick="javacript:do_login(this.form);">　<INPUT TYPE="reset" VALUE="クリア" style="width:80px;"><BR>  
</FORM>
</TD>
</TABLE>

</CENTER>

<BR><BR>

</BODY>
</HTML>

