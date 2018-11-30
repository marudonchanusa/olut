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
 *    棚卸帳票印刷 - InventoryReportClass.php
 *
 *   Release History:
 *    2005/09/30  ver 1.00.00 Initial Release
 *    2005/10/05  ver 1.00.01 #1. Changed year list logic.  
 *                            #2. Refer target date from calendar master   
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
require_once('OlutAppLib.php');

// smarty configuration
class InventoryReport_Smarty extends Smarty
{
    function InventoryReport_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class InventoryReport_SQL extends SQL
{
    function InventoryReport_SQL()
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

class InventoryReport extends OlutApp
{
    var $sql;
    var $tpl;
    var $ship_section_code;
    var $ship_section_name;
    var $warehouse_code;
    var $warehouse_name;
    var $page_no;
    var $left_margin = 10;
    var $right_margin = 10;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_A4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_A4_HEIGHT; // paper size in portlait.(not landscape)
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $x_pos;
    var $error = null;
    var $target_year;
    var $target_month;
    var $number_of_lines_per_page = 43;

    function InventoryReport()
    {
        $this->sql =& new InventoryReport_SQL();
        $this->tpl =& new InventoryReport_Smarty();
    }

    function renderScreen($formvars)
    {
        // $this->tpl->assign('target_year_list',$this->getTargetYearList());
        // $this->tpl->assign('target_month_list',$this->getTargetMonthList());
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ShowErrorsForm.tpl');
    }

    function printOut($formvars)
    {
        $this->preparePDF();

        // 現在の日から処理するのは良くない。カレンダーマスター参照に修正。
        // $this->target_year  = date('Y');   // $formvars['target_year'];
        // $this->target_month = date('m');   // $formvars['target_month'];

        OlutApp::getCurrentProcessDate($this->sql,$this->target_year, $this->target_month);

        $target_last_date = OlutApp::getLastDate($this->target_year,$this->target_month);

        $date_from = "$this->target_year/$this->target_month/01";
        $date_to   = "$this->target_year/$this->target_month/$target_last_date";
        //
        $_query  = "select t.com_code,c.name,sum(t.amount),u.name,sum(t.total_price),";
        $_query .= " t.ship_sec_code, ss.name, t.warehouse_code, w.name";
        $_query .= " from t_main t, m_unit u, m_commodity c, m_shipment_section ss, m_warehouse w";
        $_query .= " where t.com_code=c.code and c.unit_code=u.code";
        $_query .= " and w.code=t.warehouse_code and ss.code=t.ship_sec_code";
        $_query .= " and t.act_date>='$date_from' and t.act_date<='$date_to'";
        $_query .= " group by t.com_code,c.name,u.name,t.ship_sec_code, t.warehouse_code,";
        $_query .= "  w.name, ss.name";
        $_query .= " order by t.com_code,t.ship_sec_code,t.warehouse_code";

        if(!$this->sql->query($_query,SQL_INIT))
        {
            // print $_query;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = "データが存在しません";
            return false;
        }

        // init.
        $this->page_no = 1;

        do
        {
            if((strcmp($this->ship_section_code,$this->sql->record[5]) != 0)
            ||(strcmp($this->warehouse_code,$this->sql->record[7]) != 0)
            ||($this->lines > $this->number_of_lines_per_page))
            {
                // ページ替えのトリガーを保存。
                $this->ship_section_code = $this->sql->record[5];
                $this->ship_section_name = $this->sql->record[6];
                $this->warehouse_code    = $this->sql->record[7];
                $this->warehouse_name    = $this->sql->record[8];
                $this->printHeader();
                //
                $this->page_no++;
            }
            $this->printLine();

        } while($this->sql->next());

        // write!
        $this->pdf->Output();

        return true;

    }

    function preparePDF()
    {
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));
        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
    }

    function printHeader()
    {
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        // 指定はポートレート。
        $this->pdf->AddPage('P');
        //
        $this->pdf->SetFont(GOTHIC,'U',16);
        $this->pdf->SetXY(80,18);
        $this->pdf->Write(10,"棚　卸　用　紙");

        $this->pdf->SetFont(GOTHIC,'',10);
        $this->pdf->SetXY(160,18);
        $this->pdf->Write(10,"$year 年 $month 月 $date 日");

        $this->pdf->SetFont(GOTHIC,'',10);
        // 資材内コードと名前
        $this->pdf->SetXY(10,25);
        $this->pdf->Write(10,"$this->ship_section_code $this->ship_section_name");

        // 倉庫コードと名前
        $this->pdf->SetXY(90,25);
        $this->pdf->Write(10,"$this->warehouse_code $this->warehouse_name");

        // ページ。

        $this->pdf->SetXY(180,25);
        $this->pdf->Write(10,"PAGE: $this->page_no");


        $this->pdf->SetXY(20,30);
        $this->pdf->Write(10,"商  品  名");

        $this->pdf->SetXY(80,30);
        $this->pdf->Write(10,"数　量");

        $this->pdf->SetXY(120,30);
        $this->pdf->Write(10,"単　位");

        $this->pdf->SetXY(140,30);
        $this->pdf->Write(10,"計算単価");

        $this->pdf->SetXY(170,30);
        $this->pdf->Write(10,"金　額");

        $this->pdf->Line(10,38,200,38);
        //

        $this->current_y = 40;
        $this->lines = 0;

    }

    function printLine()
    {
        // 商品名
        $code = $this->sql->record[0];
        $name = $this->sql->record[1];
        $this->pdf->SetXY(10,$this->current_y);
        $this->pdf->Write(10,"$code $name");

        //　数量 右そろえ。
        $amount = OlutApp::formatNumber($this->sql->record[2],10,2);
        $this->pdf->SetXY(80,$this->current_y);
        $this->pdf->Write(10,"$amount");

        // 数量入力欄

        $this->pdf->line(102,$this->current_y+7,118,$this->current_y+7);

        // 単位
        $unit_name = $this->sql->record[3];
        $this->pdf->SetXY(120,$this->current_y);
        $this->pdf->Write(10,"$unit_name");

        // 計算単価
        $total_price = $this->sql->record[4];
        if( $amount != 0)
        {
            $unit_price =  round($total_price/$amount,2);
        }
        else
        {
            $unit_price = 0;
        }
        $this->pdf->SetXY(140,$this->current_y);
        $unit_price = OlutApp::formatNumber($unit_price,11,2);
        $this->pdf->Write(10,$unit_price);


        // 金額
        $total_price = OlutApp::formatNumber($this->sql->record[4],11,0);
        $this->pdf->SetXY(170,$this->current_y);
        $this->pdf->Write(10,$total_price);

        $this->current_y += 5;
        $this->lines++;
    }

    function getTargetYearList()
    {
        $year = Date('Y');
        for($i=$year-4; $i<=$year; $i++)
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