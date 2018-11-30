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
*    入出庫実績表示 - ArrivalShipmentCheckClass.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*    2005/10/18 ver 1.00.01 Changed logic for 開始在庫、終了在庫、現在庫.
*
*/
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('OlutAppLib.php');


// database configuration
class ArrivalShipmentCheck_SQL extends SQL
{
    function ArrivalShipmentCheck_SQL()
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
class ArrivalShipmentCheck_Smarty extends Smarty
{
    function ArrivalShipmentCheck_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

class LineInfo
{
    var $date;
    var $arrival_amount;
    var $arrival_price;
    var $shipment_amount;
    var $shipment_price;
}

/*
*
*/

class ArrivalShipmentCheck extends OlutApp
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;

    var $commodity_code;
    var $commodity_name;
    var $commodity_unit_name;
    var $commoditu_unit_price;

    //
    var $line_info = array();

    var $date_from;
    var $date_to;

    var $arrival_total_price = 0;
    var $arrival_total_amount = 0;
    var $shipment_total_price = 0;
    var $shipment_total_amount = 0;
    var $stock_start_amount = 0;
    var $stock_start_price = 0;
    var $stock_end_amount = 0;
    var $stock_end_price = 0;
    var $stock_current_amount = 0;
    var $stock_current_price = 0;

    var $balance = 0;


    function ArrivalShipmentCheck()
    {
        $this->sql =& new ArrivalShipmentCheck_SQL();
        $this->tpl =& new ArrivalShipmentCheck_Smarty();

        // 本日でよいのか？ @later
        OlutApp::getCurrentProcessDate($this->sql,&$year,&$month);
        $this->date_from = $year . $month . '01';
        $this->date_to   = date('Ymd');

        $this->restoreFromSession();

    }

    function saveToSession($formvars)
    {
        //
        if(isset($formvars['date_from']))
        {
            $_SESSION['DATE_FROM']   = $this->date_from = $formvars['date_from'];
        }

        if(isset($formvars['date_to']))
        {
            $_SESSION['DATE_TO']    = $this->date_to = $formvars['date_to'];
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
        $_SESSION['COMMODITY_UNIT_NAME']  = $this->commodity_unit_name;
        $_SESSION['COMMODITY_UNIT_PRICE'] = $this->commodity_unit_price;

    }

    function restoreFromSession()
    {
        if($_SESSION['DATE_FROM'] != null)
        {
            $this->date_from    = $_SESSION['DATE_FROM'];
        }
        if($_SESSION['DATE_TO'] != null)
        {
            $this->date_to    = $_SESSION['DATE_TO'];
        }

        $this->commodity_code   = $_SESSION['COMMODITY_CODE'];
        $this->commodity_name   = $_SESSION['COMMODITY_NAME'];
        $this->commodity_unit_price = $_SESSION['COMMODITY_UNIT_PRICE'];
        $this->commodity_unit_name  = $_SESSION['COMMODITY_UNIT_NAME'];

    }

    function renderScreen($formvars)
    {
        // assign the form vars
        $this->tpl->assign('commodity_code',$this->commodity_code);
        $this->tpl->assign('commodity_name',$this->commodity_name);
        $this->tpl->assign('commodity_unit_price',$this->commodity_unit_price);
        $this->tpl->assign('commodity_unit_name',$this->commodity_unit_name);

        $this->tpl->assign('arrivals',  $this->arrivals);
        $this->tpl->assign('shipments', $this->shipments);
        $this->tpl->assign('stocks',    $this->stocks);
        $this->tpl->assign('arrivals_total',  $this->arrivals_total);
        $this->tpl->assign('shipments_total', $this->shipments_total);
        $this->tpl->assign('stocks_total',    $this->stocks_total);


        $this->tpl->assign('date_from',    $this->date_from);
        $this->tpl->assign('date_to',      $this->date_to);

        $this->tpl->assign('arrival_total_amount',   $this->arrival_total_amount);
        $this->tpl->assign('arrival_total_price',    $this->arrival_total_price);

        $this->tpl->assign('shipment_total_amount',   $this->shipment_total_amount);
        $this->tpl->assign('shipment_total_price',    $this->shipment_total_price);

        $this->tpl->assign('stock_start_amount',   $this->stock_start_amount);
        $this->tpl->assign('stock_start_price',    $this->stock_start_price);

        $this->tpl->assign('stock_current_amount',   $this->stock_current_amount);
        $this->tpl->assign('stock_current_price',    $this->stock_current_price);

        $this->tpl->assign('stock_end_amount',   $this->stock_end_amount);
        $this->tpl->assign('stock_end_price',    $this->stock_end_price);

        $this->tpl->assign('balance',    $this->balance);
        $this->tpl->assign('line_info',  $this->line_info);

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ArrivalShipmentCheckForm.tpl');
    }

    //

    function getStockData($formvars)
    {
        //
        if(strlen($formvars['date_from'])==0 || strlen($formvars['date_to'])==0)
        {
            $this->error = "日付け入力が正しくありません";
            return;
        }

        if(OlutApp::checkDate($formvars['date_from'])==false)
        {
            $this->error = "開始日付け入力が正しくありません";
            return;
        }

        if(OlutApp::checkDate($formvars['date_to'])==false)
        {
            $this->error = "終了日付け入力が正しくありません";
            return;
        }

        if(strlen($formvars['commodity_code']) == 0)
        {
            $this->error = "商品コードを入力してください";
            return;
        }

        $this->date_from = $formvars['date_from'];
        $this->date_to   = $formvars['date_to'];
        $this->commodity_code = $formvars['commodity_code'];

        //
        $this->commodity_name = $this->getCommodityName($this->commodity_code);
        $_SESSION['COMMODITY_NAME'] = $this->commodity_name;

        //
        // date_fromまでの在庫が開始在庫。
        // 20050101
        // 0123456789

        // 注意： 開始在庫に $this->date_fromの日にあった、入出庫はカウントしない。
        //        つまり、１日前までの在庫を得ることになる。
        //        さらに $this->date_from が　月の最初の日ならば先月の最後の日までの
        //        在庫を得るのではなく、月の最初の在庫をとなる。

        if(substr($this->date_from,6,2)=='01')
        {
            $start = substr($this->date_from,0,4) . '/' . substr($this->date_from,4,2) . '/01';
            $this->getInventoryOfFirstDay($start,$this->stock_start_amount,$this->stock_start_price);
        }
        else
        {
            // 前日を得る。
            $prev_date = OlutApp::getPreviousDateWithoutSlashes(substr($this->date_from,0,4),substr($this->date_from,4,2),substr($this->date_from,6,2));

            $start = substr($prev_date,0,4) . '/' . substr($prev_date,4,2) . '/01';
            $end   = substr($prev_date,0,4) . '/' . substr($prev_date,4,2) . '/' . substr($prev_date,6,2);

            $_query  = "select sum(t.amount),sum(t.total_price) from t_main t";
            $_query .= " where t.act_date>='$start'";
            $_query .= "  and  t.act_date<='$end'";
            $_query .= "  and  t.com_code='$this->commodity_code'";

            if($this->sql->query($_query,SQL_ALL)==false)
            {
                print $_query;
                $this->error = $this->sql->error;
                return false;
            }

            if($this->sql->record != null)
            {
                if($this->sql->record[0][0] != null)
                {
                    $this->stock_start_amount = number_format($this->sql->record[0][0],2);
                }
                else
                {
                    $this->stock_start_amount = "0.00";
                }
                if($this->sql->record[0][1])
                {
                    $this->stock_start_price  = $this->sql->record[0][1];
                }
                else
                {
                    $this->stock_start_price  = 0;
                }
            }
        }

        //
        // date_to までの在庫が終了在庫
        //
        $start = substr($this->date_to,0,4) . '/' . substr($this->date_to,4,2) . '/01';
        $end   = substr($this->date_to,0,4) . '/' . substr($this->date_to,4,2) . '/' . substr($this->date_to,6,2);

        $init_amount = 0;
        $init_price  = 0;

        if(!$this->checkInventoryRecord($start))
        {
            // 在庫レコードが無い場合は計算在庫を使う。
            $this->getInventoryOfLastMonth($start,$init_amount,$init_price);
        }

        $_query  = "select sum(t.amount),sum(t.total_price) from t_main t";
        $_query .= " where t.act_date>='$start'";
        $_query .= "  and  t.act_date<='$end'";
        $_query .= "  and  t.com_code='$this->commodity_code'";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            $this->error = $this->sql->error;
            return false;
        }

        if($this->sql->record != null)
        {
            if($this->sql->record[0][0] != null)
            {
                $this->stock_end_amount = number_format($this->sql->record[0][0],2);
            }
            else
            {
                $this->stock_end_amount = "0.00";
            }
            if($this->sql->record[0][1] != null)
            {
                $this->stock_end_price  = $this->sql->record[0][1];
            }
            else
            {
                $this->stock_end_price  = 0;
            }
        }
        $this->stock_end_amount += $init_amount;
        $this->stock_end_price  += $init_price;


        //
        // システムデートまでの在庫が終了在庫
        //
        $sysdate = date('Ymd');
        $start = substr($sysdate,0,4) . '/' . substr($sysdate,4,2) . '/01';
        $end   = substr($sysdate,0,4) . '/' . substr($sysdate,4,2) . '/' . substr($sysdate,6,2);

        $init_amount = 0;
        $init_price  = 0;

        if(!$this->checkInventoryRecord($start))
        {
            // 在庫レコードが無い場合は計算在庫を使う。
            $this->getInventoryOfLastMonth($start,$init_amount,$init_price);
        }

        $_query  = "select sum(t.amount),sum(t.total_price) from t_main t";
        $_query .= " where t.act_date>='$start'";
        $_query .= "  and  t.act_date<='$end'";
        $_query .= "  and  t.com_code='$this->commodity_code'";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            $this->error = $this->sql->error;
            return false;
        }

        if($this->sql->record != null)
        {
            if($this->sql->record[0][0] != null)
            {
                $this->stock_current_amount = number_format($this->sql->record[0][0],2);
            }
            else
            {
                $this->stock_current_amount = "0.00";
            }
            if($this->sql->record[0][1] != null)
            {
                $this->stock_current_price  = $this->sql->record[0][1];
            }
            else
            {
                $this->stock_current_price  = 0;
            }
        }
        $this->stock_current_amount += $init_amount;
        $this->stock_current_price  += $init_price;

        //
        //  ここから日付けごとの入、出庫を得る。subqueryで金額、数量のカラム２個を返す
        //  ことは出来ないので、ここでは、入庫、出庫について別々の問い合わせとする。
        //

        $this->line_info = array();  // just init.

        // 入荷

        $start = substr($this->date_from,0,4) . '/' . substr($this->date_from,4,2) . '/' . substr($this->date_from,6,2);
        $end   = substr($this->date_to,0,4) . '/' . substr($this->date_to,4,2) . '/' . substr($this->date_to,6,2);

        $_query  = "select act_date,sum(amount),sum(total_price) from t_main";
        $_query .= " where (act_flag='1' or act_flag='3')";
        $_query .= " and act_date>='$start'";
        $_query .= " and act_date<='$end'";
        $_query .= " and com_code='$this->commodity_code'";
        $_query .= " group by act_date order by act_date";

        if(!$this->sql->query($_query,SQL_ALL))
        {
            print $_query;
            return false;
        }

        if($this->sql->record != null)
        {
            foreach($this->sql->record as $rec)
            {
                $info = new LineInfo;
                $info->date = $rec[0];
                $info->arrival_amount = number_format($rec[1],2);
                $info->arrival_price  = number_format($rec[2]);
                $this->line_info[]    = $info;

                $this->arrival_total_price  += $rec[2];
                $this->arrival_total_amount += $rec[1];
            }
        }

        // 出荷

        $_query  = "select act_date,sum(amount),sum(total_price) from t_main";
        $_query .= " where (act_flag='5' or act_flag='6' or act_flag='7')";
        $_query .= " and act_date>='$start'";
        $_query .= " and act_date<='$end'";
        $_query .= " and com_code='$this->commodity_code'";
        $_query .= " group by act_date order by act_date";

        if(!$this->sql->query($_query,SQL_ALL))
        {
            print $_query;
            return false;
        }

        if($this->sql->record != null)
        {
            foreach($this->sql->record as $rec)
            {
                $found = false;

                // 既存の配列にあるか？

                foreach($this->line_info as $key => $li)
                {
                    if(strcmp($li->date,$rec[0])==0)
                    {
                        $this->line_info[$key]->shipment_amount = number_format(-$rec[1],2);
                        $this->line_info[$key]->shipment_price  = number_format(-$rec[2]);
                        $found = true;
                        break;
                    }
                }

                if(!$found)
                {
                    $info = new LineInfo;
                    $info->date = $rec[0];
                    $info->shipment_amount = number_format(-$rec[1],2);
                    $info->shipment_price  = number_format(-$rec[2]);
                    $info->arrival_amount  = "0.00";
                    $info->arrival_price   = 0;
                    $this->line_info[] = $info;
                }

                $this->shipment_total_price  -= $rec[2];
                $this->shipment_total_amount -= $rec[1];
            }
        }

        if(is_array($this->line_info))
        {
            sort($this->line_info);

            // 日付のフォーマット。
            for($i=0; $i<count($this->line_info); $i++)
            {
                $this->line_info[$i]->date = ereg_replace('-','/',$this->line_info[$i]->date);
            }

            $this->balance = number_format($this->shipment_total_price - $this->arrival_total_price);

            //
            $this->arrival_total_amount  = number_format($this->arrival_total_amount,2);
            $this->arrival_total_price   = number_format($this->arrival_total_price);
            $this->shipment_total_price  = number_format($this->shipment_total_price);
            $this->shipment_total_amount = number_format($this->shipment_total_amount,2);
        }

        return true;
    }

    /*      added 2005/10/18.
    *      在庫レコードがある場合はTrueを返す。
    */
    function checkInventoryRecord($dt)
    {
        //--------------------------------------------------------------
        //  注意： 2005/10/01 以降は act_type=0 のレコードが無い場合は、
        //  棚卸されていない、と判断できる。それ以前はレコードが無いの
        //　は、在庫ゼロ、と判断できる。 (NTC様の都合なので注意）
        //--------------------------------------------------------------
        if($dt < '2005/10/01')
        {
            // 常に棚卸レコードがある、と思ってよい。
            return true;
        }

        $_query  = "select t.amount,t.total_price from t_main t";
        $_query .= " where t.act_date='$dt'";
        $_query .= "  and  t.act_flag=0";
        $_query .= "  and  t.com_code='$this->commodity_code'";

        if($this->sql->query($_query,SQL_INIT)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }
        if($this->sql->record == null)
        {
            return false;
        }
        return true;
    }

    /*  added 2005/10/18.
    *  上の関数で在庫レコードが無いと分かった場合(つまり、棚卸されていない）
    *  にはその前の月の計算在庫を得る。
    */
    function getInventoryOfLastMonth($dt,&$result_amount,&$result_price)
    {
        // 初期化
        $result_amount = 0;
        $result_price  = 0;

        $last_month_start = OlutApp::getPrevMonth($dt);
        $last_date  = OlutApp::getLastDate(substr($last_month_start,0,4),substr($last_month_start,5,2));
        $last_month_end   = substr($last_month_start,0,7) . '/' . $last_date;

        $_query  = "select sum(t.amount),sum(t.total_price) from t_main t";
        $_query .= " where t.act_date>='$last_month_start'";
        $_query .= "  and  t.act_date<='$last_month_end'";
        $_query .= "  and  t.com_code='$this->commodity_code'";

        if($this->sql->query($_query,SQL_INIT)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }
        if($this->sql->record == null)
        {
            return false;
        }

        $result_amount = $this->sql->record[0];
        $result_price  = $this->sql->record[1];

        return true;
    }
    
    /*
     *  01日の在庫レコードを読みます。
     */

    function getInventoryOfFirstDay($dt,&$result_amount,&$result_price)
    {
        // 初期化
        $result_amount = 0;
        $result_price  = 0;

        $_query  = "select t.amount,t.total_price from t_main t";
        $_query .= " where t.act_date='$dt'";
        $_query .= "  and  t.act_flag=0";
        $_query .= "  and  t.com_code='$this->commodity_code'";

        if($this->sql->query($_query,SQL_INIT)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }
        if($this->sql->record == null)
        {
            return false;
        }

        $result_amount = $this->sql->record[0];
        $result_price  = $this->sql->record[1];

        return true;
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