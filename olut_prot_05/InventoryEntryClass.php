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
 *    棚卸入力 - InventoryEntryClass.php
 *
 *   Release History:
 *    2005/09/30  ver 1.00.00 Initial Release
 *    2005/10/08  ver 1.00.01 Fixed bugs for new inventory mode.
 *    2005/10/12  ver 1.00.02 Clear offset session flag when "start". 
 *    2005/10/13  ver 1.00.03 Fixed act_flag of update mode. 
 *    2005/10/24  ver 1.00.04 在庫金額の計算を修正。
 *
 */

require_once('OlutAppLib.php');

class InventoryEntry extends OlutApp
{
    //
    var $target_month;
    var $target_year;
    var $ship_section_code;
    var $ship_section_name;
    var $warehouse_code;
    var $warehouse_name;
    var $commodity_count;
    var $commodity_codes;
    var $commodity_names;
    var $unit_names;
    var $amounts;                   // 計算在庫の配列。
    var $total_price;               // 計算在庫金額の配列。
    var $unit_price;                //
    var $inventory_amounts;         // 実在在庫の配列。
    var $inventory_prices;          // 実在在庫金額の配列。
    var $display_lines = 5;         // 1ページに表示する商品行の数。
    var $offset = 0;                // レコードオフセット
    var $search_from;               // 検索対象商品コード
    var $start_date;
    var $end_date;
    var $new_inventry_mode = false; // 新規追加モード
    var $commodity_name;
    var $commodity_code;
    var $unit_code;
    var $unit_name;
    var $inventory_price;           // 新規棚卸価格
    var $inventory_amount;          // 新規棚卸数量
    var $error;
    var $calc_flag;                 // 金額手入力フラグ

    //
    // constructor.
    //
    function InventoryEntry()
    {
        // instantiate the sql object
        $this->sql =& new InventoryEntry_SQL;

        // instantiate the template object
        $this->tpl =& new InventoryEntry_Smarty;

        //
        $this->display_lines     = $_SESSION['screen_lines'];

        //
        $this->restoreFromSession();
    }

    function newInventoryMode()
    {
        $this->new_inventry_mode = true;
    }

    /*
    *      棚卸範囲を設定する画面を表示する。
    */

    function renderSelectionForm($formvars=array())
    {
        if(!isset($formvars['year']))
        {
            $year  = null;
            $month = null;
            OlutApp::getCurrentProcessDate($this->sql,&$year,&$month);
            $formvars['year']  = $year;
            $formvars['month'] = $month;
        }
        $this->tpl->assign('year_list',$this->getYearList($formvars));
        $this->tpl->assign('month_list',$this->getMonthList($formvars));
        $this->tpl->assign('shipment_section_list',$this->getShipmentSectionList($formvars));
        $this->tpl->assign('warehouse_list', $this->getWarehouseList($formvars));
        //
        $this->tpl->display('InventorySelectForm.tpl');
    }

    /*
    *   選択画面のフォーム値を必要な内部変数に取り込む。
    *
    */
    function parseSelectionForm($formvars=array())
    {
        $this->target_year = $formvars['year'];
        $this->target_month = $formvars['month'];
        $this->ship_section_code = $formvars['shipment_section_code'];
        $this->warehouse_code = $formvars['warehouse_code'];

        $this->start_date = "$this->target_year/$this->target_month/01";
        $last = OlutApp::getLastDate($this->target_year,$this->target_month);
        $this->end_date   = "$this->target_year/$this->target_month/$last";
    }

    /*
    *  在庫数を画面から得てクラス変数に取り込む。
    */
    function parseInventory($formvars=array())
    {
        for($i=0; $i<$this->display_lines; $i++)
        {
            $id = "inventory_amount_$i";
            if(isset($formvars[$id]))
            {
                $this->inventory_amounts[$i] = $formvars[$id];
            }

            $id = "inventory_price_$i";
            if(isset($formvars[$id]))
            {
                $this->inventory_prices[$i] = $formvars[$id];
            }
            $id = "calc_flag_$i";
            if(isset($formvars[$id]))     // チェックボックスなので値が存在することがチェック状態。
            {
                $this->calc_flag[$i] = '1';
            }
            else 
            {
                $this->calc_flag[$i] = '0';
            }
        }
    }

    /*
    *  選択画面など必要な値はセッションに保存する。
    */

    function saveToSession()
    {
        $_SESSION['target_month']       = $this->target_month;
        $_SESSION['target_year']        = $this->target_year;
        $_SESSION['ship_section_code']  = $this->ship_section_code;
        $_SESSION['ship_section_name']  = $this->ship_section_name;
        $_SESSION['warehouse_code']     = $this->warehouse_code;
        $_SESSION['warehouse_name']     = $this->warehouse_name;
        $_SESSION['commodity_count']    = $this->commodity_count;
        $_SESSION['commodity_codes']    = $this->commodity_codes;
        $_SESSION['commodity_names']    = $this->commodity_names;
        $_SESSION['unit_names']         = $this->unit_names;
        $_SESSION['amaounts']           = $this->amounts;
        $_SESSION['offset']             = $this->offset;
        $_SESSION['search_from']        = $this->search_from;
        $_SESSION['start_date']         = $this->start_date;
        $_SESSION['end_date']           = $this->end_date;
        $_SESSION['total_price']        = $this->total_price;
        $_SESSION['unit_price']         = $this->unit_price;
        $_SESSION['inventory_prices']   = $this->inventory_prices;
        $_SESSION['inventory_amounts']  = $this->inventory_amounts;
        $_SESSION['calc_flag']          = $this->calc_flag;

        // 新規登録時
        $_SESSION['commodity_code']   = $this->commodity_code;
        $_SESSION['commodity_name']   = $this->commodity_name;
        $_SESSION['unit_code']        = $this->unit_code;
        $_SESSION['unit_name']        = $this->unit_name;
        $_SESSION['inventory_amount'] = $this->inventory_amount;
        $_SESSION['inventory_price']  = $this->inventory_price;
        
        $_SESSION['new_inventory_mode'] = $this->new_inventry_mode;

    }

    /*
    *  名称などマスターに問い合わせ。
    */
    function referMasters()
    {
        $this->ship_section_name = $this->getShipSectionName($this->ship_section_code);
        $this->warehouse_name    = $this->getWarehouseName($this->warehouse_code);
    }

    /*
    *  前、次ボタンなどの移動で失われる値はセッションからリストアする。
    */
    function restoreFromSession()
    {
        $this->target_month      = $_SESSION['target_month'];
        $this->target_year       = $_SESSION['target_year'];
        $this->ship_section_code = $_SESSION['ship_section_code'];
        $this->ship_section_name = $_SESSION['ship_section_name'];
        $this->warehouse_code    = $_SESSION['warehouse_code'];
        $this->warehouse_name    = $_SESSION['warehouse_name'];
        $this->commodity_count   = $_SESSION['commodity_count'];
        $this->commodity_codes   = $_SESSION['commodity_codes'];
        $this->commodity_names   = $_SESSION['commodity_names'];
        $this->unit_names        = $_SESSION['unit_names'];
        $this->amounts           = $_SESSION['amaounts'];
        $this->offset            = $_SESSION['offset'];
        $this->search_from       = $_SESSION['search_from'];
        $this->start_date        = $_SESSION['start_date'];
        $this->end_date          = $_SESSION['end_date'];
        $this->total_price       = $_SESSION['total_price'];
        $this->unit_price        = $_SESSION['unit_price'];
        $this->display_lines     = $_SESSION['screen_lines'];
        $this->inventory_prices  = $_SESSION['inventory_prices'];
        $this->inventory_amounts = $_SESSION['inventory_amounts'];
        $this->calc_flag         = $_SESSION['calc_flag'];

        // 新規登録時
        $this->commodity_code = $_SESSION['commodity_code'];
        $this->commodity_name = $_SESSION['commodity_name'];
        $this->unit_code      = $_SESSION['unit_code'];
        $this->unit_name      = $_SESSION['unit_name'];
        $this->inventory_amount = $_SESSION['inventory_amount'];
        $this->inventory_price  = $_SESSION['inventory_price'];
        
        $this->new_inventry_mode = $_SESSION['new_inventory_mode'];
    }

    /*
    *  新規登録で使う部分だけクリアする。
    */

    function clear()
    {
        $_SESSION['commodity_code'] = '';
        $_SESSION['commodity_name'] = '';
        $_SESSION['unit_code'] = '';
        $_SESSION['unit_name'] = '';
        $_SESSION['inventory_amount'] = '';
        $_SESSION['inventory_price']  = '';
        $_SESSION['offset'] = '';             // added 2005/10/12

        $this->commodity_code = '';
        $this->commodity_name = '';
        $this->unit_code = '';
        $this->unit_name = '';
        $this->inventory_amount = '';
        $this->inventory_price = '';
        
        $this->offset = 0;                  // added 2005/10/12
    }

    /*
    *  次ボタン。
    */

    function next()
    {
        $this->offset += $this->display_lines;
    }

    /*
    *  前ボタン
    */

    function previous()
    {
        $this->offset -= $this->display_lines;
        if($this->offset < 0)
        {
            $this->offset = 0;
        }
    }

    /*
    *  検索コードをセットする。
    */
    function setSearchArg($formvars=array())
    {
        $arg = $formvars['search_from'];
        if(isset($arg) && strlen($arg))
        {
            $this->search_from = $arg;
        }
        else {
            $this->search_from = null;
        }
    }

    /*
    *  在庫入力画面の表示
    */

    function renderEntryForm($formvars=array())
    {
        // assign the form vars
        $this->tpl->assign('target_year', $this->target_year);
        $this->tpl->assign('target_month',$this->target_month);
        $this->tpl->assign('ship_section_name', $this->ship_section_name);
        $this->tpl->assign('warehouse_name',    $this->warehouse_name);
        $this->tpl->assign('commodity_codes',   $this->commodity_codes);
        $this->tpl->assign('commodity_names',   $this->commodity_names);
        $this->tpl->assign('unit_names',        $this->unit_names);
        $this->tpl->assign('amounts',           $this->amounts);
        $this->tpl->assign('commodity_count',   $this->commodity_count);
        $this->tpl->assign('search_from',       $this->search_from);
        $this->tpl->assign('calc_flag',         $this->calc_flag);

        //
        if($this->new_inventry_mode)
        {
            $this->tpl->assign('new_inventory_mode',$this->new_inventry_mode);
            $this->tpl->assign('commodity_code',$this->commodity_code);
            $this->tpl->assign('commodity_name',$this->commodity_name);
            $this->tpl->assign('unit_name',     $this->unit_name);
            $this->tpl->assign('inventory_price',  $this->inventory_price);
            $this->tpl->assign('inventory_amount', $this->inventory_amount);
        }
        else
        {
            $this->tpl->assign('inventory_amount',  $this->inventory_amounts);
            $this->tpl->assign('inventory_price',   $this->inventory_prices);
        }

        // 表示用にカンマ付け。
        if( $this->total_price != null and is_array($this->total_price))
        {
            foreach($this->total_price as $key => $value)
            {
                $tmp[$key] = number_format($value);
            }
            $this->tpl->assign('total_price', $tmp);
        }

        // 表示用にカンマ付け。
        if( $this->unit_price != null && is_array($this->unit_price))
        {
            foreach($this->unit_price as $key => $value)
            {
                $unit_price[$key] = number_format($value,2);
            }
            $this->tpl->assign('unit_price', $unit_price);
        }

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('InventoryEntryForm.tpl');
    }

    /*
    *   在庫リストを得る。
    *
    *   注意：　たとえば8月の棚卸とした場合, 2005/08/01の日付けで7月までの棚卸在庫レコードを作る。
    *           なので、8月の指定日の現在庫を問い合わせたい場合はレコードタイプに関係なく、
    *           2005/08/01から2005/08/31 を集計すると8月の現在庫が分かる。
    *
    *           棚卸レコードは次の月の01日付けで挿入していることに注意。
    *
    */

    function getInventoryList($formvars=array())
    {
        //
        // 最初にトランザクションと在庫レコードをスキャンします。
        //
        $target_date_from = "$this->target_year/$this->target_month/01";
        $target_date_to   = OlutApp::getNextMonth($target_date_from);

        $_query  = "select distinct t.com_code,c.name,u.name from t_main t, m_commodity c, m_unit u where ";
        $_query .= " t.act_date>='$target_date_from' and t.act_date<='$target_date_to'";
        $_query .= " and c.code=t.com_code and c.unit_code = u.code";
        $_query .= " and t.warehouse_code='$this->warehouse_code'";     // 倉庫を限定する。
        $_query .= " and t.ship_sec_code='$this->ship_section_code'";   // 資材内コードを限定する

        if(strlen($this->search_from))
        {
            $_query .= " and t.com_code>='$this->search_from%'";
        }
        $_query .= " order by t.com_code";

        if($this->offset==null)
        {
            $this->offset = 0;
        }
        $_query .= " limit $this->display_lines offset $this->offset";

        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            $n = 0;
            foreach($this->sql->record as $rec)
            {
                $this->commodity_codes[$n] = trim($rec[0]);
                $this->commodity_names[$n] = trim($rec[1]);
                $this->unit_names[$n]      = trim($rec[2]);
                $this->amounts[$n]         = 0;
                $this->total_price[$n]     = 0;

                $n++;
            }
            $this->commodity_count = $n;
        }
        else
        {
            $this->error = $this->sql->error;
            return false;
        }

        if($n==0)
        {
            $this->error = "データの終わりまたはデータが存在しません。";
            $this->previous();
            return false;
        }

        //
        // 対象となる商品について計算在庫などを求める。
        //

        $target_date_from = "$this->target_year/$this->target_month/01";
        $last_day = OlutApp::getLastDate($this->target_year,$this->target_month);
        $target_date_to   = "$this->target_year/$this->target_month/$last_day";

        //
        $this->unit_price = array();
        $this->calc_flag  = array();

        //
        // トランザクションの入出庫を得る。
        //
        // 1. 値引きのレコードもカウントする。
        // 2. 値引きのレコードの数量は入力側でかならずゼロをセットするように。
        //
        for($n=0; $n<$this->commodity_count; $n++)
        {

            $commodity_code = $this->commodity_codes[$n];

            $_query =  "select t.com_code, c.name, u.name, sum(t.amount),";

            // 入庫+在庫 金額のサブ・クエリ
            $_query .= " (select sum(total_price) from t_main where (act_flag='1' or act_flag='0') ";
            $_query .= " and act_date>='$target_date_from' and act_date<='$target_date_to' and com_code=t.com_code),";

            // 入庫+在庫 数のサブ・クエリ
            $_query .= " (select sum(amount) from t_main where (act_flag='1' or act_flag='0') ";
            $_query .= " and com_code=t.com_code";
            $_query .= " and act_date>='$target_date_from' and act_date<='$target_date_to') ";

            //
            $_query .= " from t_main t, m_commodity c, m_unit u ";
            $_query .= " where t.com_code='$commodity_code' and c.code=t.com_code and c.unit_code = u.code ";
            $_query .= " and t.warehouse_code='$this->warehouse_code'";     // 倉庫を限定する。
            $_query .= " and t.ship_sec_code='$this->ship_section_code'";   // 資材内コードを限定する
            $_query .= " and t.act_date >= '$target_date_from' ";
            $_query .= " and t.act_date <= '$target_date_to'";              //

            if(strlen($this->search_from))
            {
                $_query .= " and t.com_code>='$this->search_from%'";
            }
            $_query .= " group by t.com_code,c.name,u.name order by t.com_code ";

            // if($this->offset==null)
            // {
            //     $this->offset = 0;
            // }
            // $_query .= "limit $this->display_lines offset $this->offset";

            //if($commodity_code=='12203')
            //{
            //print $_query;
            //}

            if( $this->sql->query($_query,SQL_INIT) == true )
            {
                // 在庫があるものだけ.... はトランザクションのを見てよいのか？
                // マイナスでも出す。レコードがあれば出す。

                if($this->sql->record != null )
                {
                    // $this->commodity_codes[$n] = trim($this->sql->record[0]);
                    // $this->commodity_names[$n] = trim($this->sql->record[1]);
                    // $this->unit_names[$n]      = trim($this->sql->record[2]);
                    $this->amounts[$n]         = trim($this->sql->record[3]);

                    // 平均単価　＝　入荷+在庫の金額合計 / 入荷+在庫の総数
                    if($this->sql->record[5] != 0)
                    {
                        // 2005/10/12
                        // $this->unit_price[$n] = round($this->sql->record[4]/$this->sql->record[5],2);
                        $this->unit_price[$n] = $this->sql->record[4]/$this->sql->record[5];

                    }
                    else
                    {
                        $this->unit_price[$n] = 0;
                    }

                    // 計算在庫金額　＝　在庫数　＊　平均単価。
                    // 
                    // floor(334.71*129) = 40164 になる？
                    // floor($this->amounts[$n]*$this->unit_price[$n]);
                    // $this->total_price[$n]     = $this->sql->record[5];   // [4] -> [5]
                    
                    // 2005/10/24 以下の計算に変更。
                    // $this->total_price[$n] = bcmul($this->amounts[$n],$this->unit_price[$n],0);
                    
                    // 1円落ちるので round にした。
                    $this->total_price[$n] = round($this->amounts[$n]*$this->unit_price[$n],0);
                    
                }
            }
            else
            {
                // print $_query;
                return false;
            }
        }

        //
        //  すでに入力してある在庫を取得。
        //
        for($i=0; $i<$this->commodity_count; $i++)
        {
            // デフォルト値として計算在庫、計算在庫金額をセット
            $this->inventory_amounts[$i] = $this->amounts[$i];
            $this->inventory_prices[$i]  = $this->total_price[$i];
            //
            if($this->getInventoryAmountAndPrice($this->commodity_codes[$i],
                                                 &$this->inventory_amounts[$i],
                                                 &$this->inventory_prices[$i],
                                                 &$this->calc_flag[$i]))
            {
                if(!isset($this->unit_price[$i]))
                {
                    if($this->inventory_amounts[$i] != 0)
                    {
                        $this->unit_price[$i] = $this->inventory_prices[$i] / $this->inventory_amounts[$i];
                    }
                }
            }
        }

        return true;
    }

    //
    // select sum(amount),(select sum(total_price) from t_main where act_flag=1
    // and act_date>='2005/06/01' and act_date<='2005/06/30' and com_code=21209 ),(select sum(amount) from t_main
    // where act_flag=1 and com_code=21209 and act_date>='2005/06/01' and act_date<='2005/06/30')
    // from t_main t where com_code=21209 and act_date>='2005/06/01' and act_date <='2005/06/30';

    /*
    *  計算在庫を得る。
    */

    /*
     *  棚卸入力個数を得る。 
     *
     *  ==> 金額手入力フラグも取得するように変更。
     */
    function getInventoryAmountAndPrice($code,&$amount,&$price,&$calc_flag)
    {
        $next_month = OlutApp::getFirstDayOfNextMonth($this->target_year,$this->target_month);

        $_query  = "select amount,total_price,calc_flag from t_main where act_flag='0' and ";
        $_query .= " act_date='$next_month' and ";
        $_query .= " warehouse_code='$this->warehouse_code' and ";
        $_query .= " ship_sec_code ='$this->ship_section_code' and";
        $_query .= " com_code='$code'";

        if($this->sql->query($_query,SQL_ALL) == true)
        {
            if( $this->sql->record != null)
            {
                $amount    = $this->sql->record[0][0];
                $price     = $this->sql->record[0][1];
                $calc_flag = $this->sql->record[0][2];
                return true;
            }
        }
        return false;
    }

    /*
    *  在庫の登録
    */
    function registerInventory()
    {
        if($this->commodity_count==0)
        {
            $this->error = "保存対象商品がありません";
            return false;
        }
        
        // オートコミットモードはやめる。
        $this->sql->Autocommit(false);
        $next_month = OlutApp::getFirstDayOfNextMonth($this->target_year,$this->target_month);

        // 在庫行数
        for($i=0; $i<$this->commodity_count; $i++)
        {
            if($this->findInventoryRecord($this->commodity_codes[$i],$next_month))
            {
                // すでに存在するので更新
                $rc = $this->updateInventory($this->commodity_codes[$i],
                                             $this->inventory_amounts[$i],
                                             $this->inventory_prices[$i],
                                             $next_month,
                                             $this->calc_flag[$i],
                                             $this->unit_price[$i]);
            }
            else
            {
                // 新規挿入
                $rc = $this->insertInventory($this->commodity_codes[$i],
                                             $this->inventory_amounts[$i],
                                             $this->inventory_prices[$i],
                                             $next_month,
                                             $this->calc_flag[$i],
                                             $this->unit_price[$i]);
            }

            if($rc == false)
            {
                $this->sql->rollback();
                $this->sql->autoCommit(true);
                return false;
            }
        }
        $this->sql->commit();
        $this->sql->AutoCommit(true);
    }

    /*
    *  在庫レコードの更新
    */
    function updateInventory($code,$amount,$stock_price,$next_month,$calc_flag,$average_price)
    {
        if(isset($amount) and strlen($amount)>0)
        {
            // 10/24 removed.
            // $average_price = $this->getAveragePrice($code);
            
            if($average_price == null)
            {
                $average_price = 0;
            }
            
            $uid = $_SESSION['user_id'];

            $_query  = "update t_main set amount=$amount,unit_price=$average_price,total_price=$stock_price,";
            $_query .= " calc_flag='$calc_flag',update_userid='$uid',updated=now()";
            $_query .= " where com_code='$code'";
            $_query .= " and ship_sec_code ='$this->ship_section_code' ";
            $_query .= " and warehouse_code='$this->warehouse_code'";
            $_query .= " and act_date='$next_month'";
            $_query .= " and act_flag=0";              // added 2005/10/13.

           // print $_query;

            return $this->sql->query($_query);
        }
        return true;
    }

    /*
    *  新規在庫レコードの挿入
    */
    function insertInventory($code,$amount,$stock_price,$next_month,$calc_flag,$average_price)
    {
        if(isset($amount) and strlen($amount)>0)
        {
            // 在庫金額（カラムはtotal_price) は、棚卸個数と単価を掛けて、
            // 計算してよいのか？？　よく考えよう。 @later
            
            // 10/24 removed.
            // $average_price = $this->getAveragePrice($code);
            
            if($average_price == null)
            {
                $average_price = 0;
            }
            
            $uid = $_SESSION['user_id'];

            $_query = "insert into t_main (act_date,act_flag,com_code,ship_sec_code,";
            $_query .= "warehouse_code,unit_price,total_price,amount,calc_flag,update_userid,updated) ";
            $_query .= "values('$next_month',";
            $_query .= "'0','$code','$this->ship_section_code','$this->warehouse_code'";
            $_query .= ",$average_price,$stock_price,$amount,'$calc_flag','$uid',now())";

            //print $_query;

            return $this->sql->query($_query);
        }
        return true;
    }

    /*
    *
    */
    function parseNewInventory($formvars)
    {
        //
        $this->inventory_amount = $formvars['inventory_amount'];
        $this->commodity_code   = $formvars['commodity_code'];
        $this->inventory_price  = $formvars['inventory_price'];
    }
    
    /*
     *  再計算
     */
    function reCalc($formvars)
    {
        // 画面上で手計算のフラグがチェックされていない行は金額の計算を実行する。
        
        for($i=0; $i<$this->commodity_count; $i++)
        {
            if( $this->calc_flag[$i] != '1' )
            {
                // 端数は切り下げ。
                // $this->inventory_prices[$i] = floor($this->inventory_amounts[$i] * $this->unit_price[$i]);
                if($this->amounts[$i] != 0)
                {
                    $this->inventory_prices[$i] = round($this->inventory_amounts[$i] *  $this->total_price[$i] / $this->amounts[$i],0);
                }
                else 
                {
                    $this->inventory_prices[$i] = 0;
                }
            }
        }
    }

    /*
    *  新規追加のチェック
    */
    function checkNewInventory($formvars)
    {
        if(strlen($formvars['commodity_code'])==0)
        {
            $this->error = "商品コードを入力してください";
            return false;
        }
        $this->commodity_code = $formvars['commodity_code'];

        if(strlen($formvars['inventory_amount']) == 0)
        {
            $this->error = "数量を入力してください";
            return false;
        }
        $this->inventory_amount = $formvars['inventory_amount'];

        if(strlen($formvars['inventory_price']) == 0)
        {
            $this->error = "金額を入力してください";
            return false;
        }
        $this->inventory_price = $formvars['inventory_price'];

        // 商品は存在するか？
        $_query = "select name from m_commodity where code='$this->commodity_code' and ship_section_code='$this->ship_section_code' and deleted is null";
        if(!$this->sql->query($_query,SQL_INIT))
        {
            $this->error = $this->sql->error;
            return false;
        }
        if($this->sql->record == null)
        {
            $this->error = "指定の商品コードが資材内に見つかりません";
            return false;
        }
        $this->commodity_name = $this->sql->record[0];

        //
        // ターゲットの月にすでにエントリがあるか？
        //
        $next_month = OlutApp::getFirstDayOfNextMonth($this->target_year,$this->target_month);
        if($this->findInventoryRecord($this->commodity_code,$next_month)==true)
        {
            $this->error = "指定の商品にはすでに棚卸レコードが存在しています";
            return false;
        }

        return true;
    }

    /*
    *
    */
    function saveNewInventory($formvars)
    {
        //
        $commodity_code   = $formvars['commodity_code'];
        $inventory_amount = $formvars['inventory_amount'];
        $inventory_price  = $formvars['inventory_price'];
        $unit_price = $this->getAveragePrice($commodity_code);
        $next_month = OlutApp::getFirstDayOfNextMonth($this->target_year,$this->target_month,$unit_price);
        
        // added 2005/10/8
        $calc_flag=0;

        return $this->insertInventory($commodity_code,$inventory_amount,$inventory_price,$next_month,$calc_flag);
    }

    /*
    *  移動平均金額を得る。  ==> 多分廃止。
    */
    function getAveragePrice($code)
    {
        $_query = "select trunc(sum(amount*unit_price)/sum(amount),2) from t_main";
        $_query .= " where com_code='$code' ";
        $_query .= " and ship_sec_code='$this->ship_section_code'";
        $_query .= " and warehouse_code='$this->warehouse_code'";
        $_query .= " and (act_flag='1' or act_flag='3')";
        $_query .= " and act_date>='$this->start_date' ";
        $_query .= " and act_date<='$this->end_date'";

        if($this->sql->query($_query,SQL_INIT)==false)
        {
            return 0;  // ええのか？
        }
        // 入庫データが全く無い。
        if($this->sql->record[0] == null)
        {
            return 0;
        }

        return $this->sql->record[0];
    }

    /*
    *  新規
    */
    function findInventoryRecord($code,$next_month)
    {
        $_query  = "select count(*) from t_main where act_flag='0' and ";
        $_query .= " com_code='$code' and ";
        $_query .= " act_date='$next_month' and";
        $_query .= " ship_sec_code='$this->ship_section_code' and";
        $_query .= " warehouse_code='$this->warehouse_code'";

        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            if($this->sql->record[0][0]>0)
            {
                return true;
            }
        }
        return false;
    }

    /*
    *  出荷部門名を得る。
    */
    function getShipSectionName($code)
    {
        $_query = "select name from m_shipment_section where code='$code'";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            return $this->sql->record[0][0];
        }
        return null;
    }

    /*
    *  倉庫名を得る。
    */
    function getWarehouseName($code)
    {
        $_query = "select name from m_warehouse where code='$code'";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            return $this->sql->record[0][0];
        }
        return null;
    }

    /*
    *  年リストを得る。
    */

    function getYearList($formvars=array())
    {
        $year_array = array (2004,2005,2006,2007,2008,2009,2010);
        $result = "";

        if(isset($formvars['year']))
        {
            $target_year = $formvars['year'];
        }
        else
        {
            $target_year = date('Y');
        }

        foreach($year_array as $year)
        {
            if(!strcmp($year,$target_year))
            {
                $result .= "<option value=$year selected>$year</option>";
            }
            else
            {
                $result .= "<option value=$year>$year</option>";
            }
        }
        return $result;
    }

    /*
    *  月のリストを得る。
    */

    function getMonthList($formvars=array())
    {
        $result = "";

        if(isset($formvars['month']))
        {
            $target_month = $formvars['month'];
        }
        else
        {
            $target_month = date('m');
        }

        for($i=1; $i<=12; $i++)
        {
            if($target_month == $i)
            {
                $result .= "<option value=$i selected>$i</option>";
            }
            else
            {
                $result .= "<option value=$i>$i</option>";
            }
        }
        return $result;
    }

    /*
    *  本部部門のリストを得る。
    */
    function getShipmentSectionList($formvars)
    {
        //
        $selected_code = $formvars['ship_section_code'];

        $_query = "select code,name from m_shipment_section order by code";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            $result = "";

            foreach($this->sql->record as $rec)
            {
                $section_code = trim($rec[0]);
                if(!strcmp($section_code,$selected_code))
                {
                    $result .= sprintf("<option value='%s' selected>",$section_code);
                }
                else
                {
                    $result .= sprintf("<option value='%s'>",$section_code);
                }
                $result .= $rec[1];
                $result .= "</option>";
            }
            return $result;
        }
        return null;
    }

    /*
    *  倉庫コードのリストを得る。
    */

    function getWarehouseList($formvars)
    {
        $selected_code = $formvars['warehouse_code'];

        $_query = "select code,name from m_warehouse order by code";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            $result = "";

            foreach($this->sql->record as $rec)
            {
                $warehouse_code = trim($rec[0]);
                if(!strcmp($warehouse_code,$selected_code))
                {
                    $result .= sprintf("<option value='%s' selected>",$warehouse_code);
                }
                else
                {
                    $result .= sprintf("<option value='%s'>",$warehouse_code);
                }
                $result .= $rec[1];
                $result .= "</option>";
            }
            return $result;
        }
        return null;
    }
}

?>