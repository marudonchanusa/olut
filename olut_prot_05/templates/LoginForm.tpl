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
<B><font color="#FFFFFF">��������</font></B>

</TD>

<TR><TD>
<CENTER>
<TABLE BORDER=0 CELLPADDING="5">
{if $error ne ""}
    <tr>
        <td bgcolor="yellow" colspan="2">
          {$error}
        </td>
    </tr>
{/if}
<TR><TD>ô���ԥ�����<BR>
</TD>
<TD VALIGN=TOP>
<INPUT TYPE="TEXT" NAME="user_id" SIZE="12" MAXLENGTH="8" value="{$user_id}" style="width:90px;"><BR>
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
<INPUT TYPE="submit" VALUE="������" name="login" style="width:80px;" onclick="javacript:do_login(this.form);">��<INPUT TYPE="reset" VALUE="���ꥢ" style="width:80px;"><BR>  
</FORM>
</TD>
</TABLE>

</CENTER>

<BR><BR>

</BODY>
</HTML>

