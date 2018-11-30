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
 *    荷渡明細書書 - ShipmentDetailReportClass.php
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
require_once('SystemProfile.php');

// smarty configuration
class ShipmentDetailReport_Smarty extends Smarty
{
    function ShipmentDetailReport_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ShipmentDetailReport_SQL extends SQL
{
    function ShipmentDetailReport_SQL()
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

class ShipmentDetailReport extends OlutApp
{
    var $left_margin = 10;
    var $right_margin = 10;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 27;     // １行に印字するデータの行数。
    var $total = 0;                     // 仕入れの合計。
    var $material_total = 0;            // 材料費のトータル。（用度品を除く）
    var $monthly_total = 0;             // 月の総合計
    var $material_monthly_total = 0;    // 月の材料費の合計。
    var $current_store;                 // 現在印刷中の業者名を保持。業者が替わったら合計を印字するのに必要。
    var $current_store_division;        // 現在の店舗部門。
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $page_no = 1;
    var $system_profile;

    // ctor
    function ShipmentDetailReport()
    {
        $this->tpl =& new ShipmentDetailReport_Smarty;
        $this->sql =& new ShipmentDetailReport_SQL;

        // 項目印字位置設定。
        $this->x_pos = array(10,60,85,110,135,160,185,210,235,265,290);
    }

    function Dispose()
    {
        $this->sql->disconnect();
    }

    /*
    *  印刷範囲入力画面のレンダリング
    */

    function renderScreen($formvars)
    {
        $this->tpl->assign('target_year_list',$this->getTargetYearList());
        $this->tpl->assign('target_month_list',$this->getTargetMonthList());
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ShipmentDetailReportForm.tpl');
    }

    /*
    *  印刷関数
    *
    */
    function printOut($formvars)
    {
        //
        $this->system_profile =& new SystemProfile();

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

        $target_year  = $formvars['target_year'];
        $target_month = $formvars['target_month'];

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        //
        //
        $this->pdf->Cell(0,9,"$target_year 年 $target_month 月   荷渡明細書",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;

        //
        $this->current_y = 15;

        $header = array("店舗名","部門","仮勘定","酒類","鮮魚","ＴＯ","食肉","材料費計","用度品","合計","消費税");

        for($i=0; $i<11;$i++)
        {
            if($i>1){
                $delta = 5;
            }else {
                $delta = 0;
            }

            $this->pdf->SetXY($this->x_pos[$i]+$delta,$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }

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

        $target_year  = $formvars['target_year'];
        $target_month = $formvars['target_month'];
        $target_last_date = OlutApp::getLastDate($target_year,$target_month);

        $dt_from = "$target_year/$target_month/01";
        $dt_to   = "$target_year/$target_month/$target_last_date";

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        // 出庫の集計。
        // act_flag=5,6,7 を対象。（これでいいのか確認 @later)
        //

        $_query  = "select st.name,sd.name,ss.name,-sum(total_price) ";
        $_query .= " from t_main t,m_shipment_section ss, m_store_division sd, m_store st ";
        $_query .= " where t.dest_code=st.code and sd.code=t.store_sec_code and ss.code=t.ship_sec_code";
        $_query .= " and t.dest_code >= '$code_from' and t.dest_code <= '$code_to' ";
        $_query .= " and (t.act_flag='5' or t.act_flag='6' or t.act_flag='7') ";
        $_query .= " and (t.act_date >= '$dt_from' and t.act_date <= '$dt_to') ";
        $_query .= " group by ss.code, ss.name, sd.code, sd.name, st.name, st.code ";
        $_query .= " order by st.code, st.name, sd.name, ss.code";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->total     = 0;
            $this->lines     = 0;
            $this->current_y = 30;
            $n = 0;

            // rec[0] => 業者名
            // rec[1] => 資材内
            // rec[2] => 金額

            foreach($this->sql->record as $rec)
            {
                // ここで店舗＋店舗部門がセットされてないとすれば、全くの最初。
                if(!isset($this->current_store))
                {
                    $this->current_store = $rec[0];
                    $this->current_store_division = $rec[1];
                }

                // 店舗または店舗内部門が変わったのでデータ行を1行印字。
                if(strcmp($this->current_store,$rec[0])!=0 || strcmp($this->current_store_division,$rec[1]) != 0)
                {
                    $this->checkPageChange(1,$formvars);
                    $this->printLine($values);

                    // 店舗が変わったら店舗の合計を印字
                    if(strcmp($this->current_store,$rec[0]) != 0)
                    {
                        // 店舗合計印字
                        $this->checkPageChange(2,$formvars);
                        $this->printStoreTotal();
                        $this->current_store_division = $rec[1];
                    }

                    $this->current_store = $rec[0];              // 店舗として保持。
                    $this->current_store_division = $rec[1];
                    unset($values);
                }


                // 店舗が変わるまでは1行に印字となる。
                $values[$rec[2]] = $rec[3];
                $n++;

            }

            // ループを抜けたらそれまでの店舗について印字。
            $this->checkPageChange(1,$formvars);
            $this->printLine($values);

            // 店舗合計印字
            $this->checkPageChange(2,$formvars);
            $this->printStoreTotal();

            // さらに月のトータル印字
            $this->checkPageChange(2,$formvars);
            $this->printMonthlyTotal();

        }
        return true;
    }

    /*
    *  ページ替えのチェックと実行。
    *  linesを初期設定したりインクリメントするので注意。
    *
    */

    function checkPageChange($lines_to_add,$formvars)
    {
        if(($this->lines+$lines_to_add) >= $this->num_of_item_per_page)
        {
            $this->pdf->AddPage('L');
            $this->printHeader($formvars);
            $this->lines = 0;
            $this->current_y = 30;
        }
        else {
            $this->lines += $lines_to_add;
        }
    }

    /*
    *  合計を印字します。（店のトータル）
    */

    function printStoreTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*店舗合計*");

        //
        //  材料費計。
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->material_total,10)); //  仕入額


        //
        //  設備費も含めた合計
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,10)); //  仕入額

        // 月のトータルに加算。
        $this->material_monthly_total += $this->material_total;
        $this->monthly_total += $this->total;

        // トータルをリセット。
        $this->total = 0;
        $this->material_total = 0;

        // ２行分進めていることに注意。
        $this->current_y += $this->line_height*2;
    }

    function printMonthlyTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*月合計*");

        //
        //  材料費計。
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->material_monthly_total,10)); //  仕入額


        //
        //  トータル
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_total,10)); //  仕入額

        // ２行分進めていることに注意。
        $this->current_y += $this->line_height*2;
    }
    /*
    *  明細１行を印刷します。
    */

    function printLine($values)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        // 材料費計
        $material_total = 0;

        //
        // 店舗名　部門　　購買品   酒類　　鮮魚　　ＴＯ　　食肉　　材料費計　　用度品　合計 消費税
        //

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,$this->current_store); //  店舗名

        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,$this->current_store_division); //  部門

        //
        //  仮勘定
        //
        $v = $values['仮勘定'];
        if( isset($v) )
        {
            $material_total += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));

        //
        //  酒類
        //
        $v = $values['酒類'];
        if(isset($v))
        {
            $material_total += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        //  鮮魚
        //
        $v = $values['鮮魚'];
        if(isset($v))
        {
            $material_total += $v;
        }
        else
        {
            $v =0;
        }
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        //  ＴＯ
        //
        $v = $values['ＴＯ'];
        if(isset($v))
        {
            $material_total += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        //  食肉
        //
        $v = $values['食肉'];
        if(isset($v))
        {
            $material_total += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        // 材料費計
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,10));

        $this->material_total += $material_total;


        // 用度品
        $v = $values['用度品'];
        if(isset($v))
        {
            $material_total += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));

        // 行トータル印字
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,10));

        // 消費税 ==> 税率は設定ファイル？
        $tax = $material_total * $this->system_profile->tax;
        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($tax,10));


        //
        $this->total += $material_total;

        // Y位置の更新。
        $this->current_y += $this->line_height;

    }

    function getTargetYearList()
    {
        for($i=2005; $i<2010; $i++)
        {
            if($i==date('Y'))
            {
                $sel = 'selected';
            }
            else
            {
                $sel = '';
            }

            $result .= "<option value=$i $sel>$i</option>";
        }
        return $result;
    }

    function getTargetMonthList()
    {
        $year  = null;
        $month = null;
        OlutApp::getCurrentProcessDate($this->sql,&$year,&$month);
        
        for($i=1; $i<=12; $i++)
        {
            if($i==$month)
            {
                $sel = 'selected';
            }
            else
            {
                $sel = '';
            }

            $result .= "<option value=$i $sel>$i</option>";
        }
        return $result;
    }
}

?>