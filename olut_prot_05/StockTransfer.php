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
 *    入出庫移動 - StockTransfer.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

session_start();

include('sys_define.inc');
include(OLUT_DIR . 'CommodityReferenceClass.inc');
include(OLUT_DIR . 'CommodityReferenceSetup.inc');
include(OLUT_DIR . "StockTransferClass.php");

$obj =& new StockTransfer;
$obj->savePostedValues($_POST);

if(isset($_SESSION['is_commodity_ref']))
{
    // 商品マスター参照中。
    $commodityRef =& new CommodityReference;
    $commodityRef->performReference();


    // 商品の指定が確定。
    if(isset($commodityRef->commodity_code))
    {
        $line = $_SESSION['target_line'];
        $obj->commodity_code[$line] = $commodityRef->commodity_code;
        $obj->commodity_name[$line] = $commodityRef->commodity_name;
        $obj->unit_price[$line]     = $commodityRef->commodity_unit_price;
        $obj->unit_name[$line]      = $commodityRef->commodity_unit_name;

        $obj->renderScreen($_POST);
        $obj->saveToSession();
    }

    if(isset($_POST['cancel']))
    {
        $obj->renderScreen($_POST);
    }
}
else
if(isset($_POST['move']))
{
    $obj->move($_POST);
    $obj->clearAllSession();
    $obj->renderScreen($_POST);
}
else 
if( isset($_POST['check']) )
{
    // 検査します。
    $obj->checkEntry(&$_POST);
    $obj->renderScreen($_POST);
}
else
{
    // 商品の参照？
    $line = $obj->isCommodityRef($_REQUEST);

    if($line != -1)
    {
        //
        // 商品参照。
        //
        $commodityRef =& new CommodityReference;
        $_SESSION['target_line'] = $line;
        $_SESSION['is_commodity_ref'] = true;
        $commodityRef->displayForm(null);
        //
    }
    else
    {
        $obj->clearAllSession();
        $obj->parseFromTo();
        $obj->getWarehouseNames();
        $obj->saveToSession();
        $obj->renderScreen($_POST);

    }
}


?>