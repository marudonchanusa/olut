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
 *    �������� ���� ���� ���� -  MonthlyArrivalReportByVendorClass.php
 *
 *   Release History:
 *    2005/09/30  ver 1.00.00 Initial Release
 *    2005/10/05  ver 1.00.01 Added year selection screen.
 *    2005/10/25  ver 1.00.02 �ԥȡ�������ɲá�
 *
 */

//
//
//  ��ա����Υ������κǸ�˶��ιԤ��֤��ʤ����ȡ�PDF���ϥ��顼�ˤʤ�ޤ���
//
//

//
// ���ܸ�Ķ�����
//
require_once(OLUT_DIR . 'mbfpdf.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB
require_once('OlutAppLib.php');

// database configuration
class MonthlyArrivalReportByVendor_SQL extends SQL
{
    function MonthlyArrivalReportByVendor_SQL()
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

// smarty configuration
class MonthlyArrivalReportByVendor_Smarty extends Smarty
{
    function MonthlyArrivalReportByVendor_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

class MonthlyArrivalReportByVendor extends OlutApp
{
    // ��ΰ��ִط������
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $left_margin  = 5.0;
    var $right_margin = 5.0;
    var $bottom_margin = 10.0;
    var $header_height = 30; // 15.0;
    var $cell_height   = 5;
    var $cell_width    = 0;
    var $cell_margin   = 2.0;
    var $number_font_size = 9.0;
    var $ck_code = 0;

    // ���󥹥������
    var $tpl;
    var $sql;
    var $pdf;

    var $current_y;
    var $x_pos;

    //  �����Ͼ��ʥ����ɡ�
    var $line_info;

    var $num_of_item_per_page = 35;     // ���Ԥ˰�������ǡ����ιԿ���(�إ���1�Խ�����
    var $section_total = array();       // ����ι�ס�
    var $total = array();               // ������ι�ס�
    var $material_total = 0;            // ������Υȡ����롣�������ʤ������
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $target_year;
    var $target_month;
    var $start_date;
    var $end_date;
    var $page_no = 0;
    var $current_ship_sec_code;         // ���ߤλ���⥳����

    // ctor
    function MonthlyArrivalReportByVendor()
    {
        $this->sql =& new MonthlyArrivalReportByVendor_SQL;
        $this->tpl =& new MonthlyArrivalReportByVendor_Smarty;

        // ���ܰ����������ꡣ
        // LAND SCAPE ����
        $paper_width  = $this->paper_height;
        $paper_height = $this->paper_width;

        //
        // ��ʿ������ 23 pixel �ֳ�
        //                                
        $this->x_pos = array(5,45,50,73,96,118,140,163,186,209,232,255,278,301,324,347,370,393);
    }

    /*
    *  �����Υᥤ��
    */
    function printOut($formvars)
    {
        //
        //  �����ϰϤ����դ���׻���
        //
        $this->setupDates($formvars);


        // ǯ�֡�����飱����ޤǤ���ꡣ
        //
        $_query  = "select orig_code, v.name, date_part('month',act_date), sum(total_price), ss.name ";
        $_query .= " from t_main t, m_vendor v, m_shipment_section ss";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')  ";
        $_query .= " and v.code = t.orig_code";
        $_query .= " and ss.code = t.ship_sec_code";
        $_query .= " group by orig_code, date_part('month', act_date), v.name, ss.name ";
        $_query .= " order by orig_code, v.name, ss.name,date_part('month',act_date)";

        if($this->sql->query($_query,SQL_INIT)==false)
        {
            print $_query;
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = '�ǡ�����¸�ߤ��ޤ���';
            return false;
        }

        //            print $_query;

        //
        // B4�λ極��������ꤷ�Ƥ��ޤ�������ϥݡ��ȥ졼�ȡ�
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        $this->pdf->SetTopMargin(0);
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');

        //
        $this->changePage();
        $this->drawTitle();
        $this->drawHeader();
        $this->current_y -= $this->cell_height;
        $this->total = array();

        $current_store = null;
        $current_ship_section = null;

        do
        {
            // ���ߤ�Ź�ޡ�
            if($current_store != $this->sql->record[0])
            {
                // �ڡ������ؤ��롩
                if($this->lines >= $this->num_of_item_per_page)
                {
                    $this->changePage();
                    $this->drawTitle();
                    $this->drawHeader();
                    $this->current_y += $this->cell_height;
                    $this->lines = 0;
                }
                else
                {
                    // Y���֤ι�����
                    $this->current_y += $this->cell_height * 2;
                    $this->lines += 2;
                }
                // ������̾��ü�˽���
                $this->writeCell(0,$this->sql->record[1],0);  // name

                //
                $current_store = $this->sql->record[0];
            }

            // ������ʤ˽���
            $this->current_y += $this->cell_height;
            $this->lines += 1;
            $this->writeCell(0,$this->sql->record[4],1);  // ship_section_name
            
            $line_total = 0;

            for($month=1; $month<=12; $month++)
            {
                if($month == $this->sql->record[2])
                {
                    $index = $this->sql->record[2]+1;   // ��+1������ǥå����͡�

                    $this->writeCell($index,number_format($this->sql->record[3]));   // ���

                    // �ȡ�����˲û�
                    $this->total[$month] += $this->sql->record[3];
                    $line_total += $this->sql->record[3];

                    $rc = $this->sql->next();
                }
                else
                {
                    $index = $month + 1;
                    $this->writeCell($index,'0');     // ���
                }
            }
            //
            $this->writeCell(14,number_format($line_total));   // ���
            //
            //
        }
        while($rc == true);
        
        $this->sql->disconnect();

        // �ȡ��������
        $this->printTotal();

        //
        $this->pdf->Output();
        return true;
    }

    /*
    *  �����ϰ����ϲ��̤Υ������
    */

    function renderScreen($formvars)
    {
        $this->tpl->assign('title','�������� ���� ���� ����');
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
    *  �ڡ����ؤ��Υ����å��ȼ¹ԡ�
    *  lines�������ꤷ���ꥤ�󥯥���Ȥ���Τ���ա�
    *
    */

    function changePage()
    {
        $this->pdf->AddPage('L');
        $this->pdf->SetTopMargin(0);
        // Y���֤����ꡣ
        $this->current_y = $this->header_height;

        $this->page_no++;
    }

    /*
    *  ���դ��ط������ꡣ
    */

    function setupDates($formvars)
    {
        // �ʲ��Ϥ��ޤ��������̤��������
        // OlutApp::getCurrentProcessDate($this->sql,$this->target_year,$this->target_month);
        
        $this->target_year = $formvars['target_year'];
        $this->start_date = "$this->target_year/01/01";
        $this->end_date = "$this->target_year/12/31";
    }

    /*
    *
    */
    function drawTitle()
    {
        // �������������롣
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        $y = 10;
        $h = 10;
        //
        $this->pdf->SetFont(GOTHIC,'',14);
        $this->pdf->SetXY(130,$y);
        $this->pdf->Write($h," ������ * �̷������˰��� $this->target_year ǯ");
        $this->pdf->SetXY(280,$y);
        $this->pdf->Write($h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->pdf->SetXY(280,$y+5);
        $this->pdf->Write($h, "ñ�̡�����",'',1,'R');
    }

    /*
    *   ����̾�ʤɡ��إ�����1�Ԥ����񤯡�
    */

    function writeHeaderCell($x_index,$title)
    {
        $this->pdf->SetFont(GOTHIC,'',12);
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height - $this->cell_height;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$title);
    }

    function printTotal()
    {
        // �ڡ������ؤ��롩
        if($this->lines >= $this->num_of_item_per_page)
        {
            $this->changePage();
            $this->drawTitle();
            $this->drawHeader();

            $this->lines = 0;
        }
                  
        $this->current_y += $this->cell_height * 2;
                    
        $this->writeCell(0,"���硡��",0);
        
        $grand_total = 0;

        for($month=1; $month<=12; $month++)
        {
            if(isset($this->total[$month]))
            {
                $index = $month+1;   // ��+1������ǥå����͡�
                $this->writeCell($index,number_format($this->total[$month]));
                
                $grand_total += $this->total[$month];
            }
            else
            {
                $index = $month + 1;
                $this->writeCell($index,'0');     // ���
            }
        }
        
        $this->writeCell(14,number_format($grand_total));
    }

    /*
    *
    */

    function drawHeader()
    {
        $this->writeHeaderCell(0,"�š������衡̾");

        for($i=1; $i<=12; $i++)
        {
            $this->writeHeaderCell($i+1," $i ��");
        }
        
        $this->writeHeaderCell($i+1,"���");
        $this->pdf->line(5,30,350,30);
    }

    function writeCell($x_index,$str,$align=1)
    {
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        if($align==1)
        {
            $this->pdf->SetFont(GOTHIC,'',9);
            $len = $this->pdf->GetStringWidth($str);
            $offset = ($cell_width - $len) - 1;
        }
        else
        {
            $this->pdf->SetFont(GOTHIC,'',11);
            $offset = 0;
        }

        $x = $this->x_pos[$x_index];
        $y = $this->current_y;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$str);
    }
}
?>