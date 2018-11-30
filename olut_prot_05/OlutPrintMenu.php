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
 * このプログラムはフリーソフトウェアです。あなたはこれを、フリーソフトウェ
 * ア財団によって発行された GNU 一般公衆利用許諾契約書(バージョン2か、希
 * 望によってはそれ以降のバージョンのうちどれか)の定める条件の下で再頒布
 * または改変することができます。
 *
 * このプログラムは有用であることを願って頒布されますが、*全くの無保証* 
 * です。商業可能性の保証や特定の目的への適合性は、言外に示されたものも含
 * め全く存在しません。詳しくはGNU 一般公衆利用許諾契約書をご覧ください。
 *
 * あなたはこのプログラムと共に、GNU 一般公衆利用許諾契約書の複製物を一部
 * 受け取ったはずです。もし受け取っていなければ、フリーソフトウェア財団ま
 * で請求してください(宛先は the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)。
 *
 *   Program name:
 *    印刷メニュー - OlutPrintMenu.php
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
<title>帳票出力処理</title>
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
        <font size="4">◆◆　</font>帳票出力処理<font size="4">　◆◆</font><br>
</p>
<div align="center">
  <center>
  <table border="0" cellpadding="10" cellspacing="0">
    <tr>
      <td valign="top">
        <a href="ShipmentDetailReport.php" onclick="javascript:new_print_window('ShipmentDetailReport.php');">荷渡明細書</a><br>
        <br>
          <a href="ShipmentSlipNoReport.php" onclick="javascript:new_print_window('ShipmentSlipNoReport.php');">店票毎荷渡明細書</a><br>
        <br>      
        <a href="AccountsPayableReport.php" onclick="javascript:new_print_window('AccountsPayableReport.php');">買掛金報告書<br>
        <br>
        <a href="PurchaseReportAccordingToVendor.php" onclick="javascript:new_print_window('PurchaseReportAccordingToVendor.php');">業者別仕入一覧表</a><br>
        <br>
        <a href="ReportOfArrivalOfGoods.php" onclick="javascript:new_print_window('ReportOfArrivalOfGoods.php');">入荷報告書</a><br>
        <br>
        <a href="MonthlyShipmentReportByCommodity.php" onclick="javascript:new_print_window('MonthlyShipmentReportByCommodity.php');">商品別月別出庫一覧表</a><br>
        <br>
        <a href="MonthlyShipmentReportByStore.php" onclick="javascript:new_print_window('MonthlyShipmentReportByStore.php');">店舗別月別出庫一覧表</a><br>
        <br>        
        <a href="MonthlyArrivalReportByVendor.php" onclick="javascript:new_print_window('MonthlyArrivalReportByVendor.php');">仕入先別月別入庫一覧表</a><br>
        <br>         
      </td>      
      <td valign="top">
        <a href="ProfitAndLossAccordingToShipmentSection.php" onclick="javascript:new_print_window('ProfitAndLossAccordingToShipmentSection.php');">入出荷差益高比較表</a><br>
        <br>
        <a href="ProfitAndLossAccordingToCommodity.php" onclick="javascript:new_print_window('ProfitAndLossAccordingToCommodity.php');">月間入出庫在庫表</a><br>
        <br>
        <a href="DeliveryReportAccordingToShop.php" onclick="javascript:new_print_window('DeliveryReportAccordingToShop.php');">店別出庫一覧表</a><br>
        <br>
        <a href="DeliveryReportAccordingToCommodity.php" onclick="javascript:new_print_window('DeliveryReportAccordingToCommodity.php');">商品別出庫一覧表</a><br>
        <br>
        <a href="InvoicePrint.php" onclick="javascript:new_print_window('InvoicePrint.php');">請求書</a><br>
        <br>
        <a href="StatementOfDeliveryRePrint.php" onclick="javascript:new_print_window('StatementOfDeliveryRePrint.php');">納品書再印刷</a><br>
        <br>
        <a href="InventoryReport.php" onclick="javascript:new_print_window('InventoryReport.php');">棚卸帳票印刷</a>      
      </td>  
    </tr>
  </table>
  </center>
</div>
<hr width="300">
<p align="center"><a href="OlutMainMenu.php">メイン・メニューへ戻る</a><br>
<br>
</p>

</body>

</html>
<?php
}
?>
