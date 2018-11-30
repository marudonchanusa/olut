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
*    月間入出庫在庫表 -  ProfitAndLossAccordingToCommodityClass.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*    2005/10/24 ver 1.00.01 1.ページが１０以上だと次ページにかぶるバグを修正。
*                           2.日付けを正しく持ってこないために取引の無い商品
*                             を表示するバグを修正。
*    2005/10/25 ver 1.00.02 部署合計の後にページ替え。
*    2005/11/16 ver 1.00.03 年初の在庫を表示する不具合を修正。
*    2006/01/11 ver 1.00.04 減耗計算の＋!)が逆。
*
*/

//
//
//  注意：このソースの最後に空の行を置かないこと。PDF出力エラーになります。
//
//

//
// 日本語環境設定
//
require_once(OLUT_DIR . 'mbfpdf.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB
require_once('OlutAppLib.php');

// database configuration
class ProfitAndLossAccordingToCommodity_SQL extends SQL
{
    function ProfitAndLossAccordingToCommodity_SQL()
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
class ProfitAndLossAccordingToCommodity_Smarty extends Smarty
{
    function ProfitAndLossAccordingToCommodity_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

/*
*  印刷行情報を保持する。
*/

class LineInfo
{
    var $code;
    var $name;
    var $ship_sec_code;             // 資材内コード
    var $depletion_flag;            // 減耗フラグ
    var $ship_amount;
    var $ship_price;
    var $arrival_amount;
    var $arrival_price;

    var $last_inventory_amount;     // 前月在庫
    var $last_inventory_price;
    var $last_inventory_unit_price;

    var $inventory_amount;          // 当月在庫
    var $inventory_price;
    var $inventory_unit_price;

    var $discount_price;
    var $discount_amount;
    var $sample_price;
    var $sample_amount;
}


class ProfitAndLossAccordingToCommodity extends OlutApp
{
    // 紙の位置関係定義。
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $left_margin  = 5.0;
    var $right_margin = 5.0;
    var $bottom_margin = 10.0;
    var $header_height = 25; // 15.0;
    var $cell_height   = 0;
    var $cell_width    = 0;
    var $cell_margin   = 2.0;
    var $number_font_size = 9.0;
    var $ck_code = 0;

    // インスタンス定義
    var $tpl;
    var $sql;
    var $pdf;

    var $current_y;
    var $x_pos;

    //  キーは商品コード。
    var $line_info;

    var $num_of_item_per_page = 12;     // １行に印字するデータの行数。(ヘダー1行除く）
    var $section_total = array();       // 部門の合計。
    var $total = array();               // 仕入れの合計。
    var $material_total = 0;            // 材料費のトータル。（用度品を除く）
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $target_year;
    var $target_month;
    var $start_date;
    var $end_date;
    var $page_no = 1;
    var $current_ship_sec_code;         // 現在の資材内コード

    // ctor
    function ProfitAndLossAccordingToCommodity()
    {
        $this->sql =& new ProfitAndLossAccordingToCommodity_SQL;
        $this->tpl =& new ProfitAndLossAccordingToCommodity_Smarty;


        // 項目印字位置設定。
        // LAND SCAPE 設定
        $paper_width  = $this->paper_height;
        $paper_height = $this->paper_width;

        //
        // 水平の線。
        //
        $width = $paper_width - $this->right_margin - $this->left_margin;
        $x_delta = $width  / 12;

        $this->x_pos = array(5,44.5,64,96.5,135,175,200,220,250,280,310,340,359);
    }

    /*
    *  印字のメイン
    */
    function printOut($formvars)
    {
        //
        //  集計範囲の日付けを計算。
        //
        $this->setupDates($formvars);

        // 集計開始。

        if(!$this->getInventoriesOfLastMonth())
        {
            print "error inventory sql";
            return;
        }

        if(!$this->getShipments())
        {
            print "error shioments sql";    // make error smarty template @later.
            return;
        }
        if(!$this->getArrivals())
        {
            print "error arrivals sql";
            return;
        }

        if(!$this->getSampleShipments())
        {
            print "sample error";
            return;
        }

        if(!$this->getDiscounts())
        {
            print "error discount sql";
            return;
        }

        if(!$this->getInventories())
        {
            print "error inventory sql";
            return;
        }

        // 商品名などフラグ類をセット
        $this->getCommodityInfo();

        // 集計方法の違うCKを得る。
        $this->getCKCode();

        //
        //  縦方向は「商品」
        //

        //
        // B4の紙サイズを指定しています。指定はポートレート。
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->AddPage('L');
        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        $this->pdf->SetTopMargin(0);
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');

        if(isset($this->lines))
        {
            //
            $this->drawTitle();
            $this->drawMesh();
            $this->drawHeader();

            // Y位置を設定。
            $this->current_y = $this->header_height;
            $this->current_y += $this->cell_height;

            if($this->line_info != null)
            {

                // sort line info??
                ksort($this->line_info);

                $this->lines = 0;
                // 結果を印字するループ。
                foreach($this->line_info as $line)
                {
                    //
                    if($this->current_ship_sec_code==null)
                    {
                        $this->current_ship_sec_code = $line->ship_sec_code;
                    }

                    // 資材内が変わった？
                    if($this->current_ship_sec_code != $line->ship_sec_code)
                    {
                        $this->checkPageChange(1);
                        $this->printSectionTotal();
                        $this->current_y += $this->cell_height;
                        $this->current_ship_sec_code = $line->ship_sec_code;
                        
                        // added 2005/10/25
                        $this->forcePageChange();
                    }

                    $this->checkPageChange(1);
                    $this->printLine($line);
                    $this->current_y += $this->cell_height;
                }

                // 部署合計を印刷。
                $this->checkPageChange(1);
                $this->printSectionTotal();
                $this->current_y += $this->cell_height;

                //
                // 総合計を印刷。
                //
                $this->checkPageChange(1);
                $this->printTotal();
            }

            $this->pdf->Output();
        }
        else
        {
            print "no data";
        }

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
        $this->tpl->display('ProfitAndLossAccordingToCommodityForm.tpl');
    }
    
    /*
     *  ページ替えのチェックと実行。
     *  linesを初期設定したりインクリメントするので注意。
     *
     */
    function checkPageChange($lines_to_add)
    {
        if(($this->lines+$lines_to_add) > $this->num_of_item_per_page)
        {
            $this->forcePageChange();
            $this->lines = 1;

        }
        else {
            $this->lines += $lines_to_add;
        }
    }
    
    /*
     *  強制ページ替え
     */
    function forcePageChange()
    {
        $this->pdf->AddPage('L');
        $this->pdf->SetTopMargin(0);
        // Y位置を設定。
        $this->current_y = $this->header_height;

        //
        $this->drawTitle();
        $this->drawMesh();
        $this->drawHeader();

        $this->current_y += $this->cell_height;
        $this->lines = 0;        
    }

    /*
    *  日付け関係の設定。
    */

    function setupDates($formvars)
    {
        // 以下は画面入力になったので削除。
        //OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
        //$this->target_year = date('Y');
        //$this->target_month = "06"; // date('m');
        
        $this->target_year  = $formvars['target_year'];
        $this->target_month = $formvars['target_month'];
        
        // 0 の補正が必要でした。2005/10/22
        $this->start_date = sprintf("%04d/%02d/01",$this->target_year,$this->target_month);
        $last = OlutApp::getLastDate($this->target_year,$this->target_month);
        $this->end_date   = sprintf("%04d/%02d/%02d",$this->target_year,$this->target_month,$last);
    }

    /*
    *
    */
    function drawTitle()
    {
        // 印刷日時を得る。
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        $x = 0;
        $y = 10;

        $h = 10;
        //
        $this->pdf->SetFont(GOTHIC,'',14);
        $this->pdf->SetXY(130,$y);
        $this->pdf->Write($h,"$this->target_year 年 $this->target_month 月　月間入出庫在庫表");
        $this->pdf->SetXY(280,$y);
        $this->pdf->Write($h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;

    }

    /*
    *   商品名など、ヘダーに1行だけ書く。
    */

    function WriteHeaderCell($x_index,$title)
    {
        $this->pdf->SetFont(GOTHIC,'',12);
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$title);
    }

    /*
    *   商品回転率専用。
    */

    function WriteHeaderDoubleLineCell($x_index,$title1,$title2)
    {
        $this->pdf->SetFont(GOTHIC,'',12);
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $h = $this->cell_height/2;

        $len = $this->pdf->GetStringWidth($title1);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$title1);

        $y += $h;
        $len = $this->pdf->GetStringWidth($title2);
        $offset = ($cell_width - $len)/2 - 1;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$title2);

    }
    /*
    * 金額のと単価表示のヘダーセル。縦3分割。中2分割。下の文言は固定で、「数量、単価(円）、金額(円）
    *
    */
    function WriteHeaderMoneyAndUnitPriceCell($x_index,$title)
    {
        // セルの高さを3分割。
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x,$y+$ch);
        $this->pdf->Write($ch,"   数量");

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x+$cell_width/2,$y+$ch);
        $this->pdf->Write($ch," 単価(円)");

        $this->pdf->line($x,$y+$ch*2,$x+$cell_width,$y+$ch*2);

        $str = "金　額(円)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch*2);
        $this->pdf->Write($ch,$str);

        // 縦のライン
        $x += $cell_width/2;
        $this->pdf->line($x, $y+$ch, $x, $y+$ch*2);

    }
    /*
    *　　金額だが、単価の無いセル。3段。縦のラインなし。
    *
    */
    function WriteHeaderMoneyCell($x_index,$title)
    {
        // セルの高さを3分割。
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $str = "数量";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x+$offset,$y+$ch);
        $this->pdf->Write($ch,$str);

        $this->pdf->line($x,$y+$ch*2,$x+$cell_width,$y+$ch*2);

        $str = "金　額(円)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch*2);
        $this->pdf->Write($ch,$str);

    }

    /*
    *　　当月差益のセル。3段。縦のラインなし。
    *
    */
    function WriteHeaderBalanceCell($x_index,$title)
    {
        // セルの高さを3分割。
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $str = " 1個当たり(円)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x+$offset,$y+$ch);
        $this->pdf->Write($ch,$str);

        $this->pdf->line($x,$y+$ch*2,$x+$cell_width,$y+$ch*2);

        $str = "金　額(円)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch*2);
        $this->pdf->Write($ch,$str);

    }
    /*
    *　　2段、サンプルのセル。
    *
    */
    function WriteHeaderDiscountCell($x_index,$title)
    {
        // セルの高さを3分割。
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $str = "金 額(円)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch);
        $this->pdf->Write($ch*2,$str);
    }

    /*
    *
    */

    function drawHeader()
    {
        //
        // 位置( $this->left_margin, $this->header_height ) からヘダーを書く。
        // このヘダーはループして書けるということは無いと思う。

        $this->WriteHeaderCell(0,"商品名");
        $this->WriteHeaderCell(1,"単位名");
        $this->WriteHeaderMoneyAndUnitPriceCell(2,"前月繰越高");
        $this->WriteHeaderMoneyAndUnitPriceCell(3,"当月入庫高");
        $this->WriteHeaderMoneyAndUnitPriceCell(4,"当月出庫高");
        $this->WriteHeaderMoneyCell(5,"当月サンプル");
        $this->WriteHeaderDiscountCell(6,"当月値引");
        $this->WriteHeaderMoneyAndUnitPriceCell(7,"当月在庫高");
        $this->WriteHeaderMoneyCell(8,"当月減耗");
        $this->WriteHeaderMoneyAndUnitPriceCell(9,"翌月繰越");
        $this->WriteHeaderBalanceCell(10,"当月差益");
        $this->WriteHeaderDoubleLineCell(11,"商品","回転率");
    }

    /*
    *　　横12セル、縦15セル。（最上段はヘダー）
    */

    function drawMesh()
    {
        // LAND SCAPE 設定
        $paper_width  = $this->paper_height;
        $paper_height = $this->paper_width;

        //
        // 水平の線。
        //
        $width = $paper_width - $this->right_margin - $this->left_margin;
        $y = $this->header_height;
        $cell_height = ($paper_height - $this->header_height - $this->bottom_margin )/14.0;

        for($i=0; $i<14; $i++)
        {
            // 線を引く
            $this->pdf->line($this->left_margin,$y,$width+$this->left_margin,$y);
            $y += $cell_height;
        }

        // 垂直の線
        $y = $this->header_height;
        $x_delta = $width  / 12;
        $height  = $cell_height*14.0 + 9.0 ;  // +4.0 桁落ち？

        for($i=0; $i<13; $i++)
        {
            $x = $this->x_pos[$i];
            $this->pdf->line($x,$y,$x,$height);
        }

        // クラス変数に戻す。
        $this->cell_height = $cell_height;
        $this->cell_width  = $x_delta;
    }


    /*
    *   商品名など、ヘダーに1行だけ書く。
    */

    function WriteCell($x_index,$title)
    {
        $this->pdf->SetFont(GOTHIC,'',12);

        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->current_y;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$title);
    }

    /*
    *  在庫のセルを書く。(前月繰越高、当月在庫高）
    */
    function WriteInventoryCell($index,$amount,$unit_price,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        // セルを上下に割る。
        $h = $this->cell_height /2;

        // 上の左に数量。
        $x = $this->x_pos[$index] ;
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$formatted_amount");
        $offset = ($cell_width /2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$formatted_amount);

        $unit_price = number_format($unit_price,2);
        //
        $x = $this->x_pos[$index] + $cell_width/2;
        $len = $this->pdf->GetStringWidth("$unit_price");
        $offset = ($cell_width/2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$unit_price);

        // 金額を下に右揃え。
        $price = number_format($price);
        $x = $this->x_pos[$index];
        $y = $this->current_y + $h;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  金額、数量のセルを書く。(前月繰越高、入庫高、出庫高用）
    */
    function WriteMoneyAndUnitPriceCell($index,$amount,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        // セルを上下に割る。
        $h = $this->cell_height / 2;

        // 上の左に数量。
        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$formatted_amount");
        $offset = ($cell_width/2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$formatted_amount);

        // 上の右に平均単価。ここで計算していいものか？
        if($amount != 0)
        {
            $unit_price = round($price / $amount,2);
            $unit_price = number_format($unit_price,2);
        }
        else
        {
            $unit_price = 0;
        }
        //
        $x = $this->x_pos[$index] + $cell_width/2;
        $len = $this->pdf->GetStringWidth("$unit_price");
        $offset = ($cell_width/2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$unit_price);

        // 金額を下に右揃え。
        $price = number_format($price);
        $x = $this->x_pos[$index] ;
        $y = $this->current_y + $h;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  単価のない金額のセルを書く。（サンプル出荷、）
    */
    function WriteMoneyAndAmountCell($index,$amount,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        // セルを上下に割る。
        $h = $this->cell_height / 2;

        // 上の左に数量。
        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$formatted_amount");
        $offset = ($cell_width - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$formatted_amount);


        // 金額を下に右揃え。
        $price = number_format($price);
        $x = $this->x_pos[$index];
        $y = $this->current_y + $h;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  金額のみのセルを書く。（値引き）
    */
    function WriteMoneyCell($index,$amount,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        $h = $this->cell_height;

        // 金額を下に右揃え。
        $price = number_format($price);
        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin - 2 ;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  数値のみのセルを書く。（商品回転率）
    */
    function WriteNumberCell($index,$value)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $h = $this->cell_height;

        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$value");
        $offset = $cell_width - $len - $this->cell_margin-2;  // special -2 ?

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$value);
    }

    /*
    *  トータル行の数値のみのセルを書く。
    */
    function WriteTotalCell($index,$value)
    {
        $value = number_format($value,0);

        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $h = $this->cell_height;

        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$value");
        $offset = $cell_width - $len - $this->cell_margin;  // special -2 ?

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$value);
    }
    /*
    *  月の出荷を得る。
    */
    function getShipments()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and (act_flag='5' or act_flag='7')  ";
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            //  $line_info = new LineInfo();
            //
            $line_info->code        = $rec[0];
            $line_info->ship_amount = $rec[1]*(-1);
            $line_info->ship_price  = $rec[2]*(-1);

            $this->line_info[$rec[0]] = $line_info;
        }

        return true;
    }


    /*
    *  入荷を得る。
    */
    function getArrivals()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and (act_flag='1' or act_flag='3')  ";    // 値引きは除く。
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code           = $rec[0];
            $line_info->arrival_amount = $rec[1];
            $line_info->arrival_price  = $rec[2];

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }


    function getInventoriesOfLastMonth()
    {
        //
        //  現在の仕様では当月の１日に棚卸データがある。
        //

        $_query = "select com_code, amount, total_price, unit_price from t_main ";
        $_query .= " where act_date = '$this->start_date' ";
        $_query .= " and act_flag='0' ";
        $_query .= " order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code             = $rec[0];
            $line_info->last_inventory_amount = $rec[1];
            $line_info->last_inventory_price  = $rec[2];
            $line_info->last_inventory_unit_price = $rec[3];

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }
    /*
    * 当月在庫を得る。
    */

    function getInventories()
    {
        //
        //  現在の仕様では来月の１日に棚卸データがある。
        //
        
        // 2005/11/16 fix.  月のsubstrの開始が6 -> 5に訂正。
        
        $inventory_date = OlutApp::getFirstDayOfNextMonth(substr($this->start_date,0,4),substr($this->start_date,5,2));

        $_query  = "select com_code, amount, total_price, unit_price from t_main ";
        $_query .= " where act_date = '$inventory_date' ";
        $_query .= " and act_flag='0' ";
        $_query .= " order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code             = $rec[0];
            $line_info->inventory_amount = $rec[1];
            $line_info->inventory_price  = $rec[2];
            $line_info->inventory_unit_price = $rec[3];

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }

    /*
    *  サンプル出荷の照会
    */
    function getSampleShipments()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and act_flag='6' ";
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code          = $rec[0];
            $line_info->sample_amount = $rec[1]*(-1);
            $line_info->sample_price  = $rec[2]*(-1);

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }


    /*
    * 値引きの照会
    */
    function getDiscounts()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and act_flag='2' ";
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code            = $rec[0];
            $line_info->discount_amount = $rec[1]*(-1);
            $line_info->discount_price  = $rec[2]*(-1);

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;


    }
    /*
    *  商品コードから商品名を得る。
    */

    function getCommodityName($code,&$name,&$ship_sec_code,&$depletion_flag)
    {
        $_query = "select name, ship_section_code, depletion_flag from m_commodity where code='$code' and deleted is null";
        if($this->sql->query($_query,SQL_INIT)==false)
        {
            return false;
        }

        //

        $name          = $this->sql->record[0];
        $ship_sec_code = $this->sql->record[1];

        if($this->sql->record[2]==null)
        {
            $depletion_flag = '0';
        }
        else
        {
            $depletion_flag = $this->sql->record[2];
        }

        return true;
    }

    /*
    *  商品コードから単位名を得る。
    */
    function getUnitName($code)
    {
        $_query = "select u.name from m_unit u, m_commodity c where c.unit_code=u.code and c.code='$code' and c.deleted is null";
        if($this->sql->query($_query,SQL_INIT)==false)
        {
            return null;
        }
        return $this->sql->record[0];
    }

    /*
    *   １行印字。
    */

    function printLine($line)
    {
        // 商品コードと商品名を印字。

        $h = $this->cell_height / 2;
        $x = $this->left_margin;
        $y = $this->current_y;

        // 左詰めでコードを書く。
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->pdf->SetXY($x,$y);
        $this->pdf->Write($h,$line->code);

        // 同じく商品名。
        $this->pdf->SetXY($x,$y+$h);
        $this->pdf->Write($h,$line->name);

        // 単位。
        $unit = $this->getUnitName($line->code);
        $this->WriteCell(1,$unit);

        // 前月繰越高
        $this->WriteInventoryCell(2,$line->last_inventory_amount,$line->last_inventory_unit_price, $line->last_inventory_price);
        $this->section_total[0] += $line->last_inventory_price;

        // 当月入庫高
        $this->WriteMoneyAndUnitPriceCell(3,$line->arrival_amount,$line->arrival_price);
        $this->section_total[1] += $line->arrival_price;

        // 当月出庫高
        $this->WriteMoneyAndUnitPriceCell(4,$line->ship_amount,$line->ship_price);
        $this->section_total[2] += $line->ship_price;

        // サンプル
        $this->WriteMoneyAndAmountCell(5,$line->sample_amount, $line->sample_price);
        $this->section_total[3] += $line->sample_price;

        // 値引き
        $this->WriteMoneyCell(6,$line->discount_amount, $line->discount_price);
        $this->section_total[4] += $line->discount_price;

        //if($line->code=='17111')
        //{
           // print "xx";
          // $line->code = '17111';
        //}
        //
        // 当月在庫高  (= 計算在庫)。
        //

        if($line->ship_sec_code == $this->ck_code || $line->depletion_flag=='1')
        {
            //
            // セントラルキッチンの食材または雑商品なので、計算在庫＝当月棚卸在庫　とする。(減耗は常にゼロ）
            //
            // 減耗フラグセット対象となる商品コード：
            //
            //  材料 　　"50002"  "60002"  "70002"  "75002".
            //  雑商品 　"29800"  "39800"  "49800"  "89800".
            //
            $inventory_amount = $line->inventory_amount;
            $inventory_price  = $line->inventory_price;

            if($inventory_amount != 0)
            {
                $avg_unit_price = round($inventory_price / $inventory_amount);
            }
            else {
                $avg_unit_price = 0;
            }
        }
        else
        {

            //
            // 入荷と在庫から平均単価を求める
            //
            if(($line->last_inventory_amount + $line->arrival_amount) != 0)
            {
                $avg_unit_price = round(($line->last_inventory_price + $line->arrival_price)/($line->last_inventory_amount + $line->arrival_amount),2);
                $avg = ($line->last_inventory_price + $line->arrival_price)/($line->last_inventory_amount + $line->arrival_amount);
            }
            else
            {
                $avg_unit_price = $avg = 0;
            }

            $inventory_amount = $line->last_inventory_amount + $line->arrival_amount - $line->ship_amount - $line->sample_amount;
            $inventory_price  = round($inventory_amount * $avg,0);

            // 以下は N.G.
            // $inventory_price  = $line->last_inventory_price + $line->arrival_price - $line->ship_price - $line->sample_price - $line->discount_price;
            //
        }

        //if($line->code=='85160')
        //{
        //    print "xx";
        // }

        $this->WriteInventoryCell(7,$inventory_amount, $avg_unit_price, $inventory_price);
        $this->section_total[5] += $inventory_price;

        // 減耗　＝　計算在庫　-　実在庫　
        // 
        // 2006/1/11 以下のコードを逆にしました。
        // $loss_amount = $inventory_amount - $line->inventory_amount;
        // $loss_price  = $inventory_price  - $line->inventory_price;
        $loss_amount = $line->inventory_amount - $inventory_amount;
        $loss_price  = $line->inventory_price  - $inventory_price;
        //
        $this->WriteMoneyAndAmountCell(8,$loss_amount,$loss_price);
        $this->section_total[6] += $loss_price;


        // 翌月繰越 = 当月入力在庫
        $this->WriteInventoryCell(9,$line->inventory_amount, $line->inventory_unit_price, $line->inventory_price);
        $this->section_total[7] += $line->inventory_price;

        // 当月差益＝荷渡勘定　- ( 前月繰越高　＋　当月仕入高 - 当月棚卸　）＋商品見本　-　破損等（減耗）
        $balance_amount = $line->ship_amount - ( $line->last_inventory_amount + $line->arrival_amount - $line->inventory_amount) + $line->sample_amount - $loss_amount;
        $balance_price  = $line->ship_price  - ( $line->last_inventory_price  + $line->arrival_price  - $line->inventory_price)  + $line->sample_price  - $loss_price;
        $this->WriteMoneyAndAmountCell(10,$balance_amount,$balance_price);
        $this->section_total[8] += $balance_price;

        // >    - 「商品回転率」の計算方法をお教えください。
        // ＠小数点第一位未満を四捨五入（出荷件数　＊　１００/　在庫件数　）　です
        $rate = 0;
        if( $line->inventory_amount != 0)
        {
            $rate = round($line->ship_amount * 100 / $line->inventory_amount,1);
        }
        $this->WriteNumberCell(11,number_format($rate,2));
    }

    /*
    *  部署合計を印字。
    */

    function printSectionTotal()
    {
        // 商品コードと商品名を印字。
        $h = $this->cell_height / 2;
        $x = $this->left_margin;
        $y = $this->current_y;

        $this->pdf->SetFont(GOTHIC,'',12);

        // 同じく商品名。
        $this->pdf->SetXY($x,$y+$h/2);
        $this->pdf->Write($h,"部  署  合  計");

        // 前月繰越高
        $this->WriteTotalCell(2,$this->section_total[0]);

        // 当月入庫高
        $this->WriteTotalCell(3,$this->section_total[1]);

        // 当月出庫高
        $this->WriteTotalCell(4,$this->section_total[2]);

        // サンプル
        $this->WriteTotalCell(5,$this->section_total[3]);

        // 値引き
        $this->WriteTotalCell(6,$this->section_total[4]);

        $this->WriteTotalCell(7,$this->section_total[5]);

        $this->WriteTotalCell(8,$this->section_total[6]);

        // 翌月繰越 = 当月入力在庫
        $this->WriteTotalCell(9,$this->section_total[7]);

        // 当月差益＝
        $this->WriteTotalCell(10,$this->section_total[8]);

        // 全体のトータルに足す。
        foreach($this->section_total as $key => $value)
        {
            $this->total[$key] += $value;
            $this->section_total[$key] = 0;    // reset.
        }

    }

    /*
    *  総合計を印刷
    */

    function printTotal()
    {
        // 商品コードと商品名を印字。
        $h = $this->cell_height / 2;
        $x = $this->left_margin;
        $y = $this->current_y;

        $this->pdf->SetFont(GOTHIC,'',12);

        // 同じく商品名。
        $this->pdf->SetXY($x,$y+$h/2);
        $this->pdf->Write($h,"総  合  計");

        // 前月繰越高
        $this->WriteTotalCell(2,$this->total[0]);

        // 当月入庫高
        $this->WriteTotalCell(3,$this->total[1]);

        // 当月出庫高
        $this->WriteTotalCell(4,$this->total[2]);

        // サンプル
        $this->WriteTotalCell(5,$this->total[3]);

        // 値引き
        $this->WriteTotalCell(6,$this->total[4]);

        $this->WriteTotalCell(7,$this->total[5]);

        $this->WriteTotalCell(8,$this->total[6]);

        // 翌月繰越 = 当月入力在庫
        $this->WriteTotalCell(9,$this->total[7]);

        // 当月差益＝
        $this->WriteTotalCell(10,$this->total[8]);
    }

    function getCKCode()
    {
        $this->sql->query("select code from m_shipment_section where ck_flag<>'0'",SQL_INIT);
        $this->ck_code = $this->sql->record[0];
    }

    function getCommodityInfo()
    {
        foreach($this->line_info as $key => $line)
        {
            $this->getCommodityName($line->code,&$this->line_info[$key]->name,
            &$this->line_info[$key]->ship_sec_code,
            &$this->line_info[$key]->depletion_flag);
        }
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
            // if($i==date('m'))
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