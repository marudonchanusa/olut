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
 *    マスターメニュー - OlutMasterMenu.php 
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
<title>マスタ管理画面</title>
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
        <font size="4">◆◆　</font>マスタ管理<font size="4">　◆◆<br>
        </font>
</p>
<div align="center">
  <center>
  <table border="0" cellpadding="10" cellspacing="0">
    <tr>
      <td width="50%" valign="top"><a href="CommodityMaster.php">商品マスタ</a><br>
        <br>
        <a href="StoreMaster.php">店舗マスタ</a><br>
        <br>
      </td>
      <td width="50%" valign="top"><a href="VendorMaster.php">仕入取引先マスタ</a><br>
        <br>
      </td>

   </tr>
   
   <tr>
      <td width="50%" valign="top"><a href="CommodityCheckList.php">商品チェックリスト</a><br>
        <br>
        <a href="StoreCheckList.php">店舗チェックリスト</a><br>
        <br>
      </td>
      <td width="50%" valign="top"><a href="VendorCheckList.php">仕入取引先チェックリスト</a><br>
        <br>
      </td>

   </tr>
   
  </table>
  </center>
</div>
<p><br>
</p>
<hr width="300">
<p align="center"><a href="OlutMainMenu.php">メイン・メニューへ戻る</a><br>
<br>
</p>

</body>

</html>
<?php
}
?>

