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
 *    �ᥤ���˥塼 - OlutMainMenu.php 
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

session_start();
require_once('sys_define.inc');
require_once(OLUT_DIR . 'Login.php');

$dt = date('Y/m/d');
$tm = date('G:i:s');

$login =& new Login();
if($login->validateUser($_POST)==true)
{
?>

<html>

<head>
<meta http-equiv="Content-Language" content="ja">
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<title>��̳�������</title>
</head>

<body bgcolor="#99CCFF" vlink="#0000FF">

<div align="center">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td width="95%"></td>
      <td width="5%" nowrap>Date&nbsp; <?php print $dt ?><br>    
        Time&nbsp; <?php print $tm ?></td>    
    </tr>
  </table>
</div>
<br><br>

<div align="center">
  <table border="0" cellpadding="0" cellspacing="0" width="500">
    <tr>
      <td width="20" height="20"><img border="0" src="image/c_lh.gif" width="20" height="20"></td>
      <td bgcolor="#FFFFFF" colspan="2">��</td>
      <td width="20" height="20"><img border="0" src="image/c_rh.gif" width="20" height="20"></td>
    </tr>
    <tr>
      <td width="20" bgcolor="#FFFFFF"></td>
      <td bgcolor="#FFFFFF" colspan="2" align="center">
        <font size="4">��������̳���򡡢���</font>
        <hr>
      </td>
      <td width="20" bgcolor="#FFFFFF">
        <p align="center"><br>
      </td>
    </tr>
    <tr>
      <td width="20" bgcolor="#FFFFFF">��</td>
      <td bgcolor="#FFFFFF" width="280" valign="top" align="right">
          <table border="0" cellpadding="20" cellspacing="0">
            <tr>
              <td><br>
                <a href="OlutShipmentMenu.php">�вٽ���</a><br>
                <br>
                <a href="OlutArrivalOfGoodsMenu.php">���ٽ���</a><br>
                <br>
                <a href="OlutStockTransferMenu.php">���ٰ�ư����</a><br>
                <br>                
                <a href="OlutPrintMenu.php">Ģɼ����</a><br>
                <br>                
                <a href="LogOut.php">����������</a><br>
                <br>                
                <a href="PasswordChange.php">�ѥ�����ѹ�</a>
                
                
              </td>
            </tr>
          </table>
        <br>
      </td>
      <td bgcolor="#FFFFFF" width="280" valign="top" align="left">
          <table border="0" cellpadding="20" cellspacing="0">
            <tr>
              <td>
              <br><a href="ArrivalShipmentCheck.php">���и˼���ɽ��</a><br>   
              <br><a href="StockCheck.php">�߸˳�ǧɽ��</a><br>              
              <br><a href="OlutMasterMenu.php">�ޥ�������</a><br>
              <br><a href="OlutDataMenu.php">�ǡ�����̳</a><br>
              <br><a href="InventoryEntry.php">ê����̳</a></td>
            </tr>
          </table>
          ��</td>
      <td width="20" bgcolor="#FFFFFF">
        ��
      </td>
    </tr>
    <tr>
      <td width="20" height="20" bgcolor="#FFFFFF">��</td>
      <td height="20" bgcolor="#FFFFFF" colspan="2">
        <hr>
        <p align="center"><A HREF="javascript:window.close()">��λ</a></p>
      </td>
      <td height="20" bgcolor="#FFFFFF">��</td>
    </tr>
    <tr>
      <td width="20" height="20"><img border="0" src="image/c_ll.gif" width="20" height="20"></td>
      <td height="20" bgcolor="#FFFFFF"></td>
      <td height="20" bgcolor="#FFFFFF">��</td>
      <td width="20" height="20"><img border="0" src="image/c_rl.gif" width="20" height="20"></td>
    </tr>
  </table>
</div>
<br>
<br>
<br>

</body>

</html>

<?php
}
?>
