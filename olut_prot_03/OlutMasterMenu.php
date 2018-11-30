<?php
/*
 * Olut inventory management  system
 * Copyright (C) 2005 NTC all rights reserved.
 *
 *   http://www.newtokyo.co.jp/olut/
 *
 * Development and delpoyments by TechKnowledge inc.
 *   http://www.techknowldge.co.jp/olut.html
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * ���Υץ����ϥե꡼���եȥ������Ǥ������ʤ��Ϥ���򡢥ե꡼���եȥ���
 * �����Ĥˤ�ä�ȯ�Ԥ��줿 GNU ���̸������ѵ��������(�С������2������
 * ˾�ˤ�äƤϤ���ʹߤΥС������Τ����ɤ줫)��������β��Ǻ�����
 * �ޤ��ϲ��Ѥ��뤳�Ȥ��Ǥ��ޤ���
 *
 * ���Υץ�����ͭ�ѤǤ��뤳�Ȥ��ä����ۤ���ޤ�����*������̵�ݾ�* 
 * �Ǥ������Ȳ�ǽ�����ݾڤ��������Ū�ؤ�Ŭ�����ϡ������˼����줿��Τ��
 * ������¸�ߤ��ޤ��󡣾ܤ�����GNU ���̸������ѵ���������������������
 *
 * ���ʤ��Ϥ��Υץ����ȶ��ˡ�GNU ���̸������ѵ���������ʣ��ʪ�����
 * ������ä��Ϥ��Ǥ����⤷������äƤ��ʤ���С��ե꡼���եȥ��������Ĥ�
 * �����ᤷ�Ƥ�������(����� the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)��
 *
 *   Program name:
 *    �ޥ�������˥塼 - OlutMasterMenu.php 
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

session_start();
require_once('sys_define.inc');
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{
    // login check....
    // session clear...

    $dt = date('Y/m/d');
    $tm = date('G:i:s');
?>
<html>

<head>
<meta http-equiv="Content-Language" content="ja">
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<title>�ޥ�����������</title>
</head>

<body bgcolor="#FFFF99" vlink="#0000FF">

<div align="center">
  <center>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td width="95%"></td>
      <td width="5%" nowrap>Date&nbsp;<?php print $dt; ?><br>        
        Time&nbsp; <?php print $tm; ?></td>       
    </tr>
  </table>
  </center>
</div>
<p align="center">
        <font size="4">������</font>�ޥ�������<font size="4">������<br>
        </font>
</p>
<div align="center">
  <center>
  <table border="0" cellpadding="10" cellspacing="0">
    <tr>
      <td width="50%" valign="top"><a href="CommodityMaster.php">���ʥޥ���</a><br>
        <br>
        <a href="StoreMaster.php">Ź�ޥޥ���</a><br>
        <br>
      </td>
      <td width="50%" valign="top"><a href="VendorMaster.php">���������ޥ���</a><br>
        <br>
      </td>

   </tr>
   
   <tr>
      <td width="50%" valign="top"><a href="CommodityCheckList.php">���ʥ����å��ꥹ��</a><br>
        <br>
        <a href="StoreCheckList.php">Ź�ޥ����å��ꥹ��</a><br>
        <br>
      </td>
      <td width="50%" valign="top"><a href="VendorCheckList.php">�������������å��ꥹ��</a><br>
        <br>
      </td>

   </tr>
   
  </table>
  </center>
</div>
<p><br>
</p>
<hr width="300">
<p align="center"><a href="OlutMainMenu.php">�ᥤ�󡦥�˥塼�����</a><br>
<br>
</p>

</body>

</html>
<?php
}
?>

