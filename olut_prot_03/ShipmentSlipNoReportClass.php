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
 *    店票毎荷渡明細書 - ShipmentSlipNolReportClass.php
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
class ShipmentSlipNoReport_Smarty extends Smarty
{
    function ShipmentSlipNoReport_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ShipmentSlipNoReport_SQL extends SQL
{
    function ShipmentSlipNoReport_SQL()
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

class ShipmentSlipNoReport extends OlutApp
{
    var $left_margin = 10;
    var $right_margin = 10;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_A4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_A4_HEIGHT; // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 37;     // １行に印字するデータの行数。
    var $total = 0;                     // 仕入れの合計。
    var $section_total = 0;             //
    var $material_total = 0;            // 材料費のトータル。（用度品を除く）
    var $monthly_total = 0;             // 月の総合計
    var $current_store;                 // 現在印刷中の業者名を保持。業者が替わったら合計を印字するのに必要。
    var $current_store_division;        // 現在の店舗部門。
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $page_no = 1;
    var $system_profile;
    var $target_year;
    var $target_month;

    // ctor
    function ShipmentSlipNoReport()
    {
        $this->tpl =& new ShipmentSlipNoReport_Smarty;
        $this->sql =& new ShipmentSlipNoReport_SQL;

        // 項目印字位置設定。
        $this->x_pos = array(10,70,100,125,145,170,195,220,245,275,300);
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
        $this->tpl->display('ShipmentSlipNoReportForm.tpl');
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
        // B4の紙サイズを指定しています。指定はA4ポートレート。
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        //$this->pdf->SetButtomMargin(0);

        $this->pdf->AddPage('P');
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->printHeader($formvars);
        if($this->printData($formvars)==true)
        {
            header("content-type: application/pdf;");
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
        $this->pdf->Cell(0,9,"$target_year 年 $target_month 月   店票毎荷渡明細書",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;

        //
        $this->current_y = 15;

        $header = array("店舗名","部門","日付","伝票番号","  金額");

        for($i=0; $i<sizeof($header);$i++)
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
        $width = $this->paper_width - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  データ問い合わせ＆明細行印刷。
    */


    function printData($formvars)
    {
        if($this->target_year != null && $this->target_month != null)
        {
            $code_from = '00000';
            $code_to   = '99999';
            $target_year  = $this->target_year;
            $target_month = $this->target_month;
        }
        else
        {
            $code_from = $formvars['code_from'];
            $code_to   = $formvars['code_to'];
            $target_year  = $formvars['target_year'];
            $target_month = $formvars['target_month'];
        }
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

        $_query  = "select st.name,sd.name,act_date,slip_no,-sum(total_price) ";
        $_query .= " from t_main t, m_store_division sd, m_store st ";
        $_query .= " where t.dest_code=st.code and sd.code=t.store_sec_code ";
        $_query .= " and t.dest_code >= '$code_from' and t.dest_code <= '$code_to' ";
        $_query .= " and (t.act_flag='5' or t.act_flag='6' or t.act_flag='7') ";
        $_query .= " and (t.act_date >= '$dt_from' and t.act_date <= '$dt_to') ";
        $_query .= " group by sd.code, sd.name, st.name, st.code,act_date,slip_no ";
        $_query .= " order by st.code, sd.code, act_date, slip_no";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }
        else
        {
            $this->total     = 0;
            $this->lines     = 0;
            $this->current_y = 30;
            $n = 0;

            // rec[0] => 店舗名
            // rec[1] => 部門
            // rec[2] => 日
            //    [3] => 伝票番号
            //    [4] => 金額

            foreach($this->sql->record as $rec)
            {
                // 店舗が変わったら店舗の合計を印字
                if($this->current_store != null && strcmp($this->current_store,$rec[0]) != 0)
                {
                    // 部門トータル印字
                    $this->checkPageChange(1,$formvars);
                    $this->printSectionTotal();
                    // 店舗合計印字
                    $this->checkPageChange(2,$formvars);
                    $this->printStoreTotal();
                }
                else
                if($this->current_store_division != null && strcmp($this->current_store_division,$rec[1]) != 0)
                {
                    // 部門トータル印字
                    $this->checkPageChange(1,$formvars);
                    $this->printSectionTotal();
                }

                $this->checkPageChange(1,$formvars);
                $this->printLine($rec);

            }

            // 部門トータル印字
            $this->checkPageChange(1,$formvars);
            $this->printSectionTotal();
            // 店舗合計印字
            $this->checkPageChange(2,$formvars);
            $this->printStoreTotal();

            // さらに月のトータル印字
            // $this->checkPageChange(2,$formvars);
            // $this->printMonthlyTotal();

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
            $this->pdf->AddPage('P');
            $this->printHeader($formvars);
            $this->lines = 0;
            $this->current_y = 30;
            $this->current_store = null;  // force store name printed.
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
        //  合計
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,11)); //  仕入額

        // 月のトータルに加算。
        $this->material_monthly_total += $this->material_total;
        $this->monthly_total += $this->total;

        // トータルをリセット。
        $this->total = 0;

        // ２行分進めていることに注意。
        $this->current_y += $this->line_height*2;
    }

    /*
    *  合計を印字します。（部門のトータル）
    */

    function printSectionTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*部門合計*");

        //
        //  合計
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->section_total,11)); //  仕入額

        // 月のトータルに加算。
        $this->material_monthly_total += $this->material_total;
        $this->monthly_total += $this->section_total;

        // トータルをリセット。
        $this->section_total = 0;

        //
        $this->current_y += $this->line_height;
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

    function printLine($rec)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        //
        // 店舗名　部門　　日付　伝票番号　金額
        //
        // rec[0] => 店舗名
        // rec[1] => 部門
        // rec[2] => 日
        //    [3] => 伝票番号
        //    [4] => 金額

        if($this->current_store != $rec[0])
        {
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$rec[0]); //  店舗名
            $this->current_store = $rec[0];
            $this->current_store_division = null;
        }

        if($this->current_store_division != $rec[1])
        {
            $this->pdf->SetXY($this->x_pos[1],$this->current_y);
            $this->pdf->Write(20,$rec[1]); //  部門
            $this->current_store_division = $rec[1];
        }

        $rec[2] = ereg_replace('-','/',$rec[2]);

        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);

        $this->pdf->SetXY($this->x_pos[3]+5,$this->current_y);
        $this->pdf->Write(20,$rec[3]);

        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],11));
        //
        $this->total += $rec[4];
        $this->section_total += $rec[4];

        // Y位置の更新。
        $this->current_y += $this->line_height;

    }

    /*
    *  パラメータから日付を得る。
    */

    function parseParameter()
    {
        $parm = $_SERVER['QUERY_STRING'];
        $this->target_year = null;
        $this->target_month = null;

        if($parm != null)
        {
            if(preg_match('/year=(\d*)/',$parm,$matches))
            {
                $this->target_year = $matches[1];
            }

            if(preg_match('/month=(\d*)/',$parm,$matches))
            {
                $this->target_month = $matches[1];

            }
        }

        if($this->target_year != null && $this->target_month != null)
        {
            return true;
        }
        return false;
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