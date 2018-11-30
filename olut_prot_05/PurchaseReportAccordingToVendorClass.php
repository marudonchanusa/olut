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
 *    業者別仕入一覧 -  PurchaseReportAccordingToVendorClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *    2005/10/26 ver 1.00.01 買掛 -> 現金と印字する。
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
require_once('OlutAppLib.php');

// smarty configuration
class PurchaseReportAccordingToVendor_Smarty extends Smarty
{
    function PurchaseReportAccordingToVendor_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class PurchaseReportAccordingToVendor_SQL extends SQL
{
    function PurchaseReportAccordingToVendor_SQL()
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

class PurchaseReportAccordingToVendor extends OlutApp
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
    var $num_of_item_per_page = 29;     // １行に印字するデータの行数。
    var $total;                         // 仕入れの合計。
    var $grand_total;                   // 支払額の合計。
    var $current_vendor;
    var $current_commodity;             //
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $page_no = 1;
    var $last_printed_vendor;
    var $cash_count = 0;            // 現金払い件数。
    var $credit_count = 0;          // 買掛件数。
    var $cash_arrivals_total    = 0;
    var $credit_arrivals_total  = 0;
    var $cash_discount_total   = 0;
    var $credit_discount_total = 0;
    var $cash_paid_total = 0;
    var $credit_paid_total = 0;
    var $target_year;
    var $target_month;

    // ctor
    function PurchaseReportAccordingToVendor()
    {
        $this->tpl =& new PurchaseReportAccordingToVendor_Smarty;
        $this->sql =& new PurchaseReportAccordingToVendor_SQL;

        // 項目印字位置設定。
        $this->x_pos = array(10,80,100,190,220,250,280,310);
    }

    /*
    *  印刷範囲入力画面のレンダリング
    */

    function renderScreen($formvars)
    {

        if(isset($formvars['target_year']))
        {
            $this->target_year  = $formvars['target_year'];
            $this->target_month = $formvars['target_month'];
        }
        
        $this->tpl->assign('target_year_list',  OlutApp::getTargetYearList($this->target_year));
        $this->tpl->assign('target_month_list', OlutApp::getTargetMonthList($this->target_month));
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('PurchaseReportAccordingToVendorForm.tpl');
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
        $this->printHeader($formvars,'0');
        
        // 買掛
        if($this->printData($formvars,'0')==false)
        {
            // to show error messages.
            $this->renderScreen($formvars);
        }
        else
        {
            // 現金へ
            $this->forcePageChange($formvars,'1');
            $this->current_vendor = null;
            
            if($this->printData($formvars,'1')==false)
            {
                // to show error messages.
                $this->renderScreen($formvars);
            }
            else 
            {
                // 総合計を印字
                $this->checkPageChange(3,$formvars,'1');
                $this->printGrandTotal($formvars);                
                
                header("content-type: application/pdf;");
                $this->pdf->Output();
            }
        }
    }

    /*
    *  各ページのヘダーを印刷
    */

    function printHeader($formvars,$payment_flag)
    {
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        if(isset($formvars['target_year']))
        {
            $target_month = $formvars['target_month'];
            $target_year  = $formvars['target_year'];
        }
        else
        {
            $target_month = $month;
            $target_year  = $year;
        }

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;
        
        if($payment_flag=='0')
        {
            $pay_type = '買掛';
        }
        else 
        {
            $pay_type = '現金';
        }

        $this->pdf->Cell(0,9,"$target_year 年 $target_month 月　業者別仕入一覧表($pay_type)",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;
        //
        $this->current_y = 15;

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"仕入先");
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,"扱い先");
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"商品名");
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,"仕入数量");
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,"平均単価");
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,"仕入額");
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,"値引額");
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,"支払金額");

        // 線を引く
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  データ問い合わせ＆明細行印刷。
    */

    function printData($formvars,$payment_flag)
    {
        $this->target_month = $formvars['target_month'];
        $this->target_year  = $formvars['target_year'];
        
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];

        $date_from = $this->target_year . '/' . $this->target_month . '/01';
        $last      = OlutApp::getLastDate($this->target_year, $this->target_month);
        $date_to   = $this->target_year . '/' . $this->target_month . "/$last";

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        $_query = "select v.name, s.name, c.name, sum(t.amount), sum(t.total_price) ";
        // ここから値引きの副照会
        $_query .= ",(select total_price from t_main where t.slip_no=slip_no and act_flag='2'";
        $_query .= " and t.slip_no=slip_no and t.com_code=com_code) as discount";
        $_query .= ",t.payment_flag";

        //
        $_query .= " from t_main t,m_commodity c, m_vendor v , m_shipment_section s ";
        $_query .= " where (t.act_flag='1' or t.act_flag='3')";
        $_query .= " and t.orig_code >= '$code_from' and t.orig_code <= '$code_to'";
        $_query .= " and payment_flag='$payment_flag'";


        if(isset($date_from) && strlen($date_from))
        {
            $_query .= " and t.act_date >= '$date_from'";
        }
        if(isset($date_to) && strlen($date_from))
        {
            $_query .= " and t.act_date <= '$date_to'";
        }

        $_query .= " and t.com_code=c.code and v.code=t.orig_code and ";
        $_query .= " s.code=t.ship_sec_code ";
        $_query .= " group by t.com_code,c.name,v.name,s.name,t.orig_code,discount,t.payment_flag ";
        $_query .= " order by t.orig_code,s.name, t.com_code" ;

        //print $_query;


        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->lines     = 0;
            $this->current_y = 30;
            // ループする。
            foreach($this->sql->record as $rec)
            {
                // ここで業者がセットされてないとすれば、全くの最初。
                if($this->current_vendor == null)
                {
                    $this->current_vendor = $rec[0];
                    $this->current_commodity = $rec[2];
                }
                // 業者が変更された場合にはトータルを印字します。
                if(strcmp($this->current_vendor,$rec[0]))
                {
                    // トータル印字。２行になるのだが印字できるか？
                    $this->checkPageChange(2,$formvars,$payment_flag);

                    // トータル印字。
                    $this->printTotal();

                    // 現在の業者として保持。
                    $this->current_vendor = $rec[0];
                }

                // 明細行を印字する。
                $this->checkPageChange(1,$formvars,$payment_flag);
                $this->printLine($rec);


            }

            // ループを抜けたらそれまでの業者分のトータルを印字。
            $this->checkPageChange(2,$formvars,$payment_flag);
            $this->printTotal();

        }
        return true;
    }

    /*
    *  ページ替えのチェックと実行。
    *  linesを初期設定したりインクリメントするので注意。
    *
    */

    function checkPageChange($lines_to_add,$formvars,$payment_flag)
    {
        if(($this->lines+$lines_to_add) >= $this->num_of_item_per_page)
        {
            $this->forcePageChange($formvars,$payment_flag);
        }
        else {
            $this->lines += $lines_to_add;
        }
    }
    
    function forcePageChange($formvars,$payment_flag)
    {
        $this->pdf->AddPage('L');
        $this->printHeader($formvars,$payment_flag);
        $this->lines = 0;
        $this->current_y = 30;
        $this->last_printed_vendor = null;       // ページが替わったので取引先名は印字する。        
    }

    /*
    *  トータルを印字します。
    */

    function printTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"合計"); //  仕入額

        //
        //  仕入額のトータル。
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,10)); //  仕入額


        //
        //  支払額トータル
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  仕入額

        // トータルをリセット。
        $this->total = 0;
        $this->grand_total = 0;

        // ２行分進めていることに注意。
        $this->current_y += $this->line_height*2;
    }

    /*
    *  明細１行を印刷します。
    */

    function printLine($rec)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        //
        // 取引先 / 扱い先 / 商品名　/ 仕入数量　/　平均単価　/　仕入額　/　値引額　/　支払金額
        //

        //
        // 取引先先頭24 byteとする。
        //
        if(strcmp($this->last_printed_vendor,$rec[0]))
        //if(true)
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
        $this->last_printed_vendor = $rec[0];

        //
        //  扱い先
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,$rec[1]); //  扱い先

        //
        //  商品名
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);  //  商品名

        //
        //  仕入れ数量
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[3],8,2));  //  仕入れ数量

        //$this->pdf->Write(20,OlutApp::formatNumber($rec[3],8));  //  仕入れ数量

        //
        //  平均単価
        //
        if($rec[3] != 0)
        {
            $average = round($rec[4] / $rec[3],2);
        }
        else
        {
            $average =0;
        }
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($average,12,2));  //  平均単価

        //
        //  仕入額
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],10)); //  仕入額

        // 値引き額 => 入庫区分'2' のレコード　
        $discount = $rec[5] * (-1);
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($discount,10));

        // 値引きはマイナスで入っているので、以下は加算となる。
        $paid_total = $rec[4]+$rec[5];
        //
        //  支払額　
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($paid_total,10));


        //
        // トータルの計算。
        //
        $this->total += $rec[4];
        $this->grand_total += $paid_total;

        if($rec[6] == '0')
        {
            $this->credit_arrivals_total  += $rec[4];
            $this->credit_discount_total -= $rec[5];
            $this->credit_paid_total     += $paid_total;
        }
        else
        {
            $this->cash_arrivals_total  += $rec[4];
            $this->cash_discount_total -= $rec[5];
            $this->cash_paid_total     += $paid_total;
        }

        // Y位置の更新。
        $this->current_y += $this->line_height;

    }


    function printGrandTotal($formvars)
    {
        $this->credit_count = $this->getPaymentCount('0',$formvars);
        $this->cash_count   = $this->getPaymentCount('1',$formvars);

        // three lines.
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"買掛合計");

        $this->pdf->SetXY(50,$this->current_y);
        $this->pdf->Write(20,"仕入件数: $this->credit_count");

        $credit_arrivals_total = OlutApp::formatNumber($this->credit_arrivals_total,11);
        $this->pdf->SetXY(90,$this->current_y);
        $this->pdf->Write(20,"仕入額: $credit_arrivals_total");

        $credit_discount_total = OlutApp::formatNumber($this->credit_discount_total,11);
        $this->pdf->SetXY(140,$this->current_y);
        $this->pdf->Write(20,"値引額: $credit_discount_total");

        $credit_paid_total = OlutApp::formatNumber($this->credit_paid_total,11);
        $this->pdf->SetXY(190,$this->current_y);
        $this->pdf->Write(20,"支払額: $credit_paid_total");

        // Y位置の更新。
        $this->current_y += $this->line_height;

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"現金合計");

        $this->pdf->SetXY(50,$this->current_y);
        $this->pdf->Write(20,"仕入件数: $this->cash_count");

        $cash_arrivals_total = OlutApp::formatNumber($this->cash_arrivals_total,11);
        $this->pdf->SetXY(90,$this->current_y);
        $this->pdf->Write(20,"仕入額: $cash_arrivals_total");

        $cash_discount_total = OlutApp::formatNumber($this->cash_discount_total,11);
        $this->pdf->SetXY(140,$this->current_y);
        $this->pdf->Write(20,"値引額: $cash_discount_total");

        $cash_paid_total = OlutApp::formatNumber($this->cash_paid_total,11);
        $this->pdf->SetXY(190,$this->current_y);
        $this->pdf->Write(20,"支払額: $cash_paid_total");

        // Y位置の更新。
        $this->current_y += $this->line_height;

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"総　合計");

        $grand_total_count = $this->credit_count+ $this->cash_count;

        $this->pdf->SetXY(50,$this->current_y);
        $this->pdf->Write(20,"仕入件数: $grand_total_count");

        $grand_total_arrivals = $this->cash_arrivals_total+$this->credit_arrivals_total;
        $grand_total_arrivals = OlutApp::formatNumber($grand_total_arrivals,11);
        $this->pdf->SetXY(90,$this->current_y);
        $this->pdf->Write(20,"仕入額: $grand_total_arrivals");

        $grand_total_discount = $this->cash_discount_total + $this->credit_discount_total;
        $grand_total_discount = OlutApp::formatNumber($grand_total_discount,11);
        $this->pdf->SetXY(140,$this->current_y);
        $this->pdf->Write(20,"値引額: $grand_total_discount");

        $grand_paid_total = $this->cash_paid_total + $this->credit_paid_total;

        $grand_paid_total = OlutApp::formatNumber($grand_paid_total,11);
        $this->pdf->SetXY(190,$this->current_y);
        $this->pdf->Write(20,"支払額: $grand_paid_total");

    }

    function getPaymentCount($payment_flag,$formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];
        $date_from = OlutApp::formatDate($formvars['date_from']); // ハイフォンでフォーマット
        $date_to   = OlutApp::formatDate($formvars['date_to']);

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        $_query = "select count(*) from t_main t ";
        $_query .= " where t.payment_flag='$payment_flag' and (t.act_flag='1' or t.act_flag='3')";
        $_query .= " and t.orig_code >= '$code_from' and t.orig_code <= '$code_to'";


        if(isset($date_from) && strlen($date_from))
        {
            $_query .= " and t.act_date >= '$date_from'";
        }
        if(isset($date_to) && strlen($date_from))
        {
            $_query .= " and t.act_date <= '$date_to'";
        }

        // print $_query;

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            return 0;
        }
        return $this->sql->record['count'];
    }
}

?>