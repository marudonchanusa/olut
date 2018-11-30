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
 *    納品書印刷 - StatementOfDelivery.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

/*
 *
 *   このエントリはメニュー「納品書印刷」から呼ばれます。
 *   (納品書再印刷ではないので注意）
 *
 *  ポイント：　
 *      1. 出荷登録で登録した伝票番号をセッションに保存されている。
 *      2. 出荷登録の終了時に登録した伝票すべてを１つのPDFファイルとして印刷する。
 *      3. 一つのPDFとして印刷するということはパラメータとして渡した場合、数が
 *         あまりに多いとURL長の制限ですべてを印字したりできないことが予想される。
 *      4. なので、印刷対象となる、伝票番号はセッション渡しにする。
 *      5. セッション名は固定で'STATEMENT_OF_DELIVERY' とし、文字列の配列（中身はもちろん伝票番号）とする。
 *
 *
 *   さらに重要な設定について：
 *
 *   session_start(); するとヘダーにcache: none; などが追加されるのでPDFファイルが
 *   破壊されているとエラーが出ます。回避するにはphp.iniの以下に設定をします。
 *   (default is nocache)
 *   session.cache_limiter = private
 *   上記の設定だとページがキャッシュされアプリケーションの動作がわけわからない
 *   状態となりますので、このページはset_cache_limiter('none') とすることにしました。
 *
 *   see this url:
 *    http://php.s3.to/man/function.session-cache-limiter.html
 *
 */

session_cache_limiter('none');
session_start();

include('sys_define.inc');
include(OLUT_DIR . "StatementOfDeliveryClass.php");
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{
    $obj =& new StatementOfDelivery;

    $codes = $_SESSION['STATEMENT_OF_DELIVERY'];
    if($codes != null)
    {
        $obj->printOut($codes);

        // 現在のユーザーIDは保存
        $uid = $_SESSION['user_id'];
        $screen_lines = $_SESSION['screen_lines'];
        session_unset();
        // 再設定。
        $_SESSION['user_id'] = $uid;
        $_SESSION['screen_lines'] = $screen_lines;
        //
    }
    else
    {
        $obj->error = "印刷すべき出庫データはありません";
        $obj->renderErrorScreen();
    }
}
?>