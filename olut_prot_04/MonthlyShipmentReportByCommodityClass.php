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
*    商品別月別出庫一覧 -  MonthlyShipmentReportByCommodityClass.php
*
*   Release History:
*    2005/09/30  ver 1.00.00 Initial Release
*    2005/10/05  ver 1.00.01 Added year selection screen.
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
class MonthlyShipmentReportByCommodity_SQL extends SQL
{
    function MonthlyShipmentReportByCommodity_SQL()
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
class MonthlyShipmentReportByCommodity_Smarty extends Smarty
{
    function MonthlyArrivalReportByCommodity_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

class MonthlyShipmentReportByCommodity extends OlutApp
{
    // 紙の位置関係定義。
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $left_margin  = 5.0;
    var $right_margin = 5.0;
    var $bottom_margin = 10.0;
    var $header_height = 25; // 15.0;
    var $cell_height   = 5;
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

    var $num_of_item_per_page = 19;     // １行に印字するデータの行数。(ヘダー1行除く）
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
    function MonthlyShipmentReportByCommodity()
    {
        $this->sql =& new MonthlyShipmentReportByCommodity_SQL;
        $this->tpl =& new MonthlyShipmentReportByCommodity_Smarty;
        
        //
        // 水平の線。
        //
        $this->x_pos = array(5,45,50,75,100,125,150,175,200,225,250,275,300,325,350,375,400);
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

        // 年間、１月から１２月までを指定。
        //
        $_query  = "select com_code, c.name, u.name, date_part('month',act_date), sum(-amount), sum(-total_price) ";
        $_query .= " from t_main t, m_commodity c, m_unit u";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and (act_flag='5' or act_flag='7')  ";
        $_query .= " and c.code = t.com_code";
        $_query .= " and c.unit_code = u.code";
        $_query .= " group by com_code, date_part('month', act_date), c.name, u.name ";
        $_query .= " order by com_code, date_part('month',act_date)";

        if($this->sql->query($_query,SQL_INIT)==false)
        {
            print $_query;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = 'データが存在しません';
            return false;
        }

        //
        // B4の紙サイズを指定しています。指定はポートレート。
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->AddPage('L');
        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        $this->pdf->SetTopMargin(0);
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');

        //
        $this->drawTitle();
        $this->drawHeader();
        // Y位置を設定。
        $this->current_y = $this->header_height;
        $this->total = array();

        $current_commodity = null;

        do
        {
            // 現在の商品？
            if($current_commodity != $this->sql->record[0])
            {
                // ページを替える？
                if($this->lines == $this->num_of_item_per_page)
                {
                    $this->changePage();
                    $this->drawTitle();
                    $this->drawHeader();
                    // Y位置を設定。
                    $this->current_y += $this->cell_height * 2;
                    $this->lines = 0;
                }
                else
                {
                    // Y位置の更新。
                    $this->current_y += $this->cell_height * 2;
                    $this->lines++;
                }
                // 商品名とコードを左端に出力
                $this->writeUpperCell(0,$this->sql->record[1]);  // name
                $this->writeLowerCell(0,$this->sql->record[0]);  // code
                $this->writeLowerCell(1,$this->sql->record[2]);  // 単位
                //
                $current_commodity = $this->sql->record[0];
            }

            for($month=1; $month<=12; $month++)
            {
                if($month == $this->sql->record[3])
                {
                    $index = $this->sql->record[3]+1;   // 月+1がインデックス値。

                    $this->writeUpperCell($index,number_format($this->sql->record[5]));     // 金額
                    $this->writeLowerCell($index,number_format($this->sql->record[4],2));   // 数量
                    
                    // 金額のみトータル必要。
                    $this->total[$month] += $this->sql->record[5];
                    
                    $rc = $this->sql->next();
                }
                else
                {
                    $index = $month + 1;
                    $this->writeUpperCell($index,'0');     // 金額
                    $this->writeLowerCell($index,'0.00');   // 数量
                }
            }
            //
            //
        } while($rc == true);
             
        $this->sql->disconnect();
                
        // 総合計。
        $this->printTotal();

        //
        $this->pdf->Output();
        return true;
    }
    
    /*
    *  印刷範囲入力画面のレンダリング
    */

    function renderScreen($formvars)
    {
        $this->tpl->assign('title','商品別 月別 出庫一覧');
        $this->tpl->assign('target_year_list',OlutApp::getTargetYearList());
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('YearSelectionForm.tpl');
    }
    /*
    *  ページ替えのチェックと実行。
    *  linesを初期設定したりインクリメントするので注意。
    *
    */

    function changePage()
    {
        $this->pdf->AddPage('L');
        $this->pdf->SetTopMargin(0);
        // Y位置を設定。
        $this->current_y = $this->header_height;

        $this->page_no++;
    }

    /*
    *  日付け関係の設定。
    */

    function setupDates($formvars)
    {
        // OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
        
        $this->target_year = $formvars['target_year'];
        $this->start_date = "$this->target_year/01/01";
        $this->end_date = "$this->target_year/12/31";
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

        $y = 10;
        $h = 10;
        //
        $this->pdf->SetFont(GOTHIC,'',14);
        $this->pdf->SetXY(130,$y);
        $this->pdf->Write($h," 商品 * 別月別出庫一覧 $this->target_year 年");
        $this->pdf->SetXY(280,$y);
        $this->pdf->Write($h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->pdf->SetXY(280,$y+5);
        $this->pdf->Write($h, "単位：　円",'',1,'R');
    }

    /*
    *   商品名など、ヘダーに1行だけ書く。
    */

    function writeHeaderCell($x_index,$title)
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
    *
    */

    function drawHeader()
    {
        //
        // 位置( $this->left_margin, $this->header_height ) からヘダーを書く。
        // このヘダーはループして書けるということは無いと思う。

        $this->writeHeaderCell(0,"商　品　名");

        for($i=1; $i<=12; $i++)
        {
            $this->writeHeaderCell($i+1," $i 月");
        }
        $this->pdf->line(5,30,350,30);
    }

    function printTotal()
    {
        // ページを替える？
        if($this->lines >= $this->num_of_item_per_page)
        {
            $this->changePage();
            $this->drawTitle();
            $this->drawHeader();

            $this->lines = 0;
        }
                  
        $this->current_y += $this->cell_height * 3;
                    
        $this->writeUpperCell(0,"総　合　計");


        for($month=1; $month<=12; $month++)
        {
            if(isset($this->total[$month]))
            {
                $index = $month+1;   // 月+1がインデックス値。
                $this->writeUpperCell($index,number_format($this->total[$month]));
            }
            else
            {
                $index = $month + 1;
                $this->writeUpperCell($index,'0');     // 金額
            }
        }
    }
    /*
    *   商品名など、ヘダーに1行だけ書く。
    */

    function writeUpperCell($x_index,$str)
    {
        $this->pdf->SetFont(GOTHIC,'',11);

        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        if($x_index > 1)
        {
            $len = $this->pdf->GetStringWidth($str);
            $offset = ($cell_width - $len) - 1;
        }
        else
        {
            $offset = 0;
        }

        $x = $this->x_pos[$x_index];
        $y = $this->current_y;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$str);
    }

    function writeLowerCell($x_index,$str)
    {
        $this->pdf->SetFont(GOTHIC,'',11);

        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        if($x_index > 1)
        {
            $len = $this->pdf->GetStringWidth($str);
            $offset = ($cell_width - $len) - 1;
        }
        else
        {
            $offset = 0;
        }

        $x = $this->x_pos[$x_index];
        $y = $this->current_y + $this->cell_height;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$str);
    }
}
?>