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
* ���Υץ����ϥե꡼���եȥ������Ǥ������ʤ��Ϥ���򡢥ե꡼���եȥ���
* �����Ĥˤ�ä�ȯ�Ԥ��줿 GNU ���̸������ѵ��������(�С������2������
* ˾�ˤ�äƤϤ���ʹߤΥС������Τ����ɤ줫)��������β��Ǻ�����
* �ޤ��ϲ��Ѥ��뤳�Ȥ��Ǥ��ޤ���
*
* ���Υץ�����ͭ�ѤǤ��뤳�Ȥ��ä����ۤ���ޤ�����*������̵�ݾ�*
* �Ǥ������Ȳ�ǽ�����ݾڤ��������Ū�ؤ�Ŭ�����ϡ������˼����줿��Τ��
* ������¸�ߤ��ޤ��󡣾ܤ�����GNU ���̸������ѵ���������������������
*
* ���ʤ��Ϥ��Υץ����ȶ��ˡ�GNU ���̸������ѵ���������ʣ��ʪ�����
* ������ä��Ϥ��Ǥ����⤷������äƤ��ʤ���С��ե꡼���եȥ��������Ĥ�
* �����ᤷ�Ƥ�������(����� the Free Software Foundation, Inc., 59
* Temple Place, Suite 330, Boston, MA 02111-1307 USA)��
*
*   Program name:
*    ���������å��ꥹ�� - VendorCheckListClass.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*
*/

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF ���ܸ�Ķ�����
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
            $this->error = "�ǡ����١�����³���顼(" + $dsn + ")";
        }
    }
}

/*
*      �������饹��
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
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
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
            $this->error = "�ǡ�����¸�ߤ��ޤ���";
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

        // ����ϥݡ��ȥ졼�ȡ�
        $this->pdf->AddPage('P');
        //
        $this->pdf->SetFont(GOTHIC,'U',16);
        $this->pdf->SetXY(80,18);
        $this->pdf->Write(10,"���������å��ꥹ��");

        $this->pdf->SetFont(GOTHIC,'',10);
        $this->pdf->SetXY(160,18);
        $this->pdf->Write(10,"$year ǯ $month �� $date ��");

        // �ڡ�����

        $this->pdf->SetXY(180,25);
        $this->pdf->Write(10,"PAGE: $this->page_no");


        $this->pdf->SetXY(20,30);
        $this->pdf->Write(10,"������");

        $this->pdf->SetXY(35,30);
        $this->pdf->Write(10,"�����̾��");

        $this->pdf->SetXY(100,30);
        $this->pdf->Write(10,"������");

        $this->pdf->SetXY(115,30);
        $this->pdf->Write(10,"�����̾��");
        //

        $this->pdf->line(20,37,195,37);

        $this->current_y = 30;
        $this->lines = 0;

    }

    function printLine()
    {
        // ����̾
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