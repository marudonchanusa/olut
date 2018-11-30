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
 *    請求書印刷 - InvoicePrintClass.php
 *
 *   Release History:
 *    2005/09/30  ver 1.00.00 Initial Release
 *    2005/10/05  ver 1.00.01 Changed year list logic.  
 *    2005/10/08  ver 1.00.02 Print total at the last line.
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
require_once('OlutAppLib.php');
require_once('SystemProfile.php');


// smarty configuration
class InvoicePrint_Smarty extends Smarty
{
    function InvoicePrint_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class InvoicePrint_SQL extends SQL
{
    function InvoicePrint_SQL()
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

class InvoicePrint extends OlutApp
{
    var $sql;
    var $tpl;
    var $pdf;
    var $page_no = 1;
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
    var $date_from;
    var $date_to;
    var $store_code_from;
    var $store_code_to;
    var $store_name;
    var $number_of_lines_per_page = 21;
    var $x_pos = array(10,25,40,90,110,130,150,175,200);

    function InvoicePrint()
    {
        $this->sql =& new InvoicePrint_SQL();
        $this->tpl =& new InvoicePrint_Smarty();
    }


    /*
    *  印刷範囲指定画面を表示
    */

    function renderScreen($formvars)
    {
        $this->tpl->assign('target_year_list',$this->getTargetYearList());
        $this->tpl->assign('target_month_list',$this->getTargetMonthList());
        $this->tpl->assign('code_from_list', $this->getStoreList($formvars['code_from']));
        $this->tpl->assign('code_to_list',   $this->getStoreList($formvars['code_to']));

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('InvoicePrintForm.tpl');
    }

    /*
    *  印刷メイン
    */

    function printOut($formvars)
    {
        $this->preparePDF();

        $year = $formvars['target_year'];
        $month = $formvars['target_month'];
        $last  = OlutApp::getLastDate($year,$month);

        $this->date_from = "$year/$month/01";
        $this->date_to   = "$year/$month/$last";


        $this->store_code_from = $formvars['code_from'];
        $this->store_code_to   = $formvars['code_to'];

        // perform query
        $_query = "select t.act_date, t.com_code, c.name, t.amount, u.name, t.unit_price, t.total_price, t.memo, s.name";
        $_query .= " from t_main t, m_unit u, m_commodity c, m_store s";
        $_query .= " where c.code=t.com_code and c.unit_code=u.code and t.dest_code=s.code";
        $_query .= " and (t.act_flag='5' or t.act_flag='6' or t.act_flag='7')";
        $_query .= " and t.act_date>='$this->date_from' and t.act_date<='$this->date_to'";

        if(strlen($this->store_code_to)==0 && strlen($this->store_code_from) > 0)
        {
            $_query .= " and t.dest_code='$this->store_code_from'";
        }
        else
        if(strlen($this->store_code_to)>0 && strlen($this->store_code_from)==0)
        {
            $_query .= " and t.dest_code<='$this->store_code_from'";
        }
        else
        if(strlen($this->store_code_to)>0 && strlen($this->store_code_from)>0)
        {
            // 範囲で指定。
            $_query .= " and t.dest_code>='$this->store_code_from' and t.dest_code<='$this->store_code_to'";
        }

        //
        $_query .= "order by t.dest_code, t.act_date, t.com_code";

        if(!$this->sql->query($_query,SQL_ALL))
        {
            $this->error = $this->sql->error;
            print $_query;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = "データがありません";
            return false;
        }
        //
        //  税抜き金額の合計はヘダーに印字するので先に求めておく。
        //

        $current_store = $this->sql->record[0][8];
        $total = 0;

        foreach($this->sql->record as $rec)
        {
            if(strcmp($current_store,$rec[8]) != 0)
            {
                $totals[] = $total;
                $total    = 0;
                $current_store = $rec[8];
            }
            $total += $rec[6]*(-1);
        }
        $totals[] = $total;


        // ここから明細の印字。
        $total_index      = 1;
        $this->lines      = 0;
        $this->page_no    = 1;
        $this->total      = $totals[0];
        $this->store_name = $current_store = $this->sql->record[0][8];
        $this->printHeader();

        foreach($this->sql->record as $rec)
        {
            // 店舗が変わった？
            if(strcmp($current_store,$rec[8]) != 0)
            {
                $this->printLineTotal();
                
                $this->total = $totals[$total_index++];
                $this->lines = 0;
                $this->page_no = 1;
                $this->store_name = $current_store = $rec[8];
                $this->printHeader();
            }
            $this->printLine($rec);
            $this->lines++;

            // 同じ店舗（＝取引先）でページ替え。
            if($this->lines >= $this->number_of_lines_per_page)
            {
                $this->printHeader();
                $this->lines = 0;
            }
        }
        $this->printLineTotal();
        $this->pdf->Output();
        return true;
    }

    /*
    *
    */
    function preparePDF()
    {
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));
        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
    }

    /*
    *
    */
    function printHeader()
    {
        $system_profile =& new SystemProfile();

        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        // 指定はポートレート。
        $this->pdf->AddPage('P');
        //
        $this->pdf->SetFont(GOTHIC,'',18);
        $this->pdf->SetXY(10,10);
        $this->pdf->Write(10,"御請求書");

        $this->pdf->SetFont(GOTHIC,'',10);
        $this->pdf->SetXY(160,10);
        $this->pdf->Write(10,"$year 年 $month 月 $date 日");

        // ページ番号
        $this->pdf->SetXY(179,15);
        $this->pdf->Write(10,"PAGE: $this->page_no");

        // 宛先
        // $store_name = trim($this->store_name);  // no mb_trim??
        $store_name = mb_ereg_replace("[　]*$",'',$this->store_name);
        $this->pdf->SetFont(GOTHIC,'U',15);
        $this->pdf->SetXY(10,20);
        $this->pdf->Write(10,"$store_name 御中 ");  // need trailing space to fix fpdf bug.

        // 会社名と住所
        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY(102,30);
        // $this->pdf->Write(10,"(株)エヌティー・トレーディング・コーポレーション");
        $this->pdf->Write(10,$system_profile->company_name);

        $this->pdf->SetXY(125,38);
        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->Write(10,$system_profile->company_address_1);
        // "〒169-8530 東京都新宿区百人町 2-25-13");
        $this->pdf->SetXY(125,43);
        // $this->pdf->Write(10,"TEL: 03-3369-9100 / FAX: 03-5389-3003");
        $this->pdf->Write(10,$system_profile->company_address_2);

        // 金額は最初のページだけ。
        if($this->page_no == 1)
        {
            // 金額のボックス。
            $this->pdf->line(10,30,100,30);      // 水平
            $this->pdf->line(10,40,100,40);
            $this->pdf->line(10,50,100,50);

            $this->pdf->line(10,30,10,50);
            $this->pdf->line(40,30,40,50);
            $this->pdf->line(70,30,70,50);
            $this->pdf->line(100,30,100,50);

            $this->pdf->SetFont(GOTHIC,'',12);
            $this->pdf->SetXY(16,30);
            $this->pdf->Write(10,"税抜金額");
            $this->pdf->SetXY(48,30);
            $this->pdf->Write(10,"消費税");
            $this->pdf->SetXY(79,30);
            $this->pdf->Write(10,"総合計");

            $total = OlutApp::formatNumber($this->total,11,0);
            $this->pdf->SetXY(14,40);
            $this->pdf->Write(10,$total);

            // 消費税
            $tax   = floor($this->total * $system_profile->tax);  // 0.05;
            $grand_total = $this->total + $tax;

            $tax         = OlutApp::formatNumber($tax,11,0);
            $grand_total = OlutApp::formatNumber($grand_total,11,0);

            $this->pdf->SetXY(42,40);
            $this->pdf->Write(10,$tax);

            $this->pdf->SetXY(72,40);
            $this->pdf->Write(10,$grand_total);

        }

        // 明細のメッシュ

        $w = $this->paper_width - $this->left_margin;
        $y = 53;

        for($i=0; $i<=$this->number_of_lines_per_page+1; $i++)
        {
            $this->pdf->line($this->left_margin,$y,$w,$y);
            $y += 10;
        }

        $h = ($this->number_of_lines_per_page+1)*10;

        for($i=0; $i<9; $i++)
        {
            $this->pdf->line($this->x_pos[$i],53,$this->x_pos[$i],53+$h);
        }

        // 明細のヘダー
        $hdr = array("日付","コード","商品名","数量","単位","単価","税抜金額","摘要");
        $this->pdf->SetFont(GOTHIC,'',11);

        for($i=0; $i<9; $i++)
        {
            $len = $this->pdf->GetStringWidth($hdr[$i]);
            $w   = $this->x_pos[$i+1] - $this->x_pos[$i];
            $offset = ($w-$len)/2-1;
            $this->pdf->SetXY($this->x_pos[$i]+$offset,54);
            $this->pdf->Write(10,$hdr[$i]);
        }

        $this->page_no++;
        $this->current_y = 64;

    }

    function printLine($rec)
    {
        // yyyy/mm/dd
        // 0123456789
        $dt   = substr($rec[0],5,2) . '/' . substr($rec[0],8,2);
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(10,$dt);      // 日付け。

        // 商品コード
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(10,$rec[1]);

        // 商品名
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(10,$rec[2]);

        // 数量
        $amount = OlutApp::formatNumber($rec[3]*(-1),10,2);
        $this->pdf->SetXY($this->x_pos[3]-3,$this->current_y);
        $this->pdf->Write(10,$amount);

        // 単位
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(10,$rec[4]);

        // 単価
        $unit_price = OlutApp::formatNumber($rec[5],10,0);
        $this->pdf->SetXY($this->x_pos[5]-3,$this->current_y);
        $this->pdf->Write(10,$unit_price);

        // 税抜き金額
        $total_price = OlutApp::formatNumber($rec[6]*(-1),11,0);
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(10,$total_price);

        // 摘要
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(10,$rec[7]);

        $this->current_y += 10;

    }
    
    function printLineTotal()
    {
        if($this->lines >= $this->number_of_lines_per_page)
        {
           $this->printHeader();
           $this->lines = 0;
        }
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(10,"税抜合計");
        //
        //
        //
        $total_price = OlutApp::formatNumber($this->total,11,0);
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(10,$total_price);    
    }

    function getStoreList($current_code)
    {
        $result = '';
        $_query = "select code,name from m_store where print_flag='1' order by code";
        $this->sql->query($_query,SQL_ALL);
        if($this->sql->record != null)
        {
            foreach($this->sql->record as $rec)
            {
                if(strcmp($current_code,$rec[0])==0)
                {
                    $sel = 'selected';
                }
                else
                {
                    $sel = '';
                }

                $name = mb_ereg_replace("[　]*$",'',$rec[1]);
                $result .= "<option value=$rec[0] $sel>$rec[0]: $name</option>";
            }
        }
        return $result;
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

        $result = '';

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