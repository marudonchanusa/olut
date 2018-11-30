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
 *    出荷登録 - ShipmentClass.php
 *
 *   Release History:
 *    2005/09/30 ver 1.00.00 Initial Release
 *    2005/10/08 ver 1.00.01 1. getNewSlipNo should see act_flag=5
 *                           2. Clear comodity name when no commodity code input.
 *
 */

require_once('SystemProfile.php');

class Shipment
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;
    //
    var $system_profile = null;

    // 以下は画面に表示するデータ。配列で１０個持つ。
    var $store_code = null;
    var $store_name = null;
    var $store_section_code = null;     // 店舗内部門コード
    var $commodity_code = null;
    var $commodity_name = null;        // 明細ごとの商品名
    var $unit_name    = null;          // 明細ごとの単位
    var $unit_price   = null;          // 明細ごとの単価
    var $total_price  = null;          // 明細ごとの価格
    var $amount       = null;          // 明細ごとの数量
    var $act_flag = null;              // 明細ごとの支払区分。
    var $year  = null;
    var $month = null;
    var $date  = null;
    var $slip_no = null;               // 伝票番号
    var $memo = null;

    /**
     * class constructor
     */
    function Shipment()
    {
        // instantiate the sql object
        $this->sql =& new Shipment_SQL;

        // instantiate the template object
        $this->tpl =& new Shipment_Smarty;

        //
        $this->system_profile =& new SystemProfile();

        // セッションから内部変数にレストア。
        $this->restoreFromSession();
    }

    /*
    *  POSTされた値をセッションに保存する。
    *  単純に同じフォームならば保存する必要はないが、
    *  商品マスター参照などで$_POSTからの値がなくなるので。
    *
    */
    function savePostedValues($formvars = array())
    {
        //
        // ヘダーにある値。 伝票番号や日付けなど。
        //
        if(isset($formvars['warehouse_code']))
        {
            $this->warehouse_code = $formvars['warehouse_code'];
        }
        if(isset($formvars['year']))
        {
            $this->year = $formvars['year'];
        }
        if(isset($formvars['month']))
        {
            $this->month = $formvars['month'];
        }
        if(isset($formvars['date']))
        {
            $this->date = $formvars['date'];
        }
        if(isset($formvars['slip_no']))
        {
            $this->slip_no = $formvars['slip_no'];
        }
        if(isset($formvars['store_code']))
        {
            $this->store_code = $formvars['store_code'];
        }
        if(isset($formvars['store_section_code']))
        {
            $this->store_section_code = $formvars['store_section_code'];
        }

        //
        // 明細行にある値。
        //

        $do_save = false;

        for($i=0; $i<6; $i++)
        {
            $id = "commodity_code_$i";
            if(isset($formvars[$id]))
            {
                $this->commodity_code[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "act_flag_$i";
            if(isset($formvars[$id]))
            {
                $this->act_flag[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "amount_$i";
            if(isset($formvars[$id]))
            {
                $this->amount[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "unit_price_$i";
            if(isset($formvars[$id]))
            {
                $this->unit_price[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "total_price_$i";
            if(isset($formvars[$id]))
            {
                $this->total_price[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "memo_$i";
            if(isset($formvars[$id]))
            {
                $this->memo[$i] = $formvars[$id];
                $do_save = true;
            }
        }

        if($do_save)
        {
            $this->saveToSession();
        }
    }

    /**
     * display the entry form
     *
     * @param array $formvars the form variables
     */
    function displayForm($formvars = array()) {

        if(!isset($this->store_code) || strlen($this->store_code)==0)
        {
            $this->clearAllSession();
        }

        // assign the form vars
        $this->tpl->assign('year',$this->year);
        $this->tpl->assign('month',$this->month);
        $this->tpl->assign('date',$this->date);
        $this->tpl->assign('slip_no',sprintf("%07d",$this->slip_no));
        $this->tpl->assign('store_code', $this->store_code);
        $this->tpl->assign('store_name', $this->store_name);
        $this->tpl->assign('commodity_code', $this->commodity_code);
        $this->tpl->assign('commodity_name', $this->commodity_name);
        $this->tpl->assign('unit_name',  $this->unit_name);
        $this->tpl->assign('unit_price', $this->unit_price);
        $this->tpl->assign('total_price', $this->total_price);
        $this->tpl->assign('amount',         $this->amount);
        $this->tpl->assign('store_section_list', $this->getStoreSectionList($formvars));   // 店舗内部門リストを得る。
        $this->tpl->assign('act_flag_list',  $this->getActFlagList());              // 支払区分のリストを得る。
        $this->tpl->assign('memo', $this->memo);

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ShipmentForm.tpl');

        // ここで保存していることに注意。
        // 本当はdestrouctorで保存したい(?)
        $this->saveToSession();

    }

    /*
    *  セッションに保存。
    */
    function saveToSession()
    {
        $_SESSION['YEAR']    = $this->year;
        $_SESSION['MONTH']   = $this->month;
        $_SESSION['DATE']    = $this->date;
        $_SESSION['SLIP_NO'] = $this->slip_no;
        $_SESSION['STORE_CODE']    = $this->store_code;
        $_SESSION['STORE_NAME']    = $this->store_name;
        $_SESSION['STORE_SECTION_CODE']   = $this->store_section_code;
        $_SESSION['COMMODITY_CODE_ARRAY'] = $this->commodity_code;
        $_SESSION['COMMODITY_NAME_ARRAY'] = $this->commodity_name;
        $_SESSION['UNIT_NAME_ARRAY']      = $this->unit_name;
        $_SESSION['UNIT_PRICE_ARRAY']     = $this->unit_price;
        $_SESSION['TOTAL_PRICE_ARRAY']    = $this->total_price;
        $_SESSION['AMOUNT_ARRAY']         = $this->amount;
        $_SESSION['ACT_FLAG_ARRAY']   = $this->act_flag;
        $_SESSION['MEMO_ARRAY'] = $this->memo;
    }

    /*
    *  セッションから内部変数へ戻す。
    */
    function restoreFromSession()
    {
        // restore dates
        $this->year  = $_SESSION['YEAR'];
        $this->month = $_SESSION['MONTH'];
        $this->date  = $_SESSION['DATE'];
        $this->slip_no = $_SESSION['SLIP_NO'];

        // restore store information
        $this->store_code  = $_SESSION['STORE_CODE'];
        $this->store_name  = $_SESSION['STORE_NAME'];
        $this->store_section_code = $_SESSION['STORE_SECTION_CODE'];

        // restore commodity information
        $this->commodity_code = $_SESSION['COMMODITY_CODE_ARRAY'];
        $this->commodity_name = $_SESSION['COMMODITY_NAME_ARRAY'];
        $this->unit_name      = $_SESSION['UNIT_NAME_ARRAY'];
        $this->unit_price     = $_SESSION['UNIT_PRICE_ARRAY'];
        $this->total_price    = $_SESSION['TOTAL_PRICE_ARRAY'];
        $this->amount         = $_SESSION['AMOUNT_ARRAY'];
        $this->act_flag       = $_SESSION['ACT_FLAG_ARRAY'];
        $this->memo           = $_SESSION['MEMO_ARRAY'];
    }

    /*
    *  セッションクリアおよびそのコピー変数をクリア。
    */

    function clearAllSession()
    {
        unset($_SESSION['YEAR']);
        unset($_SESSION['MONTH']);
        unset($_SESSION['DATE'] );
        unset($_SESSION['SLIP_NO']);
        // unset($_SESSION['STORE_CODE']);
        // unset($_SESSION['STORE_NAME']);
        unset($_SESSION['STORE_SECTION_CODE']);
        unset($_SESSION['COMMODITY_CODE_ARRAY']);
        unset($_SESSION['COMMODITY_NAME_ARRAY']);
        unset($_SESSION['UNIT_NAME_ARRAY'])   ;
        unset($_SESSION['UNIT_PRICE_ARRAY'])  ;
        unset($_SESSION['TOTAL_PRICE_ARRAY']) ;
        unset($_SESSION['AMOUNT_ARRAY'])      ;
        unset($_SESSION['ACT_FLAG_ARRAY']);
        unset($_SESSION['MEMO_ARRAY']);

        $this->store_name    = null;
        $this->store_code    = null;
        $this->store_section_code = null;
        $this->commodity_name = array();
        $this->commodity_code = array();
        $this->unit_name      = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->act_flag       = array();
        $this->amount         = array();
        $this->slip_no        = $this->getNewSlipNo(); // null; // array();
        $this->memo           = array();

    }

    /*
    *   伝票番号の最大値＋１を得る。
    *   複数ユーザーで利用時には問題がある。
    *   振番用のテーブルを別に持つか？（要相談 & これはテンポラリインプリメント）
    */
    function getNewSlipNo()
    {
        $_query = "select max(slip_no)+1 from t_main where act_flag='5'";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            $this->slip_no = $this->sql->record[0];
            if($this->slip_no == null )
            {
                $this->slip_no = 1;
            }
        }
        return $this->slip_no;
    }

    /*
    *  商品の本部部門コード（資材内コード）を得る。
    */
    function getShipSectionCode($commodity_code)
    {
        $_query = "select ship_section_code from m_commodity where code='$commodity_code'";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            return $this->sql->record[0];
        }
        return "10";  // 仮勘定
    }

    /*
    *    トランザクションに追加。
    *
    *     1. autocommit はオフにする。
    *     2. きちんとcommitする。
    *
    */

    function addNewEntry($formvars = array())
    {
        if(strlen($formvars['month'])==1)
        {
            $formvars['month'] = '0' . $formvars['month'];
        }
        if(strlen($formvars['date'])==1)
        {
            $formvars['date'] = '0' . $formvars['date'];
        }

        // フォームのヘダーにある変数。
        $dt  = $formvars['year'] . "/" . $formvars['month'] . "/" . $formvars['date'];
        $store_code         = $formvars['store_code'];
        $store_section_code = $formvars['store_section_code'];

        if(OlutApp::checkDate($dt)==false)
        {
            $this->error = "日付けを正しく入力してください";
            return false;
        }
        
        $uid = $_SESSION['user_id'];

        //
        $this->sql->Autocommit(false);
        
        //
        if(!$this->sql->query("begin work;"))
        {
            $this->error = $this->sql->error;
            return false;
        }
        
        // lock whole table.
        if(!$this->sql->query("lock table t_main;"))
        {
            $this->error = $this->sql->error;
            return false;
        }
        
        // 伝票番号の再度取得。
        $slip_no = $this->getNewSlipNo();
        
        // １０レコードループ
        for($i=0; $i<10; $i++)
        {
            $commodity_code = $formvars["commodity_code_$i"];
            $act_flag = $formvars["act_flag_$i"];
            $memo     = $formvars["memo_$i"];
            $ship_section_code = $this->getShipSectionCode($commodity_code);

            //
            // 注意：　出荷倉庫は「資材部倉庫」に固定となる。(いいのか？）
            //

            //
            // 出荷の金額はマイナスで登録。
            //
            $total_price = $formvars["total_price_$i"] * (-1);
            $amount      = $formvars["amount_$i"] * (-1);
            $warehouse_code = $this->system_profile->shipment_warehouse_code; // shipment_warehouse_code;

            if(strlen($commodity_code) > 0){
                $_query  = "insert into t_main (";
                $_query .= " act_date,slip_no,line_no,act_flag,";
                $_query .= " com_code,warehouse_code,dest_code,store_sec_code,";
                $_query .= " ship_sec_code, amount, unit_price, total_price, memo, update_userid,updated) ";
                $_query .= " values ('$dt',$slip_no,$i,'$act_flag','" . $commodity_code . "',";
                $_query .= "'$warehouse_code',";             // 資材部倉庫固定 01 -> プロファイル値
                $_query .= "'$store_code',";
                $_query .= "'$store_section_code',";
                $_query .= "'$ship_section_code',";
                $_query .= "$amount,";
                $_query .= $formvars["unit_price_$i"] . ",";
                $_query .= "$total_price,";
                $_query .= "'$memo','$uid',now())";

                //print $_query;

                if($this->sql->query($_query)==false)
                {
                    // 現在のエラーを保存。
                    $this->error = $this->sql->error;

                    // エラーなので中断。
                    $this->sql->Rollback();
                    // 元のオートコミットに戻す。
                    $this->sql->AutoCommit(true);
                    return false;
                }
            }
        }
        $this->sql->Commit();

        // 元のオートコミットに戻す。
        $this->sql->AutoCommit(true);
        return true;
    }

    /*
    *  店舗コードの存在チェック。
    */
    function storeCodeExsists($code)
    {
        $_query = sprintf("select name from m_store where code='%s' and deleted is null", $code);
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            if( $this->sql->record != null)
            {
                // 店舗名をセット。
                $this->store_name = $this->sql->record[0];
                return true;
            }
        }
        return false;
    }

    /*
    *  商品コードの存在チェック。
    */
    function commodityCodeExsists($code,$index,&$formvars)
    {
        $_query = "select c.code,c.name,c.current_unit_price, u.name ";
        $_query .= " from m_commodity c, m_unit u where c.code='$code'";
        $_query .= " and c.unit_code=u.code";
        //print $_query;
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            if( $this->sql->record[0] > 0)
            {
                $this->commodity_name[$index] = $this->sql->record[1];

                // 単価だけは入力される可能性があるので、フォーム値に無い場合だけ、
                // マスターからの参照値をセットする。
                $id = "unit_price_$index";
                if(!isset($formvars[$id]) || strlen($formvars[$id]) == 0)
                {
                    $this->unit_price[$index] = $this->sql->record[2];
                    //
                    // 2005/09/28 fix. フォームの値にも戻す。"単価が入力されていません”エラー対策。
                    //
                    $formvars[$id] = $this->sql->record[2];
                }
                $this->unit_name[$index]      = $this->sql->record[3];
                return true;
            }
        }
        return false;
    }

    /*
    * 入力チェック？
    */
    function checkEntry(&$formvars)
    {
        //
        // ヘダーにある日付けをチェック。
        //
        // 1. フォーマットは正しいか？
        // 2. 入力可能なデータなのか。（２ヶ月前はだめ）
        //

        if(strlen($formvars['slip_no']) != 7)
        {
            $this->error = "伝票番号を正しく入力してください。";
            return false;
        }

        if(strlen($formvars['year']) != 4)
        {
            $this->error = "年を正しく入力してください。";
            return false;
        }
        if(strlen($formvars['month'])==1)
        {
            $formvars['month'] = '0' . $formvars['month'];
        }

        if(strlen($formvars['month']) != 2)
        {
            $this->error = "月を正しく入力してください。";
            return false;
        }

        if(strlen($formvars['date'])==1)
        {
            $formvars['date'] = '0' . $formvars['date'];
        }

        if(strlen($formvars['date']) != 2)
        {
            $this->error = "日を正しく入力してください。";
            return false;
        }

        if(checkdate($formvars['month'],$formvars['date'],$formvars['year'])==false)
        {
            $this->error = "日付けを正しく入力してください。";
            return false;
        }

        // 店舗コード入力チェック
        if(strlen($formvars['store_code']) != 5)
        {
            $this->error = "店舗コードが正しく入力されていません";
            return false;
        }

        // 店舗コード存在チェック。
        if(!$this->storeCodeExsists($formvars['store_code']))
        {
            $this->error = "店舗コードは登録されていません。";
            return false;
        }

        //
        // 棚卸以前のデータは入力できないのチェック
        //
        OlutApp::getCurrentProcessDate($this->sql, $tmp_year, $tmp_month);
        $tmp = $tmp_year . $tmp_month;
        
        if($tmp > $formvars['year'] . $formvars['month'] )
        {
            //
            $this->error = "すでに月次繰越済みの日付けが指定されています";
            return false;
        }

        // レコードループ
        for($i=$count=0; $i<6; $i++)
        {
            $commodity_code = $formvars["commodity_code_$i"];

            if(strlen($commodity_code) > 0){
                //
                //
                $count = $i+1;

                // 商品コード入力チェック。
                if(strlen($commodity_code) != 5)
                {
                    $this->error = "$count 行目の商品コードが正しく入力されていません";
                    return false;
                }
                // 商品コード存在チェック。
                if(!$this->commodityCodeExsists($commodity_code,$i,&$formvars))
                {
                    $this->error = "$count 行目の商品コードは登録されていません。";
                    return false;
                }

                // 数量入力チェック。
                $id = "amount_$i";
                if(strlen($formvars[$id]) == 0)
                {
                    $this->error = "$count 行目の数量が正しく入力されていません";
                    return false;
                }

                // 単価入力チェック。
                $id = "unit_price_$i";
                if(strlen($formvars[$id]) == 0)
                {
                    $this->error = "$count 行目の単価が正しく入力されていません";
                    return false;
                }

                // 価格入力チェック。
                $id = "total_price_$i";
                if(strlen($formvars[$id]) == 0)
                {
                    //
                    // 価格を計算して設定。小数点以下切捨て。
                    //
                    
                    // 以下は桁落ちするので、駄目。
                    // $tmp_price = $formvars["unit_price_$i"] * $formvars["amount_$i"];
                    // $this->total_price[$i] = floor($tmp_price);
                    
                    $this->total_price[$i] = bcmul($formvars["unit_price_$i"],$formvars["amount_$i"],0);
                    // 計算結果を戻す。
                    $formvars[$id] = $this->total_price[$i];
                }

            }
            else 
            {
                // 入力がキャンセルされた場合を考慮してクリア。
                $this->unit_price[$i] = '';
                $this->unit_name[$i]  = '';
                $this->commodity_name[$i] = '';
            }
        }

        if($count == 0)
        {
            $this->error = '明細が一行も入力されていません。';
            return false;
        }
        return true;
    }

    

    function referStore(&$formvars)
    {
        // 店舗コード存在チェック。
        if(!$this->storeCodeExsists($formvars['store_code']))
        {
            $this->error = "店舗コードは登録されていません。";
            return false;
        }
        return true;
    }

    /*
    *    店舗参照ボタン？
    *
    */
    function isStoreRef($formvars = array())
    {
        if(isset($formvars['store_ref']))
        {
            return true;
        }
        return false;
    }

    /*
    *    商品参照ボタン？
    *
    */
    function isCommodityRef($formvars = array())
    {
        for($i=0; $i<6; $i++)
        {
            $name = "commodity_ref_$i";
            if(isset($formvars[$name]))
            {
                return $i;
            }
        }
        return -1;
    }

    /*
    *  倉庫コードのリストを得る。
    */

    function getWarehouseList($formvars)
    {
        if(isset($formvars['warehouse_code']))
        {
            $selected_code = $formvars['warehouse_code'];
        }
        else
        {
            $selected_code = $_SESSION['warehouse_code'];
        }

        $_query = "select code,name from m_warehouse order by code";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
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
        }
        return $result;
    }

    /*
    *  店舗内部門のリストを得る。(ヘダーの部門選択用）
    *
    */

    function getStoreSectionList($formvars)
    {
        if(isset($formvars['store_section_code']))
        {
            $selected_code = $formvars['store_section_code'];
        }
        else
        {
            $selected_code = $_SESSION['store_section_code'];
        }

        // 店舗コードから店舗内部門のリストを得る。

        $store_code =$formvars['store_code'];
        if($store_code == null || strlen($store_code) == 0)
        {
            $store_code = $_SESSION['STORE_CODE'];
        }

        $_query = "select store_section_code from m_store_section ";
        $_query .= " where code='$store_code'";

        if($this->sql->query($_query,SQL_ALL))
        {
            if( $this->sql->record != null)
            {
                foreach($this->sql->record as $rec)
                {
                    $store_section[] = $rec[0];
                }
            }
        }

        $result = "";

        if($store_section == null)
        {
            $_query = "select code,name from m_store_division order by code";
        }
        else
        {
            $_query = "select code,name from m_store_division where ";
            $is_first = true;
            foreach($store_section as $ss)
            {
                if($is_first == true)
                {
                    $is_first = false;
                }
                else
                {
                    $_query .= " or ";
                }
                $_query .= "code='$ss' ";
            }
            $_query .= "order by code";
        }

        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            foreach($this->sql->record as $rec)
            {
                $code = trim($rec[0]);
                if(!strcmp($code,$selected_code))
                {
                    $result .= sprintf("<option value='%s' selected>",$code);
                }
                else
                {
                    $result .= sprintf("<option value='%s'>",$code);
                }
                $result .= $rec[1];
                $result .= "</option>";
            }
        }
        return $result;
    }

    /*
    *  支払区分のリストを得る。
    */
    function getActFlagList()
    {
        // 2005/8/23 減耗は削除しました。
        $act_flag = array(5 => "出庫", 6=>"サンプル出庫",7=>"内部出庫"); // ,9=>"減耗");

        for($i=0; $i<6; $i++)
        {
            foreach($act_flag as $k => $v)
            {
                if($k == $this->act_flag[$i])
                {
                    $sel = "selected";
                }
                else
                {
                    $sel = "";
                }
                $result[$i] .= "<option value=$k $sel>$v</option>";
            }
        }
        return $result;
    }
}
?>
