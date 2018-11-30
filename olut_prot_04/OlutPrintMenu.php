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
 *    ������˥塼 - OlutPrintMenu.php
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
<title>Ģɼ���Ͻ���</title>
</head>
<SCRIPT language="javascript" SRC="js/olut.js"></SCRIPT>
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
        <font size="4">������</font>Ģɼ���Ͻ���<font size="4">������</font><br>
</p>
<div align="center">
  <center>
  <table border="0" cellpadding="10" cellspacing="0">
    <tr>
      <td valign="top">
        <a href="ShipmentDetailReport.php" onclick="javascript:new_print_window('ShipmentDetailReport.php');">�������ٽ�</a><br>
        <br>
          <a href="ShipmentSlipNoReport.php" onclick="javascript:new_print_window('ShipmentSlipNoReport.php');">Źɼ��������ٽ�</a><br>
        <br>      
        <a href="AccountsPayableReport.php" onclick="javascript:new_print_window('AccountsPayableReport.php');">��ݶ�����<br>
        <br>
        <a href="PurchaseReportAccordingToVendor.php" onclick="javascript:new_print_window('PurchaseReportAccordingToVendor.php');">�ȼ��̻�������ɽ</a><br>
        <br>
        <a href="ReportOfArrivalOfGoods.php" onclick="javascript:new_print_window('ReportOfArrivalOfGoods.php');">��������</a><br>
        <br>
        <a href="MonthlyShipmentReportByCommodity.php" onclick="javascript:new_print_window('MonthlyShipmentReportByCommodity.php');">�����̷��̽и˰���ɽ</a><br>
        <br>
        <a href="MonthlyShipmentReportByStore.php" onclick="javascript:new_print_window('MonthlyShipmentReportByStore.php');">Ź���̷��̽и˰���ɽ</a><br>
        <br>        
        <a href="MonthlyArrivalReportByVendor.php" onclick="javascript:new_print_window('MonthlyArrivalReportByVendor.php');">�������̷������˰���ɽ</a><br>
        <br>         
      </td>      
      <td valign="top">
        <a href="ProfitAndLossAccordingToShipmentSection.php" onclick="javascript:new_print_window('ProfitAndLossAccordingToShipmentSection.php');">���вٺ��׹����ɽ</a><br>
        <br>
        <a href="ProfitAndLossAccordingToCommodity.php" onclick="javascript:new_print_window('ProfitAndLossAccordingToCommodity.php');">������и˺߸�ɽ</a><br>
        <br>
        <a href="DeliveryReportAccordingToShop.php" onclick="javascript:new_print_window('DeliveryReportAccordingToShop.php');">Ź�̽и˰���ɽ</a><br>
        <br>
        <a href="DeliveryReportAccordingToCommodity.php" onclick="javascript:new_print_window('DeliveryReportAccordingToCommodity.php');">�����̽и˰���ɽ</a><br>
        <br>
        <a href="InvoicePrint.php" onclick="javascript:new_print_window('InvoicePrint.php');">�����</a><br>
        <br>
        <a href="StatementOfDeliveryRePrint.php" onclick="javascript:new_print_window('StatementOfDeliveryRePrint.php');">Ǽ�ʽ�ư���</a><br>
        <br>
        <a href="InventoryReport.php" onclick="javascript:new_print_window('InventoryReport.php');">ê��Ģɼ����</a>      
      </td>  
    </tr>
  </table>
  </center>
</div>
<hr width="300">
<p align="center"><a href="OlutMainMenu.php">�ᥤ�󡦥�˥塼�����</a><br>
<br>
</p>

</body>

</html>
<?php
}
?>
