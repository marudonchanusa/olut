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
*    入荷報告書 -  ReportOfArrivalOfGoodsClass.php
*
*   Release History:
*    2005/09/30  ver 1.00.00 Initial Release
*    2005/10/18  ver 1.00.01 Added "Real" grand total.
*
*/

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
require_once('OlutAppLib.php');

// smarty configuration
class ReportOfArrivalOfGoods_Smarty extends Smarty
{
    function ReportOfArrivalOfGoods_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ReportOfArrivalOfGoods_SQL extends SQL
{
    function ReportOfArrivalOfGoods_SQL()
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

/*
*      印刷クラス。
*
*/

class ReportOfArrivalOfGoods extends OlutApp
{
    var $left_margin = 10;
    var $right_margin = 10;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = 250;            // paper size in portlait.(not landscape)
    var $paper_height = 353;            // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 25;     // １行に印字するデータの行数。
    var $total;                         // 仕入れの合計。
    var $vendor_total;                  // 取引先ごとの合計。
    var $grand_total;                   // 総合計
    var $current_vendor;                // 現在印刷中の業者名を保持。業者が替わったら合計を印字するのに必要。
    var $current_date;                  // 現在印刷中の日付けを保持。日が変わったら小計を印字する。
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $page_no = 1;

    // ctor
    function ReportOfArrivalOfGoods()
    {
        $this->tpl =& new ReportOfArrivalOfGoods_Smarty;
        $this->sql =& new ReportOfArrivalOfGoods_SQL;

        // 項目印字位置設定。
        $this->x_pos = array(10,80,110,190,220,250,280,310);
    }

    /*
    *  印刷範囲入力画面のレンダリング
    */

    function renderScreen($formvars)
    {
        if(isset($formvars['target_year']))
        {
            $this->target_year = $formvars['target_year'];
            $this->target_month = $formvars['target_month'];
        }
        else 
        {
            $this->target_month = OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
        }

        //
        $this->tpl->assign('target_year_list',OlutApp::getTargetYearList($this->target_year));
        $this->tpl->assign('target_month_list', OlutApp::getTargetMonthList($this->target_month));

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ReportOfArrivalOfGoodsForm.tpl');
    }

    /*
    *  印刷関数
    *
    */
    function printOut($formvars)
    {
        //
        // B4の紙サイズを指定しています。指定はポートレート。
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);

        $this->pdf->AddPage('L');
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->printHeader($formvars);
        if($this->printData($formvars)==true)
        {
            $this->pdf->Output();
        }
        else
        {
            $this->renderScreen($formvars);
        }
    }

    /*
    *  各ページのヘダーを印刷
    */

    function printHeader($formvars)
    {
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        if(isset($formvars['target_year']))
        {
            $this->target_year  = $formvars['target_year'];
            $this->target_month = $formvars['target_month'];
        }
        else 
        {
            $this->target_year = date('Y');
            $this->target_month = date('m');
        }

        $year_from  = $this->target_year;
        $month_from = $this->target_month;
        $date_from  = "01";

        $year_to  = $this->target_year;
        $month_to = $this->target_month;
        $date_to  = OlutApp::getLastDate($this->target_year,$this->target_month);

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        //
        // 注意：
        //  $date_toの後に倍角スペースだと$date_toが表示されない。
        //  phpのバグだと思う。半角のスペースを入れて対応。~も表示されないので - (ハイフン）とした。
        //
        $this->pdf->Cell(0,9,"$year_from/$month_from/$date_from - $year_to/$month_to/$date_to 入荷報告書",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no ",'',1,'R');
        $this->page_no++;
        //
        $this->current_y = 15;

        // 仕入先　　日　付　　　　商品名　　　　数量　　単価　金額　入庫倉庫

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"仕入先");
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,"日　付");
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"商品名");
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,"数量");
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,"単価");
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,"金額");
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,"入庫倉庫");

        // 線を引く
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  データ問い合わせ＆明細行印刷。
    */

    function printData($formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];

        $year_from  = $this->target_year;
        $month_from = $this->target_month;
        $date_from  = "01";

        $year_to  = $this->target_year;
        $month_to = $this->target_month;
        $date_to  = OlutApp::getLastDate($this->target_year,$this->target_month);


        $date_from = "$year_from/$month_from/$date_from";
        $date_to   = "$year_to/$month_to/$date_to";

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        $_query = "select v.name, t.act_date, com.name, t.amount, ";
        $_query .= "t.unit_price,t.total_price,w.name, u.name ";
        $_query .= " from t_main t, m_shipment_section s, m_commodity com, m_vendor v, m_warehouse w, m_unit u ";
        $_query .= " where t.orig_code >= $code_from and t.orig_code <= $code_to and s.code=t.ship_sec_code ";
        $_query .= " and com.code=t.com_code and v.code=t.orig_code ";
        $_query .= " and w.code=t.warehouse_code ";
        $_query .= " and u.code=com.unit_code";
        $_query .= " and (t.act_flag='1' or t.act_flag='2' or t.act_flag='3')";

        if(isset($date_from) && strlen($date_from))
        {
            $_query .= " and t.act_date >= '$date_from'";
        }
        if(isset($date_to) && strlen($date_to))
        {
            $_query .= " and t.act_date <= '$date_to'";
        }

        $_query .= " order by t.orig_code, t.act_date, t.warehouse_code";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->total        = 0;
            $this->grand_total  = 0;
            $this->vendor_total = 0;
            $this->lines        = 0;
            $this->current_y    = 30;
            // ループする。
            foreach($this->sql->record as $rec)
            {
                // ここで業者がセットされてないとすれば、全くの最初。
                if(!isset($this->current_vendor))
                {
                    $this->current_vendor = $rec[0];
                    $this->current_date   = $rec[1];
                    $print_vendor = true;           // 最初なので業者を印字する。
                }
                else
                {
                    if(strcmp($this->current_date,$rec[1]))
                    {
                        // トータル印字1行になるのだが印字できるか？
                        $this->checkPageChange(1,$formvars,&$print_vendor);

                        // 小計印字。
                        $this->printTotal();
                        $this->current_date = $rec[1];
                    }

                    // 業者が変更された場合にはトータルを印字します。
                    if(strcmp($this->current_vendor,$rec[0]))
                    {
                        // トータル印字。２行になるのだが印字できるか？
                        $this->checkPageChange(2,$formvars,&$print_vendor);

                        // トータル印字。
                        $this->printVendorTotal();

                        // 現在の業者として保持。
                        $this->current_vendor = $rec[0];
                        $print_vendor = true;           // 業者が替わったので印刷する。

                    }
                    else
                    {
                        $print_vendor = false;          // 同じ業者なので印刷しない。
                    }
                }

                // 明細行を印字する。
                $this->checkPageChange(1,$formvars,&$print_vendor);
                $this->printLine($rec,$print_vendor);
            }

            // ループを抜けたらそれまでの商品分のトータルを印字。
            $this->checkPageChange(1,$formvars,&$print_vendor);
            $this->printTotal();
            $this->checkPageChange(2,$formvars,&$print_vendor);
            $this->printVendorTotal();
            
            // 総合計
            $this->checkPageChange(2,$formvars,&$print_vendor);
            $this->printGrandTotal();

        }
        return true;
    }

    /*
    *  ページ替えのチェックと実行。
    *  linesを初期設定したりインクリメントするので注意。
    *
    */

    function checkPageChange($lines_to_add,$formvars,&$print_vendor)
    {
        if(($this->lines+$line_to_add) >= $this->num_of_item_per_page)
        {
            $this->pdf->AddPage('L');
            $this->printHeader($formvars);
            $this->lines = 0;
            $this->current_y = 30;
            $print_vendor = true;       // ページが替わったので取引先名は印字する。
        }
        else {
            $this->lines += $lines_to_add;
        }
    }

    /*
    *  小計を印字します。(その日のトータル。仕様確認済：2005/8/9)
    */

    function printTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"*小計*");

        //
        //  仕入額のトータル。
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,10)); //  仕入額


        //
        //  支払額トータル
        //
        //$this->pdf->SetXY($this->x_pos[7],$this->current_y);
        //$this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  仕入額

        // トータルをリセット。
        $this->total = 0;
        //$this->grand_total = 0;

        // １行分進めていることに注意。
        $this->current_y += $this->line_height;
    }

    /*
    *  合計を印字します。（取引先ごとのトータル）
    */

    function printVendorTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"*合計*");

        //
        //  仕入額のトータル。
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->vendor_total,10)); //  仕入額


        //
        //  支払額トータル
        //
        //$this->pdf->SetXY($this->x_pos[7],$this->current_y);
        //$this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  仕入額

        // トータルをリセット。
        $this->total = 0;
        $this->vendor_total = 0;

        // ２行分進めていることに注意。
        $this->current_y += $this->line_height*2;
    }
    
    /*
     *      総合計を印刷
     */

    function printGrandTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"*総合計*");

        //
        //  仕入額のトータル。
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  仕入額

    }
        
    /*
    *  明細１行を印刷します。
    */

    function printLine($rec,$print_vendor)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        //
        // 仕入先/日　付/商品名/数量/単価/金額/入庫倉庫
        //

        //
        // 取引先(=仕入先）　先頭24 byteとする。
        //
        if($print_vendor == true)
        {
            if(strlen($rec[0]>24)){
                $vendor = substr($rec[0],0,24);
            }
            else
            {
                $vendor = $rec[0];
            }
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$vendor); //  取引先名
        }

        //
        //  日付け
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,ereg_replace("-","/",$rec[1]));

        //
        //  商品名
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);  //  商品名

        //
        //  数量
        //
        $this->pdf->SetXY($this->x_pos[3]-10,$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[3],8));  //  仕入れ数量
        $this->pdf->Write(20,$rec[7]);                           // 単位

        //
        //  単価
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],8));  //  単価

        //
        //  金額
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[5],10)); //  金額

        // 値引き額なんでトランザクションに入っていない。@later!

        //
        // 入庫倉庫
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,$rec[6],20); //  倉庫名


        //
        // トータルの計算。
        //
        $this->total += $rec[5];
        $this->vendor_total += $rec[5];
        $this->grand_total  += $rec[5];

        // Y位置の更新。
        $this->current_y += $this->line_height;

    }
}

?>