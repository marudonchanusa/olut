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
 *    在庫確認画面表示 - StockCheckClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('OlutAppLib.php');


// database configuration
class StockCheck_SQL extends SQL
{
    function StockCheck_SQL()
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

// smarty configuration
class StockCheck_Smarty extends Smarty
{
    function StockCheck_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

/*
*
*/

class StockCheck extends OlutApp
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;

    var $commodity_code;
    var $commodity_name;
    var $target_date;
    var $stocks;
    var $arrivals;
    var $shipments;
    var $stocks_total;
    var $arrivals_total;
    var $shipments_total;

    function StockCheck()
    {
        $this->sql =& new StockCheck_SQL();
        $this->tpl =& new StockCHeck_Smarty();
        $this->target_date = date('Y/m/d');

        $this->restoreFromSession();
    }

    function saveToSession($formvars)
    {
        //
        if(isset($formvars['year']))
        {
            $this->target_date = $formvars['year'] . '/' . $formvars['month'] . '/' .$formvars['date'];
            $_SESSION['TARGET_DATE']    = $this->target_date;
        }

        if(strlen($formvars['commodity_code'])>0)
        {
            $_SESSION['COMMODITY_CODE'] = $this->commodity_code = $formvars['commodity_code'];
        }
        else
        {
            $_SESSION['COMMODITY_CODE'] = $this->commodity_code;
        }

        $_SESSION['COMMODITY_NAME'] = $this->commodity_name;
        $_SESSION['WAREHOUSE_CODES'] = $this->warehouse_codes;
        $_SESSION['WAREHOUSE_NAMES'] = $this->warehouse_names;
        $_SESSION['ARRIVALS']        = $this->arrivals;
        $_SESSION['SHIPMENTS']       = $this->shipments;
        $_SESSION['STOCKS']          = $this->stocks;
        $_SESSION['ARRIVALS_TOTAL']        = $this->arrivals_total;
        $_SESSION['SHIPMENTS_TOTAL']       = $this->shipments_total;
        $_SESSION['STOCKS_TOTAL']          = $this->stocks_total;
    }

    function restoreFromSession()
    {
        if($_SESSION['TARGET_DATE'] != null)
        {
            $this->target_date    = $_SESSION['TARGET_DATE'];
        }
        $this->commodity_code   = $_SESSION['COMMODITY_CODE'];
        $this->commodity_name   = $_SESSION['COMMODITY_NAME'];
        $this->warehouse_codes  = $_SESSION['WAREHOUSE_CODES'];
        $this->warehouse_names  = $_SESSION['WAREHOUSE_NAMES'];
        $this->arrivals         = $_SESSION['ARRIVALS'];
        $this->shipments        = $_SESSION['SHIPMENTS'];
        $this->stocks           = $_SESSION['STOCKS'];
        $this->arrivals_total         = $_SESSION['ARRIVALS_TOTAL'];
        $this->shipments_total        = $_SESSION['SHIPMENTS_TOTAL'];
        $this->stocks_total           = $_SESSION['STOCKS_TOTAL'];
    }

    function renderScreen($formvars)
    {
        // assign the form vars
        $this->tpl->assign('commodity_code',$this->commodity_code);
        $this->tpl->assign('commodity_name',$this->commodity_name);
        $this->tpl->assign('year',  substr($this->target_date,0,4));
        $this->tpl->assign('month', substr($this->target_date,5,2));
        $this->tpl->assign('date',  substr($this->target_date,8,2));
        $this->tpl->assign('warehouse_codes', $this->warehouse_codes);
        $this->tpl->assign('warehouse_names', $this->warehouse_names);
        $this->tpl->assign('arrivals',  $this->arrivals);
        $this->tpl->assign('shipments', $this->shipments);
        $this->tpl->assign('stocks',    $this->stocks);
        $this->tpl->assign('arrivals_total',  $this->arrivals_total);
        $this->tpl->assign('shipments_total', $this->shipments_total);
        $this->tpl->assign('stocks_total',    $this->stocks_total);

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('StockCheckForm.tpl');
    }

    //

    function getStockData($formvars)
    {
        //
        if(strlen($formvars['year'])==0 || strlen($formvars['month'])==0 || strlen($formvars['date'])==0)
        {
            $this->error = "日付け入力が正しくありません";
            return;
        }

        if(checkdate($formvars['month'],$formvars['date'],$formvars['year'])==false)
        {
            $this->error = "日付け入力が正しくありません";
            return;
        }

        if(strlen($formvars['commodity_code']) == 0)
        {
            $this->error = "商品コードを入力してください";
            return;
        }
        // 商品コード直接入力時のため、商品名を得る。
        $this->commodity_name = $this->getCommodityName($formvars['commodity_code']);
        $_SESSION['COMMODITY_NAME'] = $this->commodity_name;

        $target_date = $formvars['year'] . '/' . $formvars['month'] . '/' .$formvars['date'];

        //
        // 月初からの問い合わせで在庫数量を得る。
        // 2005/01/01
        // 0123456789
        $start_of_month = substr($this->target_date,0,4) . '/' . substr($this->target_date,5,2) . '/01';

        $_query  = "select t.warehouse_code, w.name, sum(t.amount) from t_main t, m_warehouse w";
        $_query .= " where t.act_date>='$start_of_month'";
        $_query .= "  and  t.act_date<='$this->target_date'";
        $_query .= "  and  t.com_code='$this->commodity_code'";
        $_query .= "  and  w.code=t.warehouse_code";
        $_query .= "  group by t.warehouse_code, w.name";
        $_query .= "  order by t.warehouse_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            $this->error = $this->sql->error;
            return false;
        }

        $this->stocks_total = 0;

        if($this->sql->record != null)
        {
            foreach($this->sql->record as $rec)
            {
                $this->stocks_total += $rec[2];
                $this->stocks[$rec[0]] = number_format($rec[2],2);
                $this->warehouse_codes[] = $rec[0];
                $this->warehouse_names[$rec[0]] = $rec[1];
            }
        }
        $this->stocks_total = number_format($this->stocks_total,2);

        // 入庫を問い合わせ。指定日が対象。
        $_query  = "select t.warehouse_code, w.name, sum(t.amount) from t_main t, m_warehouse w";
        $_query .= " where t.act_date='$this->target_date'";
        $_query .= "  and  t.com_code='$this->commodity_code'";
        $_query .= "  and  w.code=t.warehouse_code";
        $_query .= "  and  (t.act_flag='1' or t.act_flag='2' or t.act_flag='3')";
        $_query .= "  group by t.warehouse_code,w.name";
        $_query .= "  order by t.warehouse_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }

        $this->arrivals_total = 0;

        if($this->sql->record != null)
        {
            foreach($this->sql->record as $rec)
            {
                $this->arrivals_total += $rec[2];
                $this->arrivals[$rec[0]] = number_format($rec[2],2);
                $this->warehouse_codes[] = $rec[0];
                $this->warehouse_names[$rec[0]] = $rec[1];
            }
        }

        $this->arrivals_total = number_format($this->arrivals_total,2);

        // 出庫を問い合わせ。指定日が対象。
        $_query  = "select t.warehouse_code, w.name, sum(t.amount)*(-1) from t_main t, m_warehouse w";
        $_query .= " where t.act_date='$this->target_date'";
        $_query .= "  and  t.com_code='$this->commodity_code'";
        $_query .= "  and  w.code=t.warehouse_code";
        $_query .= "  and  (t.act_flag='5' or t.act_flag='6' or t.act_flag='7')";
        $_query .= "  group by t.warehouse_code,w.name";
        $_query .= "  order by t.warehouse_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }

        $this->shipments_total = 0;

       //  print $_query;

        if($this->sql->record != null)
        {
            foreach($this->sql->record as $rec)
            {
                // $this->shipments_total += $rec[0];

                $this->shipments_total += $rec[2];
                $this->shipments[$rec[0]] = number_format($rec[2],2);
                $this->warehouse_codes[] = $rec[0];
                $this->warehouse_names[$rec[0]] = $rec[1];
            }
        }
        $this->shipments_total = number_format($this->shipments_total,2);

        if(is_array($this->warehouse_codes))
        {
            $this->warehouse_codes = array_unique($this->warehouse_codes);
        }
        return true;
    }

    function getNextDate($formvars)
    {
        if(strlen($formvars['year'])==0 || strlen($formvars['month'])==0 || strlen($formvars['date'])==0)
        {
            $this->error = "日付け入力が正しくありません";
        }
        else
        {
            $this->target_date = OlutApp::getNextDate($formvars['year'],$formvars['month'],$formvars['date']);
        }
    }

    function getPreviousDate($formvars)
    {
        if(strlen($formvars['year'])==0 || strlen($formvars['month'])==0 || strlen($formvars['date'])==0)
        {
            $this->error = "日付け入力が正しくありません";
        }
        else
        {
            $this->target_date = OlutApp::getPreviousDate($formvars['year'],$formvars['month'],$formvars['date']);
        }
    }

    /*
    *  商品コードから商品名を得る。
    */

    function getCommodityName($code)
    {
        $_query = "select name from m_commodity where code='$code' and deleted is null";
        if($this->sql->query($_query,SQL_INIT)==false)
        {
            return null;
        }
        return $this->sql->record[0];
    }
}

?>