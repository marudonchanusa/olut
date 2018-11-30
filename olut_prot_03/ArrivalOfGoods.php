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
 *    入荷登録 - ArrivalOfGoods.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

/*
 * 入荷登録
 *
 * このファイルが結果的にレンダリングする画面は以下になります。
 *
 * 1. 入荷登録画面
 * 2. 商品マスター参照画面
 * 3. 取引先マスター参照画面
 *
 * 上記の切り分けはセッション変数で行われます。
 *
 * $_SESSION['is_vendor_ref']      ==> 取引先参照中。（このフォームで再表示、つまり「あ行」とか｢前」「次」を選択）
 * $_SESSION['is_commodity_ref']   ==> 商品先参照中。
 *
 * 上記がセットされていない場合は「入荷登録」の画面。
 *
 */
session_start();

include('sys_define.inc');
include(OLUT_DIR . 'ArrivalOfGoodsClass.php');
include(OLUT_DIR . 'ArrivalOfGoodsSetup.php');
include(OLUT_DIR . 'VendorReferenceClass.inc');
include(OLUT_DIR . 'VendorReferenceSetup.inc');
include(OLUT_DIR . 'CommodityReferenceClass.inc');
include(OLUT_DIR . 'CommodityReferenceSetup.inc');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');
require_once(OLUT_DIR . 'Login.php');

$login =& new Login();
if($login->validateUser($_POST))
{
    // インスタンス生成。
    $arg =& new ArrivalOfGoods();
    $arg->savePostedValues($_POST);      // POSTデータをセッションに保存。
    //

    if(isset($_SESSION['is_vendor_ref']))
    {
        // 取引先マスター参照中。
        $vendorRef =& new VendorReference;
        $vendorRef->performReference();

        // 取引先が確定されてきた。
        if(isset($vendorRef->vendor_code))
        {
            $line = $_SESSION['target_line'];
            $arg->vendor_code[$line] = $vendorRef->vendor_code;
            $arg->vendor_name[$line] = $vendorRef->vendor_name;
            $arg->displayForm($_POST);
        }

        if(isset($_POST['cancel'])){
            $arg->displayForm($_POST);
        }
    }
    else
    if(isset($_SESSION['is_commodity_ref']))
    {
        // 商品マスター参照中。
        $commodityRef =& new CommodityReference;
        $commodityRef->performReference();


        // 商品の指定が確定。
        if(isset($commodityRef->commodity_code))
        {
            $line = $_SESSION['target_line'];
            $arg->commodity_code[$line] = $commodityRef->commodity_code;
            $arg->commodity_name[$line] = $commodityRef->commodity_name;
            $arg->unit_price[$line]     = $commodityRef->commodity_unit_price;
            $arg->unit_name[$line]      = $commodityRef->commodity_unit_name;

            $arg->displayForm($_POST);
        }

        if(isset($_POST['cancel'])){
            $arg->displayForm($_POST);
        }
    }
    else
    if( isset($_POST['check']) )
    {
        // 検査します。
        $arg->checkEntry(&$_POST);
        $arg->displayForm($_POST);
    }
    else
    if( isset($_POST['save']) )
    {
        // 保存。
        if($arg->checkEntry(&$_POST) == true)
        {
            if($arg->saveEntry($_POST)==true)
            {
                $arg->clearAllSession();
                $_POST = array();
            }
        }
        $arg->displayForm($_POST);
    }
    else
    {
        // 商品の参照？
        $line = $arg->isCommodityRef($_REQUEST);

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
            $line = $arg->isVendorRef($_REQUEST);

            if ($line != -1){
                //
                // 取引先参照。
                //
                $vendorRef =& new VendorReference;
                $_SESSION['target_line'] = $line;
                $_SESSION['is_vendor_ref'] = true;
                $vendorRef->displayForm(null);
                //
            }
            else
            {
                $_SESSION['modify_mode'] = false;
                $arg->clearAllSession();
                // 最初にGETされた場合。
                if(!isset($_POST['slip_no']))
                {
                    $arg->getNewSlipNo();
                }
                $arg->displayForm($_POST);
            }
        }
    }
}
?>
