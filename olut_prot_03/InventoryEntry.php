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
*    棚卸入力メイン - InventoryEntry.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*
*/

session_start();

include('sys_define.inc');
include(OLUT_DIR . 'InventoryEntrySetup.php');
include(OLUT_DIR . 'InventoryEntryClass.php');
require_once(OLUT_DIR . 'CommodityReferenceClass.inc');
require_once(OLUT_DIR . 'CommodityReferenceSetup.inc');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{

    // インスタンス生成。
    $obj =& new InventoryEntry;

    if(isset($_SESSION['is_commodity_ref']))
    {
        // 商品マスター参照中。
        $commodityRef =& new CommodityReference;
        $commodityRef->performReference();


        // 商品の指定が確定。
        if(isset($commodityRef->commodity_code))
        {
            $obj->commodity_code = $commodityRef->commodity_code;
            $obj->commodity_name = $commodityRef->commodity_name;
            $obj->unit_price     = $commodityRef->commodity_unit_price;
            $obj->unit_name      = $commodityRef->commodity_unit_name;

            $obj->saveToSession();
            $obj->newInventoryMode();
            $obj->renderEntryForm($_POST);
        }

        if(isset($_POST['cancel'])){
            $obj->newInventoryMode();
            $obj->renderEntryForm($_POST);
        }
    }
    else
    if(isset($_POST['start']))
    {
        //
        // 棚卸対象が選択された。
        //
        $obj->parseSelectionForm($_POST);
        $obj->referMasters();
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['save']))
    {
        // 保存ボタン。
        $obj->restoreFromSession();
        $obj->parseInventory($_POST);
        $obj->registerInventory();
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['prev']))
    {
        $obj->restoreFromSession();
        $obj->previous();
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['next']))
    {
        $obj->restoreFromSession();
        $obj->next();
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['search']))
    {
        $obj->restoreFromSession();
        $obj->setSearchArg($_POST);
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['new_inventory']))
    {
        // 新規行モード
        $obj->clear();
        $obj->newInventoryMode();
        $obj->RenderEntryForm($_POST);
    }
    else
    if(isset($_POST['check_new_commodity']))
    {
        // 新規で確認ボタンが押された。
        $obj->parseNewInventory($_POST);
        $obj->saveToSession();
        $obj->newInventoryMode();
        $obj->checkNewInventory($_POST);
        $obj->RenderEntryForm($_POST);
    }
    else
    if(isset($_POST['save_new_commodity']))
    {
        $obj->parseNewInventory($_POST);
        //
        if($obj->checkNewInventory($_POST)==true)
        {
            if($obj->saveNewInventory($_POST) == false )
            {
                $obj->newInventoryMode();
                $obj->renderEntryForm($_POST);
            }
            else
            {
                // 正常終了
                $obj->clear();
                $obj->renderEntryForm($_POST);
            }
        }
        else
        {
            $obj->renderEntryForm($_POST);
        }
    }
    else
    if(isset($_POST['commodity_ref']))
    {
        // 商品参照モード。
        //
        $commodityRef =& new CommodityReference;
        $_SESSION['is_commodity_ref'] = true;
        $commodityRef->displayForm(null);
    }
    else
    if(isset($_POST['calc']))
    {
        $obj->restoreFromSession();
        $obj->parseInventory($_POST);
        // 再計算。
        $obj->reCalc($_POST);
        $obj->renderEntryForm($_POST);
    }
    else
    {
        // 初回のGET時は棚卸対象を選択させる。
        $obj->renderSelectionForm($_POST);
    }
}


?>
