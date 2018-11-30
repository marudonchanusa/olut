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
*    入荷登録 - ArrivalOfGoodsClass.php
*
*   Release History:
*    2005/09/30  ver 1.00.00 Initial Release
*    2005/10/07  ver 1.00.01 1. Calicurate unit price when check button pressed.
*                            2. Do not set unit price from commodity master.
*    2005/10/13  ver 1.00.02 1. Changed getMaxSlipNo logic.
*                            2. Changed select tag value of modification mode.
*    2005/10/14  ver 1.00.03 1. Added act_date for update sql.
*                            2. Resumed logic of getMaxSlipNo.
*                            3. Removed logic of price "0" check.
*    2005/10/17  ver 1.00.04 1. Warehouse code was not correct when modification mode.
*                            2. Added table lock and new slip no get logic for new mode.
*    2005/10/18  ver 1.00.05 1. Clear vendor name and unit price when the line is back to blank.
*    2005/10/19  ver 1.00.06 1. Use process date for modify query screen.
*                            2. Changed array clear method in clearAllSession.
*    2005/10/26  ver 1.00.07 Added search,delete,next,prev button.
*    2005/11/30  ver 1.00.08 1.Added sort order of act_date,slip_no for finding slip by date.
*                            2.Decimal point of unit_price is set to 2.
*    2006/01/05  ver 1.00.09 getModifyData showed mixed sheet data within within the same date.
*    2006/01/20  ver 1.00.10 Memo was not able to display in search mode.
*
*
*/
require_once('OlutAppLib.php');

class ArrivalOfGoods extends OlutApp
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;

    // 以下は画面に表示するデータ。配列で１０個持つ。
    var $vendor_code = null;
    var $vendor_name = null;
    var $commodity_code = null;
    var $commodity_name = null;        // 明細ごとの商品名
    var $unit_name    = null;          // 明細ごとの単位
    var $unit_price   = null;          // 明細ごとの単価
    var $total_price  = null;          // 明細ごとの価格
    var $amount       = null;          // 明細ごとの数量
    var $payment_flag = null;          // 明細ごとの支払区分。
    var $act_flag = null;
    var $memo = null;
    var $year  = null;
    var $month = null;
    var $date  = null;
    var $slip_no = null;               // 伝票番号
    var $warehouse_code;

    // 修正選択用
    var $search_result_count = 0;
    var $search_result_slip_no;
    var $search_result_commodity_name;
    var $search_result_vendor_name;
    var $search_result_date;
    var $serach_result_arg;
    var $date_from;
    var $date_to;

    /**
     * class constructor
     */
    function ArrivalOfGoods()
    {
        // instantiate the sql object
        $this->sql =& new ArrivalOfGoods_SQL;

        // instantiate the template object
        $this->tpl =& new ArrivalOfGoods_Smarty;

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

        // 選択画面の値
        if(isset($formvars['date_from']))
        {
            $this->date_from = $formvars['date_from'];
            $do_save = true;
        }
        if(isset($formvars['date_to']))
        {
            $this->date_to = $formvars['date_to'];
            $do_save = true;
        }

        //
        // 明細行にある値。
        //

        $do_save = false;

        for($i=0; $i<10; $i++)
        {
            $id = "payment_flag_$i";
            if(isset($formvars[$id]))
            {
                $this->payment_flag[$i] = $formvars[$id];
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
                //
                if($this->act_flag[$i] == '2') // 値引き
                {
                    $this->amount[$i] = 0.0;
                }
                else
                {
                    $this->amount[$i] = $formvars[$id];
                }
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

            $id = "commodity_code_$i";
            if(isset($formvars[$id]))
            {
                $this->commodity_code[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "vendor_code_$i";
            if(isset($formvars[$id]))
            {
                $this->vendor_code[$i] = $formvars[$id];
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
     * display the vendor entry form
     *
     * @param array $formvars the form variables
     */
    function displayForm($formvars = array())
    {
        if(!isset($this->vendor_name))
        {
            $this->clearAllSession();
        }

        // assign the form vars
        $this->tpl->assign('year',$this->year);
        $this->tpl->assign('month',$this->month);
        $this->tpl->assign('date',$this->date);
        $this->tpl->assign('slip_no',sprintf("%07d",$this->slip_no));
        $this->tpl->assign('post',$formvars);
        $this->tpl->assign('vendor_code', $this->vendor_code);
        $this->tpl->assign('vendor_name', $this->vendor_name);
        $this->tpl->assign('commodity_code', $this->commodity_code);
        $this->tpl->assign('commodity_name', $this->commodity_name);
        $this->tpl->assign('unit_name',  $this->unit_name);
        $this->tpl->assign('unit_price', $this->unit_price);
        $this->tpl->assign('total_price', $this->total_price);
        $this->tpl->assign('amount', $this->amount);
        $this->tpl->assign('warehouse_list', $this->getWarehouseList($formvars));   // 倉庫コードのリストを得る。
        $this->tpl->assign('payment_flag_list', $this->getPaymentFlagList());       // 支払区分のリストを得る。
        $this->tpl->assign('act_flag_list',$this->getActFlagList());
        $this->tpl->assign('memo',$this->memo);

        if( $_SESSION['modify_mode'] == true )
        {
            $this->tpl->assign('readonly','readonly');
            $this->tpl->assign('modify_mode','1');              // メニューへ戻るのjavascriptに修正モードを知らせる必要があるので、

            if(count($this->commodity_code))
            {
                $this->tpl->assign('button_state','');
            }
            else
            {
                $this->tpl->assign('button_state','disabled');
            }
        }
        else
        {
            $this->tpl->assign('readonly','');
            $this->tpl->assign('modify_mode','0');
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
        $this->tpl->display('ArrivalOfGoodsForm.tpl');

        // ここで保存していることに注意。
        // 本当はdestrouctorで保存したい(?)
        $this->saveToSession();

    }

    function displaySelectionForm($formvars = array())
    {
        // assign the form vars
        if(strlen($this->date_from)==0 && strlen($this->date_to) == 0)
        {
            // 開始日は処理月設定から取得する必要がある。
            OlutApp::getCurrentProcessDate($this->sql,$year,$month);
            $this->date_from = sprintf("%04d%02d01",$year,$month);

            // 終了日はシステム・デイト
            $year = Date('Y');
            $month = Date('m');

            $last = OlutApp::GetLastDate($year,$month);
            $this->date_to = sprintf("%04d%02d%02d",$year,$month,$last);
        }

        $this->tpl->assign('date_from', $this->date_from);
        $this->tpl->assign('date_to', $this->date_to);
        $this->tpl->assign('count',$this->search_result_count);
        $this->tpl->assign('date', $this->search_result_date);
        $this->tpl->assign('slip_no',$this->search_result_slip_no);
        $this->tpl->assign('vendor_name', $this->search_result_vendor_name);
        $this->tpl->assign('commodity_name', $this->search_result_commodity_name);
        $this->tpl->assign('arg', $this->search_result_arg);

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ArrivalOfGoodsSelectionForm.tpl');
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
        $_SESSION['WAREHOUSE_CODE']       = $this->warehouse_code;

        $_SESSION['VENDOR_CODE_ARRAY']    = $this->vendor_code;
        $_SESSION['VENDOR_NAME_ARRAY']    = $this->vendor_name;
        $_SESSION['COMMODITY_CODE_ARRAY'] = $this->commodity_code;
        $_SESSION['COMMODITY_NAME_ARRAY'] = $this->commodity_name;
        $_SESSION['UNIT_NAME_ARRAY']      = $this->unit_name;
        $_SESSION['UNIT_PRICE_ARRAY']     = $this->unit_price;
        $_SESSION['TOTAL_PRICE_ARRAY']    = $this->total_price;
        $_SESSION['AMOUNT_ARRAY']         = $this->amount;
        $_SESSION['PAYMENT_FLAG_ARRAY']   = $this->payment_flag;
        $_SESSION['ACT_FLAG_ARRAY']       = $this->act_flag;
        $_SESSION['MEMO_ARRAY']           = $this->memo;

        $_SESSION['DATE_FROM'] = $this->date_from;
        $_SESSION['DATE_TO']   = $this->date_to;

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
        $this->warehouse_code = $_SESSION['WAREHOUSE_CODE'];

        // restore vendor information
        $this->vendor_code  = $_SESSION['VENDOR_CODE_ARRAY'];
        $this->vendor_name  = $_SESSION['VENDOR_NAME_ARRAY'];

        // restore commodity information
        $this->commodity_code = $_SESSION['COMMODITY_CODE_ARRAY'];
        $this->commodity_name = $_SESSION['COMMODITY_NAME_ARRAY'];
        $this->unit_name      = $_SESSION['UNIT_NAME_ARRAY'];
        $this->unit_price     = $_SESSION['UNIT_PRICE_ARRAY'];
        $this->total_price    = $_SESSION['TOTAL_PRICE_ARRAY'];
        $this->amount         = $_SESSION['AMOUNT_ARRAY'];
        $this->payment_flag   = $_SESSION['PAYMENT_FLAG_ARRAY'];
        $this->act_flag       = $_SESSION['ACT_FLAG_ARRAY'];
        $this->memo           = $_SESSION['MEMO_ARRAY'];

        $this->date_from = $_SESSION['DATE_FROM'];
        $this->date_to   = $_SESSION['DATE_TO'];
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

        $_SESSION['VENDOR_CODE_ARRAY'] = array();
        $_SESSION['VENDOR_NAME_ARRAY'] = array();
        $_SESSION['COMMODITY_CODE_ARRAY'] = array();
        $_SESSION['COMMODITY_NAME_ARRAY'] = array();
        $_SESSION['UNIT_NAME_ARRAY']      = array();
        $_SESSION['UNIT_PRICE_ARRAY']     = array();
        $_SESSION['TOTAL_PRICE_ARRAY']    = array();
        $_SESSION['AMOUNT_ARRAY']         = array();
        $_SESSION['PAYMENT_FLAG_ARRAY']   = array();
        $_SESSION['ACT_FLAG_ARRAY']       = array();
        $_SESSION['MEMO_FLAG_ARRAY']      = array();

        unset($_SESSION['DATE_FROM']);
        unset($_SESSION['DATE_TO']);

        $this->vendor_name    = array();
        $this->vendor_code    = array();
        $this->commodity_name = array();
        $this->commodity_code = array();
        $this->unit_name      = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->payment_flag   = array();
        $this->act_flag       = array();
        $this->memo           = array();
        $this->amount         = array();

        if ($_SESSION['modify_mode'])
        {
            // エラー表示の場合はそのままの伝票番号を残す。
            if(strlen($this->error) == 0)
            {
                // year,month,date,slip_noをセットして戻る。
                $this->getMaxSlipNo();
            }
        }
        else
        {
            $this->slip_no        = $this->getNewSlipNo(); // null; // array();
            // 修正モードでセッションに保存されているのが残ることがあった
            // ので、ここでシステムデートをセットする。 2005/10/19
            $this->year  = date('Y');
            $this->month = date('m');
            $this->date  = date('d');
        }
        $this->warehouse_code = '01';
    }

    /*
    *   伝票番号の最大値＋１を得る。
    *   複数ユーザーで利用時には問題がある。
    *   振番用のテーブルを別に持つか？（要相談 & これはテンポラリインプリメント）@later
    */
    //function getNewSlipNo()
    //{
    //    $dt = date('Y/m/d');
    //    $_query = "select max(slip_no)+1 from t_main where act_date='$dt' and act_flag=1";
    //    if($this->sql->query($_query,SQL_INIT)==true)
    //    {
    //        //
    //        $this->slip_no = $this->sql->record[0];
    //        if($this->slip_no == null )
    //        {
    //            $this->slip_no = 1000001;
    //        }
    //    }
    //    return $this->slip_no;
    //}

    // 2005/10/14 古いロジックに戻した。
    function getNewSlipNo()
    {
        $_query = "select max(slip_no)+1 from t_main where act_flag=1";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            $this->slip_no = $this->sql->record[0];
            if($this->slip_no == null )
            {
                $this->slip_no = 1000001;
            }
        }
        return $this->slip_no;

    }

    /*
     *  sets year,month,date, and slip_no
     */
    function getMaxSlipNo()
    {
        $_query = "select max(slip_no) from t_main where act_flag=1";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            $this->slip_no = $this->sql->record[0];
            if($this->slip_no == null )
            {
                $this->slip_no = 1000001;
            }
        }

        $_query = "select act_date from t_main where act_flag=1 and slip_no=$this->slip_no";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            if($this->sql->record != null)
            {
                if(preg_match('/(\d*)-(\d*)-(\d*)/',$this->sql->record[0],$matches))
                {
                    $this->year  = $matches[1];
                    $this->month = $matches[2];
                    $this->date   = $matches[3];
                }
            }
        }
    }
    /*
    *  商品の本部部門コード（資材内コード）を得る。
    */
    function getShipSectionCode($commodity_code)
    {
        $_query = sprintf("select ship_section_code from m_commodity where code='%s'", $commodity_code);
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            return $this->sql->record[0];
        }
        return "10";  // 仮勘定
    }

    /*
    *    トランザクションに追加または修正。
    *
    *     1. autocommit はオフにする。
    *     2. きちんとcommitする。
    *
    */

    function saveEntry($formvars = array())
    {
        // フォームのヘダーにある変数。
        if(strlen($formvars['month'])==1)
        {
            $formvars['month'] = '0' . $formvars['month'];
        }
        if(strlen($formvars['date'])==1)
        {
            $formvars['date'] = '0' . $formvars['date'];
        }

        $dt  = $formvars['year'] . "/" . $formvars['month'] . "/" . $formvars['date'];

        if( $_SESSION['modify_mode'] != true )
        {
            // 新規
            $warehouse_code = $formvars['warehouse_code'];
            if(strlen($wareshoue_code)==0)     // fix 2005/10/17.
            {
                $warehouse_code = $_SESSION['WAREHOUSE_CODE'];
            }
        }
        else
        {
            // 修正モード
            $warehouse_code = $this->warehouse_code;
        }

        $slip_no        = $formvars['slip_no'];

        if(OlutApp::checkDate($dt)==false)
        {
            $this->error = "日付けが正しくありません";
            return false;
        }

        // ユーザーID
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

        if( $_SESSION['modify_mode'] != true )
        {
            // 再度伝票番号を得る。
            $slip_no = $this->getNewSlipNo();
        }

        // 2005/10/08.
        // すでに伝票が存在する場合には最初に全て削除する。
        if($this->alreadyThere($dt,$slip_no,0))
        {
            $_query = "delete from t_main";
            $_query .= " where slip_no=$slip_no and act_date='$dt' and deleted is null";
            $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')";

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

        // １０レコードループ
        // $i は画面の行。途中抜けている場合もあるので、$line_noをカウントアップして使う。

        for($i=$line_no=0; $i<10; $i++)
        {
            //
            $commodity_code = $formvars["commodity_code_$i"];

            if(strlen($commodity_code) > 0)
            {
                $ship_sec_code  = $this->getShipSectionCode($commodity_code);
                $memo           = $formvars["memo_$i"];
                $act_flag       = $formvars["act_flag_$i"];
                $payment_flag   = $formvars["payment_flag_$i"];
                $vendor_code    = $formvars["vendor_code_$i"];
                $amount         = $formvars["amount_$i"];
                $unit_price     = ereg_replace(',','',$this->unit_price[$i]);   //
                $total_price    = $formvars["total_price_$i"];

                if($this->alreadyThere($dt,$slip_no,$i))
                {
                    // 修正モード。
                    $_query  = "update t_main ";
                    $_query .= " set act_date='$dt',";
                    $_query .= " slip_no=$slip_no,line_no=$line_no,";
                    $_query .= " com_code='$commodity_code',";
                    // $_query .= " warehouse_code='$warehouse_code',";   倉庫コードは必要ない。2005/10/17.
                    $_query .= " orig_code='$vendor_code',";
                    $_query .= " act_flag='$act_flag',";
                    $_query .= " amount=$amount,";
                    $_query .= " unit_price=$unit_price,";
                    $_query .= " total_price=$total_price,";
                    $_query .= " payment_flag='$payment_flag',";
                    $_query .= " memo='$memo',";
                    $_query .= " ship_sec_code='$ship_sec_code',";
                    $_query .= " update_userid='$uid',";
                    $_query .= " updated=now()";
                    $_query .= " where slip_no=$slip_no and line_no=$i act_date='$dt' and deleted is null";  // added act_date 2005/10/15.
                    $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')";

                    // print $_query;
                }
                else
                {
                    // 新規追加。
                    $_query  = "insert into t_main (";
                    $_query .= " act_date,slip_no,line_no,";
                    $_query .= " com_code,warehouse_code,orig_code,act_flag,";
                    $_query .= " amount, unit_price, total_price, payment_flag,memo,ship_sec_code, update_userid, updated) ";
                    $_query .= " values ('$dt',$slip_no,$line_no,";
                    $_query .= "'$commodity_code',";
                    $_query .= "'$warehouse_code',";
                    $_query .= "'$vendor_code',";
                    $_query .= "'$act_flag',";
                    $_query .= "$amount,";
                    $_query .= "$unit_price,";
                    $_query .= "$total_price,";
                    $_query .= "'$payment_flag',";
                    $_query .= "'$memo',";
                    $_query .= "'$ship_sec_code','$uid',now())";

                    // print $_query;
                }

                $line_no++;

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

    function alreadyThere($dt,$slip_no,$line_no)
    {
        $_query = "select count(*) from t_main";
        $_query .= " where act_date='$dt' and slip_no=$slip_no and line_no=$line_no and deleted is null";
        $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')";

        if($this->sql->query($_query,SQL_INIT))
        {
            if($this->sql->record[0] > 0)
            {
                return true;
            }
        }
        return false;
    }

    /*
    *  取引先コードの存在チェック。
    */
    function vendorCodeExsists($code,$index)
    {
        $_query = sprintf("select code,name from m_vendor where code='%s'", $code);
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            // コードが変更になる場合もあるので取引先名は再度設定。
            if( $this->sql->record[0] > 0)
            {
                $this->vendor_name[$index] = $this->sql->record[1];
                return true;
            }
        }
        return false;
    }

    /*
    *  商品コードの存在チェック。
    */
    function commodityCodeExsists($code,$index,$formvars)
    {
        $_query = "select c.code,c.name,c.current_unit_price,u.name from m_commodity c, m_unit u where c.code='$code' and u.code=c.unit_code";

        // print $_query;

        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            if( $this->sql->record[0] > 0)
            {
                $this->commodity_name[$index] = $this->sql->record[1];

                $id = "unit_price_$index";
                if(!isset($formvars[$id])  || strlen($formvars[$id]) == 0 )
                {
                    // 2005/10/06
                    // 商品マスターの単価は、出荷価格なので、ここではセットしないよう、変更。
                    //
                    // $this->unit_price[$index] = $this->sql->record[2];
                    // $formvars[$id]  = $this->sql->record[2];             // フォームデータにも戻す。
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

        // １０レコードループ
        for($i=$count=0; $i<10; $i++)
        {
            $commodity_code = $formvars["commodity_code_$i"];

            if(strlen($commodity_code) > 0){
                //
                //
                $count = $i+1;
                //
                $act_flag = $formvars["act_flag_$i"];

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

                // 取引先コード入力チェック
                $id = "vendor_code_$i";
                if(strlen($formvars[$id]) != 5)
                {
                    $this->error = "$count 行目の取引先コードが正しく入力されていません";
                    return false;
                }

                // 取引先コード存在チェック。
                if(!$this->vendorCodeExsists($formvars[$id],$i))
                {
                    $this->error = "$count 行目の取引先コードは登録されていません。";
                    return false;
                }

                // 数量入力チェック。
                if($act_flag != '2')   // 値引きはノーチェックとする。
                {
                    $id = "amount_$i";
                    if(strlen($formvars[$id]) == 0 || $formvars[$id] == '0')
                    {
                        $this->error = "$count 行目の数量が正しく入力されていません";
                        return false;
                    }
                    $this->amount[$i] = $formvars[$id];
                }

                //
                // 2005/10/06
                // 単価が入力されていない場合は、計算で単価を求めることにする。
                //

                // なので、価格が入力されていないのはNG.
                $id = "total_price_$i";
                // if(strlen($formvars[$id]) == 0 || $formvars[$id] == "0")

                // 2005/10/14 金額ゼロを許す。
                //
                if(strlen($formvars[$id]) == 0)
                {
                    $this->error = "$count 行目の 価格を入力してください。";
                    return false;
                }
                $this->total_price[$i] = $formvars[$id];

                // 2005/10/08.
                // 常に単価は計算するように変更。
                //

                // 単価入力チェック。
                // $id = "unit_price_$i";
                // if(strlen($formvars[$id]) == 0)
                //{
                if($this->amount[$i] != 0)
                {
                    //
                    // 単価入力が無いので、逆算する。
                    // 小数点以下は切り捨て。
                    //
                    // ==> 2005/11/30 小数点以下２桁に変更。//さらに、切捨ても小数点以下２桁に変更。
                    //
                    // 古いコードは以下のとおり：
                    // $this->unit_price[$i] = number_format(floor($this->total_price[$i] / $this->amount[$i]),2);
                    //
                    
                    $tmp_val = $this->total_price[$i] / $this->amount[$i] * 100;
                    $tmp_val = floor($tmp_val);
                    $tmp_val /= 100;
                    //
                    //
                    $this->unit_price[$i] = number_format($tmp_val,2);
                }
                // }

                //
                // 値引きのときは金額をマイナスで入力してもらう。
                //

                if($act_flag == '2')
                {
                    if($formvars["total_price_$i"] > 0)
                    {
                        $this->error = "$count 行は値引きなので金額をマイナスとしてください";
                        return false;
                    }
                    if($this->$formvars["amount_$i"] != 0)
                    {
                        $this->error = "$count 行は値引きなので数量をゼロとしてください";
                        return false;
                    }
                }
            }
            else
            {
                // 商品コードの入力が無いので、表示する商品名もクリアする。
                $this->commodity_name[$i] = '';
                $this->unit_name[$i]      = '';
                $this->unit_price[$i]     = '';     // 2005/10/18 added.
                $this->vendor_name[$i]    = '';     // 2005/10/18 added.
            }
        }

        if($count == 0)
        {
            $this->error = '明細が一行も入力されていません。';
            return false;
        }
        return true;
    }

    /*
    *    取引先参照ボタン？
    *
    */
    function isVendorRef($formvars = array())
    {
        for($i=0; $i<10; $i++)
        {
            $name = "vendor_ref_$i";
            if(isset($formvars[$name]))
            {
                return $i;
            }
        }
        return -1;
    }

    /*
    *    商品参照ボタン？
    *
    */
    function isCommodityRef($formvars = array())
    {
        for($i=0; $i<10; $i++)
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

        if($selected_code == null)
        {
            $selected_code = $this->warehouse_code;
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
    *  支払区分のリストを得る。
    */
    function getPaymentFlagList()
    {
        for($i=0; $i<10; $i++)
        {
            if($this->payment_flag[$i] == '1')
            {
                $selected0 = "";
                $selected1 = "selected";
            }
            else
            {
                $selected0 = "selected";
                $selected1 = "";
            }
            $result[$i] .= "<option value=0 $selected0>買掛</option>";
            $result[$i] .= "<option value=1 $selected1>現金</option>";
        }
        return $result;
    }

    /*
    *  入庫フラグのリストを得る。
    */
    function getActFlagList()
    {
        $afl = array(1=>'入庫',2=>'値引入庫',3=>'内部入庫');
        $result = '';
        for($i=0; $i<10; $i++)
        {
            foreach($afl as $key=>$value)
            {
                if($this->act_flag[$i] == $key)
                {
                    $sel = "selected";
                }
                else
                {
                    $sel = '';
                }
                $result[$i] .= "<option value=$key $sel>$value</option>";
            }
        }
        return $result;
    }

    function getTargetSlipNo($formvars)
    {
        if(strlen($formvars['slip_no'])==0)
        {
            $this->error = "伝票番号を入力してください";
            return false;
        }
        else
        {
            $this->act_date  = sprintf("%04d/%02d/%02d",$formvars['year'], $formvars['month'], $formvars['date']);
            $this->slip_no = $formvars['slip_no'];
        }
        return true;
    }
    
    function getTargetSlipNoForFind($formvars)
    {
        if(strlen($formvars['slip_no'])>0)
        {
            $this->slip_no = $formvars['slip_no'];
        }
        if(strlen($formvars['year']) > 0)
        {
            $this->act_date  = sprintf("%04d/%02d/%02d",$formvars['year'], $formvars['month'], $formvars['date']);
        }
        return true;
    }
    /*
    *   修正データを読み込む。
    */
    function getModifyData()
    {
        $_query  = "select t.act_date,t.warehouse_code, t.line_no,t.payment_flag,t.act_flag,";
        $_query .= " t.orig_code,v.name,c.unit_code,u.name,";
        $_query .= " t.com_code, c.name,t.unit_price, t.total_price, t.amount, t.slip_no,t.memo";
        $_query .= " from t_main t, m_commodity c, m_vendor v, m_unit u";
        $_query .= " where ";
        $_query .= "  t.act_date='$this->act_date'";
        
        if( strlen($this->slip_no) > 0 )   // 2005/10/28
        {        
            $_query .= " and  t.slip_no='$this->slip_no' ";
        }
        $_query .= "  and t.com_code=c.code and v.code=t.orig_code";
        $_query .= "  and c.unit_code=u.code and t.deleted is null";
        $_query .= " order by t.act_date, t.slip_no, t.line_no";       // act_date,slip_no を追加。2005/11/30

        //print $_query;

        if(!$this->sql->query($_query,SQL_ALL))
        {
            $this->error = $this->sql->error;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = "指定された伝票は存在しません";
            return false;
        }

        $header_is_set = false;

        /// added to clear array. 2005/10/19.
        $this->payment_flag   = array();
        $this->act_flag       = array();
        $this->vendor_code    = array();
        $this->vendor_name    = array();
        $this->unit_code      = array();
        $this->unit_name      = array();
        $this->commodity_code = array();
        $this->commodity_name = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->amount         = array();
        $this->memo           = array();   // 2006/1/20 added.

        foreach($this->sql->record as $rec)
        {
            if(!$header_is_set)
            {
                // YYYY-MM-DD
                $this->year = substr($rec[0],0,4);
                $this->month = substr($rec[0],5,2);
                $this->date  = substr($rec[0],8,2);
                $this->warehouse_code = $rec[1];
                $header_is_set = true;
                $this->slip_no = $rec[14];
            }
            
            // 2006/1/5 added below.
            if($this->slip_no != $rec[14])
            {
                break;
            }

            // base is zero.
            $index = $rec[2];       // line_no
            $this->payment_flag[$index]   = $rec[3];
            $this->act_flag[$index]       = $rec[4];
            $this->vendor_code[$index]    = $rec[5];
            $this->vendor_name[$index]    = $rec[6];
            $this->unit_code[$index]      = $rec[7];
            $this->unit_name[$index]      = $rec[8];
            $this->commodity_code[$index] = $rec[9];
            $this->commodity_name[$index] = $rec[10];
            $this->unit_price[$index]     = number_format($rec[11],2);  // 2005/11/30 小数点以下２桁に変更。
            $this->total_price[$index]    = $rec[12];
            $this->amount[$index]         = $rec[13];
            $this->memo[$index]           = $rec[15];     // 2006/1/20 added.
            //
        }

        // 以下がセットされていないなら、データが無かった。
        if($header_is_set == false)
        {
            $this->error = "データがありません";
            return false;
        }
        return true;

    }

    function delete()
    {
        $_query  = "delete from t_main ";
        $_query .= " where slip_no=$this->slip_no and act_date='$this->act_date'";
        $_query .= "  and (act_flag='1' or act_flag='2' or act_flag='3')";
        $_query .= "  and deleted is null";

        // print $_query;

        if(!$this->sql->query($_query,SQL_NONE))
        {
            $this->error = $this->sql->error;
            return false;
        }
        return true;
    }
    /*
    *  伝票の検索
    */

    function find()
    {
        $_query = "select t.act_date,t.slip_no,c.name,v.name ";
        $_query .= " from t_main t, m_commodity c, m_vendor v";
        $_query .= " where t.orig_code = v.code and t.line_no=0 and ";
        $_query .= " t.com_code=c.code and (t.act_flag='1' or t.act_flag='3')";
        $_query .= " and t.deleted is null";
        
        $_query .= " and act_date='$this->act_date'";
        
        if(strlen($this->slip_no) > 0)
        {
            $_query .= " and slip_no='$this->slip_no'";
        }
        $_query .= " order by t.slip_no, t.line_no";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL) && $this->sql->record != null)
        {
            $i = 0;
            foreach($this->sql->record as $rec)
            {
                $this->search_result_date[$i]    = ereg_replace("-","/",$rec[0]);
                $this->search_result_slip_no[$i] = sprintf("%07d",$rec[1]);
                $this->search_result_commodity_name[$i] = $rec[2];
                $this->search_result_vendor_name[$i] = $rec[3];

                // ここでselectのvalueを作ってしまいます。2005/10/13
                $this->search_result_arg[$i] = $rec[0] . ',' . $this->search_result_slip_no[$i];
                $i++;
            }
            $this->search_result_count = $i;
        }

        if($this->search_result_count == 0)
        {
            $this->error = "指定された伝票は存在しません";
            return false;
        }

        return true;
    }

    function prev()
    {
        $_query  = "select t.act_date,t.warehouse_code, t.line_no,t.payment_flag,t.act_flag,";
        $_query .= " t.orig_code,v.name,c.unit_code,u.name,";
        $_query .= " t.com_code, c.name,t.unit_price, t.total_price, t.amount, t.slip_no, t.memo";
        $_query .= " from t_main t, m_commodity c, m_vendor v, m_unit u";
        $_query .= " where t.slip_no<'$this->slip_no'";
        $_query .= "  and act_date='$this->act_date'";
        $_query .= "  and t.com_code=c.code and v.code=t.orig_code";
        $_query .= "  and c.unit_code=u.code and t.deleted is null";
        $_query .= " order by t.slip_no desc, t.act_date desc, t.line_no limit 10";

        // print $_query;

        if(!$this->sql->query($_query,SQL_ALL))
        {
            $this->error = $this->sql->error;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = "指定された伝票は存在しません";
            return false;
        }

        $header_is_set = false;

        /// added to clear array. 2005/10/19.
        $this->payment_flag   = array();
        $this->act_flag       = array();
        $this->vendor_code    = array();
        $this->vendor_name    = array();
        $this->unit_code      = array();
        $this->unit_name      = array();
        $this->commodity_code = array();
        $this->commodity_name = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->amount         = array();
        $this->memo           = array();

        foreach($this->sql->record as $rec)
        {
            if(!$header_is_set)
            {
                // YYYY-MM-DD
                $this->year = substr($rec[0],0,4);
                $this->month = substr($rec[0],5,2);
                $this->date  = substr($rec[0],8,2);
                $this->warehouse_code = $rec[1];
                $this->slip_no        = $rec[14];
                $header_is_set = true;
            }

            // base is zero.
            $index = $rec[2];       // line_no

            if($this->slip_no == $rec[14])
            {
                $this->payment_flag[$index]   = $rec[3];
                $this->act_flag[$index]       = $rec[4];
                $this->vendor_code[$index]    = $rec[5];
                $this->vendor_name[$index]    = $rec[6];
                $this->unit_code[$index]      = $rec[7];
                $this->unit_name[$index]      = $rec[8];
                $this->commodity_code[$index] = $rec[9];
                $this->commodity_name[$index] = $rec[10];
                $this->unit_price[$index]     = number_format($rec[11],2);   // 小数点以下２桁に変更 2005/11/30
                $this->total_price[$index]    = $rec[12];
                $this->amount[$index]         = $rec[13];
                $this->memo[$index]           = $rec[15];   // 2006/1/20.
            }
            //
        }

        // 以下がセットされていないなら、データが無かった。
        if($header_is_set == false)
        {
            $this->error = "データがありません";
            return false;
        }
        return true;
    }

    function next()
    {
        $_query  = "select t.act_date,t.warehouse_code, t.line_no,t.payment_flag,t.act_flag,";
        $_query .= " t.orig_code,v.name,c.unit_code,u.name,";
        $_query .= " t.com_code, c.name,t.unit_price, t.total_price, t.amount, t.slip_no, t.memo";
        $_query .= " from t_main t, m_commodity c, m_vendor v, m_unit u";
        $_query .= " where t.slip_no>'$this->slip_no'";
        $_query .= "  and act_date='$this->act_date'";        
        $_query .= "  and t.com_code=c.code and v.code=t.orig_code";
        $_query .= "  and c.unit_code=u.code and t.deleted is null";
        $_query .= " order by t.slip_no asc, t.act_date desc, t.line_no limit 10";

        if(!$this->sql->query($_query,SQL_ALL))
        {
            $this->error = $this->sql->error;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = "指定された伝票は存在しません";
            return false;
        }

        $header_is_set = false;

        /// added to clear array. 2005/10/19.
        $this->payment_flag   = array();
        $this->act_flag       = array();
        $this->vendor_code    = array();
        $this->vendor_name    = array();
        $this->unit_code      = array();
        $this->unit_name      = array();
        $this->commodity_code = array();
        $this->commodity_name = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->amount         = array();
        $this->memo           = array();

        foreach($this->sql->record as $rec)
        {
            if(!$header_is_set)
            {
                // YYYY-MM-DD
                $this->year = substr($rec[0],0,4);
                $this->month = substr($rec[0],5,2);
                $this->date  = substr($rec[0],8,2);
                $this->warehouse_code = $rec[1];
                $this->slip_no        = $rec[14];
                $header_is_set = true;
            }

            // base is zero.
            $index = $rec[2];       // line_no

            if($this->slip_no == $rec[14])
            {
                $this->payment_flag[$index]   = $rec[3];
                $this->act_flag[$index]       = $rec[4];
                $this->vendor_code[$index]    = $rec[5];
                $this->vendor_name[$index]    = $rec[6];
                $this->unit_code[$index]      = $rec[7];
                $this->unit_name[$index]      = $rec[8];
                $this->commodity_code[$index] = $rec[9];
                $this->commodity_name[$index] = $rec[10];
                $this->unit_price[$index]     = number_format($rec[11],2);  // 小数点以下２桁に変更 2005/11/30.
                $this->total_price[$index]    = $rec[12];
                $this->amount[$index]         = $rec[13];
                $this->memo[$index]           = $rec[15];   // 2006/1/20
            }
            //
        }

        // 以下がセットされていないなら、データが無かった。
        if($header_is_set == false)
        {
            $this->error = "データがありません";
            return false;
        }
        return true;
    }

}
?>
