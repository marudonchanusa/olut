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
 *    納品書再印刷 - StatementOfDeliveryRePrintClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
require_once('OlutAppLib.php');
require_once('StatementOfDeliveryClass.php');    // 

// smarty configuration
class StatementOfDeliveryRePrint_Smarty extends Smarty
{
    function StatementOfDeliveryRePrint_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class StatementOfDeliveryRePrint_SQL extends SQL
{
    function StatementOfDeliveryRePrint_SQL()
    {
        //
        // dbtype://user:pass@host/dbname
        //
        $dsn = OLUT_DSN;

        if ($this->connect($dsn) == false)
        {
            $this->error = "データベース接続エラー(" + $dsn + ")";
        }
    }
}

class StatementOfDeliveryRePrint extends OlutApp
{
    var $tpl;
    var $sql;
    var $error;

    // ctor
    function StatementOfDeliveryRePrint()
    {
        $this->tpl =& new StatementOfDeliveryRePrint_Smarty();
        $this->sql =& new StatementOfDeliveryRePrint_SQL();
    }

    function renderScreen($formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];
        
        if(strlen($code_from))
        {
            $this->tpl->assign('code_from',$code_from);
        }

        if(strlen($code_to))
        {
            $this->tpl->assign('code_to',$code_to);
        }
        
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('StatementOfDeliveryRePrintForm.tpl');
    }

    function printOut($formvars)
    {
        //
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];

        //
        // 出荷のコードチェック。 TO は無くても良い。
        //

        if(strlen($code_from) == 0)
        {
            $this->error = "出荷コードを入力してください";
            return false;
        }
        
        if($code_from > $code_to)
        {
            $this->error = "コードの範囲を正しく入力してください";
            return false;
        }
        //
        // 出荷のコードを得る。
        //

        $_query  = "select distinct slip_no from t_main ";
        if( strlen($code_to) > 0)
        {
            // the range is from - to type.
            $_query .= " where slip_no >= '$code_from'";
            $_query .= " and slip_no <= '$code_to'";
        }
        else
        {
            $_query .= " where slip_no = '$code_from'";
        }
        $_query .= " and (act_flag='5' or act_flag='6' or act_flag='7')";
        $_query .= " order by slip_no";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            // print $_query;
            $this->error = "問い合わせエラー";
            return false;
        }

        $i = 0;
        foreach($this->sql->record as $rec)
        {
            $codes[$i++] = $rec[0];
        }

        //
        // 印刷クラスを生成。
        //
        $obj =& new StatementOfDelivery();
        return $obj->printOut($codes);
    }
}


?>