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
 *    入出荷差益高比較表 -  ProfitAndLossAccordingToShipmentSectionClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

//
//  注意：このソースの最後に空の行を置かないこと。PDF出力エラーになります。
//

//
// 日本語環境設定
//
require_once(OLUT_DIR . 'mbfpdf.php');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB
require_once('OlutAppLib.php');


// database configuration
class ProfitAndLossAccordingToShipmentSection_SQL extends SQL
{
    function ProfitAndLossAccordingToShipmentSection_SQL()
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


class ProfitAndLossAccordingToShipmentSection extends OlutApp
{
    var $left_margin  = 10;
    var $right_margin = 10;
    var $cell_height  = 8.0;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 25;     // １行に印字するデータの行数。
    var $total = 0;                     // 仕入れの合計。
    var $material_total = 0;            // 材料費のトータル。（用度品を除く）
    var $current_vendor;                // 現在印刷中の業者名を保持。業者が替わったら合計を印字するのに必要。
    var $current_date;                  // 現在印刷中の日付けを保持。日が変わったら小計を印字する。
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $target_year;
    var $target_month;
    var $start_date;
    var $end_date;
    var $start_date_of_last_year;
    var $end_date_of_last_year;
    var $shipment_section_names;
    var $shipment_section_codes;
    var $ck_flags;                      // セントラルキッチンのフラグ。１はCK.（＝食肉）
    var $invs;                          // 当月棚卸データを保持。
    var $page_no = 1;
    var $counted_invs;                  // 計算在庫
    var $depletion;                     // 減耗。配列です。

    // ctor
    function ProfitAndLossAccordingToShipmentSection()
    {
        $this->sql =& new ProfitAndLossAccordingToShipmentSection_SQL;
        // 項目印字位置設定。
        $this->x_pos = array(10,50,75,100,125,150,175,200,225,255,280,305,330);
    }

    /*
    *  印字のメイン
    */
    function printOut()
    {
        //
        //  集計範囲の日付けを計算。
        //
        $this->setupDates();


        //
        //  縦方向は「本部部門」
        //
        $this->setupShipmentSection();

        //
        // B4の紙サイズを指定しています。指定はポートレート。
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);

        $this->pdf->AddPage('L');
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
        $this->pdf->SetFont(GOTHIC,'',12);

        // 月合計、上半分を印字開始。
        $this->printHeader();

        if($this->printMonthlyReport()==true)
        {
            // 年合計、下半分を印字開始。
            $this->printMiddleHeader();

            if($this->printAnnualReport()==true)
            {
                $this->pdf->Output();
            }
        }
        else
        {
            // $this->renderScreen($formvars);
        }
        $this->sql->disconnect();        
    }


    /*
    *  日付け関係の設定。
    */

    function setupDates()
    {
        //$this->target_year = date('Y');
        //$this->target_month = '06';  // date('m');

        OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
    }

    //
    function setupMonthlyRange()
    {
        //
        // 4年分の月範囲を計算。 インデックスは0から-3までになる。
        //

        for($i=0; $i > -4; $i--)
        {
            $year = $this->target_year + $i;
            $last = OlutApp::getLastDate($year,$this->target_month);
            $this->start_date[$i] = "$year/$this->target_month/01";
            $this->end_date[$i]   = "$year/$this->target_month/$last";
        }
    }

    /*
    *  累積計算のため1月スタート。スタート月はプロファイルにする。@later
    */

    function setupAnnualRange()
    {
        $month = "01";
        for($i=0; $i > -4; $i--)
        {
            $year = $this->target_year + $i;
            $last = OlutApp::getLastDate($year,$this->target_month);
            $this->start_date[$i] = "$year/$month/01";
            $this->end_date[$i]   = "$year/$this->target_month/$last";
        }
    }

    /*
    *  本部部門の設定。
    */

    function setupShipmentSection()
    {
        $_query = "select name,code,ck_flag from m_shipment_section order by isdcd";   // isdcd にするか？ はい。
        $this->sql->query($_query,SQL_ALL);

        foreach($this->sql->record as $rec)
        {
            $this->shipment_section_names[] = $rec[0];
            $this->shipment_section_codes[] = $rec[1];
            $this->ck_flags[$rec[0]] = $rec[2];
        }
    }

    /*
    *  ヘダーを印字。
    */

    function printHeader()
    {
        // 印刷日時を得る。
        $year = date('Y');
        $month = date('m');
        $date  = date('d');

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = $this->cell_height;

        //
        $this->pdf->Cell(0,$h,"$this->target_year 年 $this->target_month 月　入出荷差益高比較表",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no ",'',1,'R');
        $this->page_no++;

        $header = array("＊月　間＊","荷渡勘定","前年比","指数","前月繰越高","当月仕入高",
        "商品見本","破損等","当月棚卸","差益高","前年比","指数");

        $this->current_y = 13;

        for($i=0; $i<12;$i++)
        {
            $this->pdf->SetXY($this->x_pos[$i],$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }


        // 線を引く
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);
    }

    /*
    *  出荷の合計を問い合わせ。（過去4年分問い合わせになる）
    */
    function queryMonthlyShipments($start_date, $end_date)
    {
        $_query = "select ss.name,-sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and (t.act_flag='5' or t.act_flag='7')";  // '6' サンプル含めてよいのか？ => 抜くのが正解。2005/9/4
        $_query .= " and t.act_date>='$start_date'";
        $_query .= " and t.act_date<='$end_date'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  サンプル出荷の合計を問い合わせ。
    */
    function querySampleShipments($start_date, $end_date)
    {
        $_query = "select ss.name,-sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and t.act_flag='6'";
        $_query .= " and t.act_date>='$start_date'";
        $_query .= " and t.act_date<='$end_date'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  在庫入力金額の問い合わせ
    */

    function queryMonthlyInventories($start_date)
    {
        //
        // 在庫レコードは夫々一ヶ月前の月のはじめの日にある。
        // たとえば8月1日には7月分の棚卸レコードがある。
        //
        $sd = OlutApp::getNextMonth($start_date);

        $_query = "select ss.name,sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and t.act_flag='0'";
        $_query .= " and t.act_date='$sd'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";

        //print $_query;
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  計算在庫を得る。移動平均。
    *
    *  ::::以下は使っていない:::::
    *  理由は、累計では現在のNTCのロジックに合致しないため。単月では合致。
    *
    */

    function queryCountedInventories($start_date,$end_date)
    {
        foreach($this->shipment_section_codes as $key => $sc)
        {
            //
            // 最初に商品毎に計算在庫を求める。
            //
            $_query = "select t.com_code,sum(t.amount)*" ;
            $_query .= "(select  case when sum(amount)=0 then 0 else sum(total_price)/sum(amount) end from t_main where ((act_flag='1' or act_flag='2' or act_flag='3')  ";
            $_query .= " and act_date>='$start_date' and act_date<='$end_date'";
            $_query .= " and t.com_code=com_code and amount <> 0) or";
            $_query .= " (act_flag='0' and act_date='$start_date' and t.com_code=com_code and amount<>0))";
            $_query .= " from t_main t, m_commodity c where t.act_date>='$start_date' and t.act_date<='$end_date'";
            $_query .= " and c.code=t.com_code and c.depletion_flag<>'1'";
            $_query .= " and t.ship_sec_code='$sc' group by t.com_code order by t.com_code";

            if(!$this->sql->query($_query,SQL_ALL))
            {
                print $_query;
                return false;
            }

            $total = 0;

            foreach($this->sql->record as $rec)
            {
                $total += $rec[1];
            }

            //
            //  計算在庫＝棚卸在庫　とする商品
            //
            $_query = "select t.com_code," ;
            $_query .= "(select total_price from t_main where com_code=t.com_code ";
            $_query .= " and act_date='$start_date' and act_flag='0')";
            $_query .= " from t_main t, m_commodity c where t.act_date>='$start_date' and t.act_date<='$end_date'";
            $_query .= " and c.code=t.com_code and c.depletion_flag='1'";
            $_query .= " and t.ship_sec_code='$sc' group by t.com_code order by t.com_code";

            if(!$this->sql->query($_query,SQL_ALL))
            {
                print $_query;
                return false;
            }

            foreach($this->sql->record as $rec)
            {
                $total += $rec[1];
            }

            $ssn = $this->shipment_section_names[$key];
            $this->counted_invs[] = array($ssn,round($total,0));
        }
        return true;
    }

    //
    //      減耗の計算。結果は this->depletion の配列
    //
    function queryDepletion($start_date,$end_date)
    {
        // 初期化。
        $this->depletion = array();
        // 最初は資材内のループです。
        foreach($this->shipment_section_codes as $key => $sc)
        {
            $ssn = $this->shipment_section_names[$key];
            if($this->ck_flags[$ssn] == '1')
            {
                // セントラルキッチンなので減耗をゼロとする。
                $ssn = $this->shipment_section_names[$key];
                $this->depletion[] = array($ssn,0);
            }
            else
            {
                //
                // 期間内にあるトランザクションから商品コードを抽出。
                // さらに減耗フラグをチェックしていることに注意。
                //
                
                $_query  = "select distinct t.com_code from t_main t, m_commodity c ";
                $_query .= " where t.act_date>='$start_date' and t.act_date<='$end_date' ";
                $_query .= " and c.code = t.com_code";
                $_query .= " and t.ship_sec_code='$sc' and c.depletion_flag='0' and t.deleted is null order by t.com_code";
                
                // print $_query;
                
                if($this->sql->query($_query,SQL_ALL) && $this->sql->record != null)
                {
                    // トランザクションありました。
                    $commodity_codes = $this->sql->record;

                    $depletion_total = 0;

                    foreach($commodity_codes as $code)
                    {
                        if($this->calcDepletionByCommodity($code[0],$start_date,$end_date,&$dep))
                        {
                            $depletion_total += $dep;
                        }
                    }
                    $ssn = $this->shipment_section_names[$key];
                    $this->depletion[] = array($ssn,round($depletion_total,0));

                }
                else
                {
                    // 配列がずれるので。
                    $ssn = $this->shipment_section_names[$key];
                    $this->depletion[] = array($ssn,0);
                }
            }
        }
        return true;
    }

    //
    // 期間内かつ、特定商品の減耗の配列を得る。
    //
    function calcDepletionByCommodity($code,$sd,$ed,&$depletion)
    {
        $depletion = 0;
        $init_price  = 0;
        $init_amount = 0;

        //  yyyy/mm/dd format.
        $num_of_month = substr($ed,5,2) - substr($sd,5,2);
        $cm = $sd;   // current month.

        // 月でループ
        for($i=0; $i<=$num_of_month; $i++)
        {
            //
            // 月の最終日を得る。
            //
            $last = OlutApp::getLastDate(substr($cm,0,4),substr($cm,5,2));
            $last_of_cm = substr($cm,0,8) . $last;

            // 初期在庫
            $_query = "select amount,total_price from t_main where act_flag=0 and com_code=$code and act_date='$cm'";
            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record != null)
            {
                $init_amount = $this->sql->record[0];
                $init_price  = $this->sql->record[1];
            }
            else
            {
                $init_amount = 0;
                $init_price  = 0;
            }

            // 期間内在庫。月単位の移動平均を求める。

            // 入庫の金額と数量
            $_query  = "select sum(amount),sum(total_price) from t_main where act_flag=1 and com_code=$code ";
            $_query .= " and act_date>='$cm' and act_date<='$last_of_cm'";

            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record == null)
            {
                $arrival_price  = 0;
                $arrival_amount = 0;
            }
            else {
                $arrival_price  = $this->sql->record[1];
                $arrival_amount = $this->sql->record[0];
            }

            if($arrival_amount + $init_amount != 0)
            {
                // 平均単価。
                $avg_price = round(($arrival_price+$init_price)/($arrival_amount+$init_amount),2);
            }
            else
            {
                $avg_price = 0;
            }

            // 出荷数量
            $_query  = "select sum(-amount) from t_main where (act_flag=5 or act_flag=6) and com_code=$code ";
            $_query .= " and act_date>='$cm' and act_date<='$last_of_cm'";

            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record == null)
            {
                $ship_amount = 0;
            }
            else {
                //
                $ship_amount = $this->sql->record[0];
            }

            //
            // 当月棚卸数量
            //

            $next_month = OlutApp::getNextMonth($cm);
            $_query = "select amount,total_price from t_main where act_flag=0 and com_code=$code and act_date='$next_month'";
            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record != null)
            {
                $inventory = $this->sql->record[0];
            }
            else {
                $inventory = 0;
            }

            //
            $depletion += ($inventory - ($arrival_amount + $init_amount - $ship_amount)) * $avg_price;

            // print "$i,$inventory,$arrival_amount,$ship_amount,$avg_price\n";

            $cm = $next_month;
        }
        return true;        // done.
    }

    /*
    *  入庫の問い合わせ
    */

    function queryMonthlyArrivalOfGoods($start_date,$end_date)
    {
        $_query = "select ss.name,sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and (t.act_flag='1' or t.act_flag='2' or t.act_flag='3')";  // このフラグでいいのかな？？
        $_query .= " and t.act_date>='$start_date'";
        $_query .= " and t.act_date<='$end_date'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  上半分の月範囲レポートを印刷。
    */

    function printMonthlyReport()
    {
        $this->setupMonthlyRange();
        $start_y = 25;
        return $this->printReportBody($start_y,false);
    }

    /*
    *  下半分の年範囲のレポートを印刷。
    */

    function printAnnualReport()
    {
        $this->setupAnnualRange();
        $start_y = $this->current_y + 10;
        return $this->printReportBody($start_y,true);
    }

    /*
    *  レポート本体の印字。
    */

    function printReportBody($start_y,$is_annual)
    {
        $h = $this->cell_height;
        //
        // 4年分の出荷を求める。
        //
        for($i=0; $i>-4; $i--)
        {
            if( $this->queryMonthlyShipments($this->start_date[$i],$this->end_date[$i]) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $ships[$i][$rec[0]] = $rec[1];
                }
            }
        }
        //
        //  4年分の前月までの在庫金額を求める。
        //
        for($i=0; $i>-4; $i--)
        {
            $last_start = OlutApp::getPrevMonth($this->start_date[$i]);
            $last_end = OlutApp::getPrevMonth($this->end_date[$i]);

            if( $this->queryMonthlyInventories($last_start) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $prev_invs[$i][$rec[0]] = $rec[1];
                }
            }
        }

        /*
        *  年間累計でなければ当月の棚卸在庫を求める。結果はクラス変数に入れる。
        */

        if($is_annual==false)
        {
            //
            //  4年分の在庫金額を求める。
            //
            for($i=0; $i>-4; $i--)
            {
                if( $this->queryMonthlyInventories($this->start_date[$i]) == true )
                {
                    foreach($this->sql->record as $rec)
                    {
                        $this->invs[$i][$rec[0]] = $rec[1];
                    }
                }
            }
        }

        //
        //  4年分の計算在庫を求める。
        //
        //for($i=0; $i>-4; $i--)
        //{
        //    if( $this->queryCountedInventories($this->start_date[$i],$this->end_date[$i]) == true )
        //    {
        //        foreach($this->counted_invs as $rec)
        //        {
        //            $counted_invs[$i][$rec[0]] = $rec[1];
        //        }
        //    }
        //}
        
        $depletion = array();

        // 2年分の減耗を求める。
        for($i=0; $i>-2; $i--)
        {
            // 
            if($this->queryDepletion($this->start_date[$i],$this->end_date[$i])==true)
            {
                foreach($this->depletion as $rec)
                {
                    $depletion[$i][$rec[0]] = $rec[1];
                }
            }
        }


        // 4年分の入庫金額を求める。
        for($i=0; $i>-4; $i--)
        {
            if( $this->queryMonthlyArrivalOfGoods($this->start_date[$i],$this->end_date[$i]) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $arg[$i][$rec[0]] = $rec[1];
                }
            }
        }

        // 4年分のサンプル出庫金額を求める。
        for($i=0; $i>-4; $i--)
        {
            if( $this->querySampleShipments($this->start_date[$i],$this->end_date[$i]) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $samples[$i][$rec[0]] = $rec[1];
                }
            }
        }

        // 行単位に印字開始。
        $this->current_y = $start_y;

        foreach($this->shipment_section_names as $ssn)
        {
            //
            //  本部部門名を印字
            //
            $this->pdf->SetFont(GOTHIC,'',10);
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$ssn);

            $this->pdf->SetFont(GOTHIC,'',9);

            // ===== COL 1 ======
            //
            //  当年当月の出荷合計
            //
            $v = $ships[0][$ssn];
            $this->pdf->SetXY($this->x_pos[1],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($v,11));
            $totals[0][0] += $v;

            //
            //  前年当月の出荷合計
            //
            $v = $ships[-1][$ssn];
            $this->pdf->SetXY($this->x_pos[1],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($v,11));
            $totals[-1][0] += $v;

            // ====== COL 2 ======
            // 前年比の上段。｢荷渡勘定」-「前年荷渡勘定」
            $ratio1 =  $ships[0][$ssn] -  $ships[-1][$ssn];
            $this->pdf->SetXY($this->x_pos[2],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($ratio1,11));
            $totals[0][1] += $ratio1;

            // 前年比の下段。｢荷渡勘定」-「前年荷渡勘定」   ==> 不要。
            //$ratio2 =  $ships[-1][$ssn] -  $ships[-2][$ssn];
            //$this->pdf->SetXY($this->x_pos[2],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($ratio2,11));
            //$totals[-1][1] += $ratio2;

            // ======= COL3 ========
            // 上段
            //
            // 指数　=　荷渡勘定 / 前年同月の出荷金額合計　*　１００　を小数点下一桁で四捨五入
            if(isset($ships[-1][$ssn]) && ($ships[-1][$ssn] != 0))
            {
                $index = round($ships[0][$ssn]/$ships[-1][$ssn]*100,1);
            }
            else
            {
                $index = 0;
            }
            $this->pdf->SetXY($this->x_pos[3],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            $totals[0][2] += $index;

            //
            // 下段
            //
            // 指数　=　荷渡勘定 / 前年同月の出荷金額合計　*　１００　を小数点下一桁で四捨五入
            //if(isset($ships[-2][$ssn]) && ($ships[-2][$ssn] != 0))
            //{
            //    $index = round($ships[-1][$ssn]/$ships[-2][$ssn]*100,1);
            // }
            //else
            //{
            //    $index = 0;
            //}
            //$this->pdf->SetXY($this->x_pos[3],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            //$totals[-1][2] += $index;

            // ======= COL４ ========
            // 上段
            //
            // 前月繰越高
            $this->pdf->SetXY($this->x_pos[4],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($prev_invs[0][$ssn],11));
            $totals[0][3] += $prev_invs[0][$ssn];

            //
            // 下段
            //
            // 前月繰越高
            $this->pdf->SetXY($this->x_pos[4],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($prev_invs[-1][$ssn],11));
            $totals[-1][3] += $prev_invs[-1][$ssn];

            // ==== COL 5 ======
            // 上段
            //
            // 当月仕入高
            $this->pdf->SetXY($this->x_pos[5],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($arg[0][$ssn],11));
            $totals[0][4] += $arg[0][$ssn];


            //
            // 下段
            //
            // 当月仕入高
            $this->pdf->SetXY($this->x_pos[5],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($arg[-1][$ssn],11));
            $totals[-1][4] += $arg[-1][$ssn];

            // ==== COL 6 ======
            // 上段
            //
            // サンプル出庫
            $this->pdf->SetXY($this->x_pos[6],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($samples[0][$ssn],11));
            $totals[0][5] += $samples[0][$ssn];

            //
            // 下段
            //
            // サンプル出庫
            $this->pdf->SetXY($this->x_pos[6],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($samples[-1][$ssn],11));
            $totals[-1][5] += $samples[-1][$ssn];

            // ==== COL 7 ====
            // 上段
            //
            // 破損等。 減耗と同じ　：　計算在庫　-　実在庫　
            //
            //   上記は逆のように思います。実在庫　-　計算在庫
            //
            //
            //if($this->ck_flags[$ssn]=='1')      // CK は、破損なし。
            //{
            //    $loss1 = 0;
            //}
            //else
            //{
            //    $loss1 =  $this->invs[0][$ssn] - $counted_invs[0][$ssn];
            //}

            $loss1 = $depletion[0][$ssn];
            //
            $this->pdf->SetXY($this->x_pos[7],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($loss1,11));
            $totals[0][6] += $loss1;

            //
            // 下段
            //
            // 破損等。
            //if($this->ck_flags[$ssn]=='1')
            //{
            //    $loss2 = 0;
            //}
            //else
            //{
            //    $loss2 = $this->invs[-1][$ssn] - $counted_invs[-1][$ssn];
            //}

            $loss2 = $depletion[-1][$ssn];

            $this->pdf->SetXY($this->x_pos[7],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($loss2,11));
            $totals[-1][6] += $loss2;

            // 前々年の破損を計算しておきます。
            //if($this->ck_flags[$ssn]=='1')
            //{
            //    $loss3 = 0;
            //}
            //else
            //{
            //    $loss3 = $this->invs[-2][$ssn] - $counted_invs[-2][$ssn];
            //}

            // ==== COL 8 ====
            // 上段
            // 当月棚卸
            //
            // 注意；当月棚卸は年累計でも月間計でも同じものを印字するので、$this->invsを参照する。
            //
            $this->pdf->SetXY($this->x_pos[8],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($this->invs[0][$ssn],11));
            $totals[0][7] += $this->invs[0][$ssn];

            //
            // 下段
            //
            // 当月棚卸
            $this->pdf->SetXY($this->x_pos[8],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($this->invs[-1][$ssn],11));
            $totals[-1][7] += $this->invs[-1][$ssn];

            // ==== COL 9 ====
            // 上段
            // 差益高=荷渡勘定　- ( 前月繰越高　＋　当月仕入高 - 当月棚卸　）＋商品見本　-　破損等（減耗）

            $profit1 = $ships[0][$ssn] - ($prev_invs[0][$ssn] + $arg[0][$ssn] - $this->invs[0][$ssn]) - $loss1 + $samples[0][$ssn];
            $this->pdf->SetXY($this->x_pos[9],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($profit1,11));
            $totals[0][8] += $profit1;

            // 下段
            // 差益高=荷渡勘定　- ( 前月繰越高　＋　当月仕入高 - 当月棚卸　）＋商品見本　-　破損等（減耗）

            $profit2 = $ships[-1][$ssn] - ($prev_invs[-1][$ssn] + $arg[-1][$ssn] - $this->invs[-1][$ssn]) - $loss2 + $samples[-1][$ssn];
            $this->pdf->SetXY($this->x_pos[9],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($profit2,11));
            $totals[-1][8] += $profit2;

            // 前々年の差益高を計算しておく。
            $profit3 = $ships[-2][$ssn] - ($prev_invs[-2][$ssn] + $arg[-2][$ssn] - $this->invs[-2][$ssn]) - $loss3 + $samples[-2][$ssn];

            // ==== COL 10 ====
            // 上段
            // 前年比　   -> 差益高　-　前年同月の差益高

            $ratio1 = $profit1 - $profit2;
            $this->pdf->SetXY($this->x_pos[10],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($ratio1,11));
            $totals[0][9] += $ratio1;

            // 前年比の下段は要らない。
            // 下段
            $ratio2 = $profit2 - $profit3;
            //$this->pdf->SetXY($this->x_pos[10],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($ratio2,11));
            $totals[-1][9] += $ratio2;
            //
            // ==== COL 11 ====
            //
            // 指数＝差益高　 / 前年同月の差益高合計　*　１００　を小数点下一桁で四捨五入
            //
            if($profit2 != 0)
            {
                $index = round($profit1 / $profit2 * 100,1);
            }
            else
            {
                $index = 0;
            }
            $this->pdf->SetXY($this->x_pos[11],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            $totals[0][10] += $index;

            //if($profit3 != 0)
            //{
            //    $index = round($profit2 / $profit3 * 100,1);
            //}
            //else
            //{
            //    $index = 0;
            //}
            //$this->pdf->SetXY($this->x_pos[11],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            //$totals[-1][10] += $index;

            // 次の行へ。
            $this->current_y += ($h+1);
        }

        // トータル印字
        $this->printTotals($totals);

        return true;
    }

    /*
    *  資材内のトータル印字
    *
    */

    function printTotals($totals)
    {
        $h = $this->cell_height;
        //
        //  本部部門名を印字
        //
        $this->pdf->SetFont(GOTHIC,'',10);
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"資材部合計");

        // chage font here....
        $this->pdf->SetFont(GOTHIC,'',9);
        // ===== COL 1 ======
        //
        //  当年当月の出荷合計
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][0],11));

        //
        //  前年当月の出荷合計
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][0],11));

        // ====== COL 2 ======
        // 前年比の上段。｢荷渡勘定」-「前年荷渡勘定」
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][1],11));

        // 前年比の下段。｢荷渡勘定」-「前年荷渡勘定」  ==> 不要
        //$this->pdf->SetXY($this->x_pos[2],$this->current_y+$h/2);
        //$this->pdf->Write(20,OlutApp::formatNumber($totals[-1][0],11));

        // ======= COL3 ========
        // 上段
        //
        // 指数　=　荷渡勘定 / 前年同月の出荷金額合計　*　１００　を小数点下一桁で四捨五入

        if(isset($totals[-1][0]) && ($totals[-1][0] != 0))
        {
            $index = round($totals[0][0]/$totals[-1][0]*100,1);
        }
        else
        {
            $index = 0;
        }
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));

        //
        // 下段
        //
        // 指数　=　荷渡勘定 / 前年同月の出荷金額合計　*　１００　を小数点下一桁で四捨五入

        // ブランクでいいはず。

        // ======= COL４ ========
        // 上段
        //
        // 前月繰越高
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][3],11));

        //
        // 下段
        //
        // 前月繰越高
        $this->pdf->SetXY($this->x_pos[4],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][3],11));

        // ==== COL 5 ======
        // 上段
        //
        // 当月仕入高
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][4],11));


        //
        // 下段
        //
        // 当月仕入高
        $this->pdf->SetXY($this->x_pos[5],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][4],11));

        // ==== COL 6 ======
        // 上段
        //
        // サンプル出庫
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][5],11));

        //
        // 下段
        //
        // サンプル出庫
        $this->pdf->SetXY($this->x_pos[6],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][5],11));

        // ==== COL 7 ====
        // 上段
        //
        // 破損等。 減耗と同じ　：　計算在庫　-　実在庫　
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][6],11));

        //
        // 下段
        //
        // 破損等。
        $this->pdf->SetXY($this->x_pos[7],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][6],11));

        // ==== COL 8 ====
        // 上段
        // 当月棚卸
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][7],11));

        //
        // 下段
        //
        // 当月棚卸
        $this->pdf->SetXY($this->x_pos[8],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][7],11));

        // ==== COL 9 ====
        // 上段
        // 差益高=荷渡勘定　- ( 前月繰越高　＋　当月仕入高 - 当月棚卸　）＋商品見本　-　破損等（減耗）

        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][8],11));

        // 下段
        // 差益高=荷渡勘定　- ( 前月繰越高　＋　当月仕入高 - 当月棚卸　）＋商品見本　-　破損等（減耗）

        $this->pdf->SetXY($this->x_pos[9],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][8],11));

        // ==== COL 10 ====
        // 上段
        // 前年比　   -> 差益高　-　前年同月の差益高

        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][9],11));

        // 下段不要。
        //$this->pdf->SetXY($this->x_pos[10],$this->current_y+$h/2);
        //$this->pdf->Write(20,OlutApp::formatNumber($totals[-1][9],11));

        //
        // ==== COL 11 ====
        //
        // 指数＝差益高　 / 前年同月の差益高合計　*　１００　を小数点下一桁で四捨五入
        //
        if($totals[-1][8] != 0)
        {
            $index = round($totals[0][8] /$totals[-1][8] * 100,1);
        }
        else
        {
            $index = 0;
        }
        $this->pdf->SetXY($this->x_pos[11],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));

        // 下段はブランクでいいはず。

        // 次の行へ。
        $this->current_y += ($h+1);
    }

    /*
    *  累計用のヘダーを印字
    *
    */
    function printMiddleHeader()
    {

        $this->current_y += 5;

        $this->pdf->SetFont(GOTHIC,'',12);

        $header = array("＊累　計＊","荷渡勘定","前年比",
        "指数","前年繰越高","当期仕入高","商品見本","破損等","当月棚卸","差益高","前年比","指数");

        for($i=0; $i<12;$i++)
        {
            $this->pdf->SetXY($this->x_pos[$i],$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }

        // 線を引く
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+5 ,$width,$this->current_y+5 );
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);
    }
}

?>