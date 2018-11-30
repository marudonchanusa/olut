<?php /* Smarty version 2.6.9, created on 2005-09-28 19:35:29
         compiled from PasswordChangeForm.tpl */ ?>
<HTML>
<HEAD>
<TITLE>プロファイル変更</TITLE>
</HEAD>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>

<BODY BGCOLOR="#FFFFCC">
<CENTER>
<FORM METHOD="POST">
<BR>

<TABLE BORDER="5" CELLPADDING="10">
<TR><TD align=center BGCOLOR="#000080">
<B><font color="#FFFFFF">利用者プロファイル変更</font></B>

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
  <TD>新パスワード</TD>
  <TD VALIGN=TOP><INPUT TYPE="password" NAME="new_password" SIZE="12" MAXLENGTH="8" ></TD>
</tr>

<tr>
  <TD>
    <p align="right">パスワード確認</p>
  </TD>
  <TD VALIGN=TOP>
    <INPUT TYPE="password" NAME="verify_password" SIZE="12" MAXLENGTH="8"><BR>
  </TD>
</tr>

<tr>
  <TD>
    <p align="right">画面表示行数</p>
  </TD>
  <TD VALIGN=TOP>
    <INPUT TYPE="text" NAME="screen_lines" SIZE="5" MAXLENGTH="2" value="<?php echo $this->_tpl_vars['screen_lines']; ?>
" onkeypress="javascript:digitonly();"><BR>
  </TD>
</tr>

</TABLE>
<BR>
<INPUT TYPE="SUBMIT" VALUE="変更実行" name="password_change" style="width:80px;">　<INPUT TYPE="reset" VALUE="クリア" style="width:80px;"><BR>  
</FORM>
</TD>
</TABLE>
<p>
<a href="OlutMainMenu.php">メイン・メニューへ戻る</a>
</p>
</CENTER>

<BR><BR>

</BODY>
</HTML>

