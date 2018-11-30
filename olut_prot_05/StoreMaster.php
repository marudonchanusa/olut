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
 *    店舗マスター登録メイン - StoreMaster.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');
include(OLUT_DIR . 'StoreMasterClass.php');
include(OLUT_DIR . 'StoreMasterSetup.php');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{
    // create Vendor
    $store =& new Store;

    if (isset($_REQUEST['add'])){
        //
        // 新規登録ボタンが押された場合。
        //
        //  1. フォームをクリアにする。
        //  2. 主キー(コード）を入力可能とする。
        //

        $store->displayForm();

    } else if(isset($_REQUEST['save'])){

        //
        // 保存ボタンが押された場合。
        //

        $store->mungeFormData($_POST);
        if($store->isValidForm($_POST))
        {
            if($store->saveEntry($_POST))
            {
                $store->clear();
                $store->displayForm();
            }
            else
            {
                $store->displayForm($_POST);
            }
        } else {
            //
            $store->displayForm($_POST);
        }

    } else if(isset($_REQUEST['reload'])){

        //
        // リロードボタンが押された。
        //
        $store->displayForm($store->findEntryExact($_POST));

    } else if(isset($_REQUEST['search'])){

        //
        // フォーム右上にコード入力された場合に検索してフォームにデータ表示する。
        //

        $store->displayForm($store->findEntry($_POST));

    } else if(isset($_REQUEST['next'])){

        //
        // フォーム上部の「次表示」ボタン。
        //
        $store->displayForm($store->getNextEntry($_POST));

    } else if(isset($_REQUEST['prev'])){

        //
        // フォーム上部の「前表示」ボタン
        //

        $store->displayForm($store->getPrevEntry($_POST));

        //
    } else if(isset($_REQUEST['delete'])){

        //
        // 削除ボタン
        //
        $store->deleteEntry($_POST);
        $store->displayForm();

    }else{

        //
        // フォーム[GET]時にブランクフォームを表示
        //
        //
        $store->displayForm();
    }
}


?>
