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
*    取引先チェックリスト - VendorCheckListClass.php
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

// smarty configuration
class VendorCheckList_Smarty extends Smarty
{
    function VendorCheckList_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class VendorCheckList_SQL extends SQL
{
    function VendorCheckList_SQL()
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

class VendorCheckList extends OlutApp
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
    var $number_of_lines_per_page = 88;

    function VendorCheckList()
    {
        $this->sql =& new VendorCheckList_SQL();
        $this->tpl =& new VendorCheckList_Smarty();
    }

    function renderScreen($formvars)
    {
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

        //
        $_query  = "select code, name from m_vendor order by code";

        if(!$this->sql->query($_query,SQL_INIT))
        {
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = "データが存在しません";
            return false;
        }

        // init.
        $this->page_no = 1;

        $this->printHeader();

        do
        {
            $this->checkPageChange(1);
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
        $this->pdf->Write(10,"取引先チェックリスト");

        $this->pdf->SetFont(GOTHIC,'',10);
        $this->pdf->SetXY(160,18);
        $this->pdf->Write(10,"$year 年 $month 月 $date 日");

        // ページ。

        $this->pdf->SetXY(180,25);
        $this->pdf->Write(10,"PAGE: $this->page_no");


        $this->pdf->SetXY(20,30);
        $this->pdf->Write(10,"コード");

        $this->pdf->SetXY(35,30);
        $this->pdf->Write(10,"取引先名称");

        $this->pdf->SetXY(100,30);
        $this->pdf->Write(10,"コード");

        $this->pdf->SetXY(115,30);
        $this->pdf->Write(10,"取引先名称");
        //

        $this->pdf->line(20,37,195,37);

        $this->current_y = 30;
        $this->lines = 0;

    }

    function printLine()
    {
        // 商品名
        $code = $this->sql->record[0];
        $name = $this->sql->record[1];
        $offset = 0;

        if($this->lines % 2)
        {
            $offset = 80;
        }
        $this->pdf->SetXY(20+$offset,$this->current_y);
        $this->pdf->Write(20,$code);

        $this->pdf->SetXY(35+$offset,$this->current_y);
        $this->pdf->Write(20,$name);

        if($this->lines % 2)
        {
            $this->current_y += 5;
        }
        $this->lines++;
    }

    function checkPageChange($lines_to_add)
    {
        if($this->lines + $lines_to_add > $this->number_of_lines_per_page)
        {
            $this->page_no++;
            $this->printHeader();
        }
    }
}

?>