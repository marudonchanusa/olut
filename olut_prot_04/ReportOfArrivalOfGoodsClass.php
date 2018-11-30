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
*    �������� -  ReportOfArrivalOfGoodsClass.php
*
*   Release History:
*    2005/09/30  ver 1.00.00 Initial Release
*    2005/10/18  ver 1.00.01 Added "Real" grand total.
*
*/

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF ���ܸ�Ķ�����
require_once('OlutAppLib.php');

// smarty configuration
class ReportOfArrivalOfGoods_Smarty extends Smarty
{
    function ReportOfArrivalOfGoods_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ReportOfArrivalOfGoods_SQL extends SQL
{
    function ReportOfArrivalOfGoods_SQL()
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

class ReportOfArrivalOfGoods extends OlutApp
{
    var $left_margin = 10;
    var $right_margin = 10;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = 250;            // paper size in portlait.(not landscape)
    var $paper_height = 353;            // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 25;     // ���Ԥ˰�������ǡ����ιԿ���
    var $total;                         // ������ι�ס�
    var $vendor_total;                  // ����褴�Ȥι�ס�
    var $grand_total;                   // ����
    var $current_vendor;                // ���߰�����ζȼ�̾���ݻ����ȼԤ��ؤ�ä����פ��������Τ�ɬ�ס�
    var $current_date;                  // ���߰���������դ����ݻ��������Ѥ�ä��龮�פ�������롣
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $page_no = 1;

    // ctor
    function ReportOfArrivalOfGoods()
    {
        $this->tpl =& new ReportOfArrivalOfGoods_Smarty;
        $this->sql =& new ReportOfArrivalOfGoods_SQL;

        // ���ܰ����������ꡣ
        $this->x_pos = array(10,80,110,190,220,250,280,310);
    }

    /*
    *  �����ϰ����ϲ��̤Υ������
    */

    function renderScreen($formvars)
    {
        if(isset($formvars['target_year']))
        {
            $this->target_year = $formvars['target_year'];
            $this->target_month = $formvars['target_month'];
        }
        else 
        {
            $this->target_month = OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
        }

        //
        $this->tpl->assign('target_year_list',OlutApp::getTargetYearList($this->target_year));
        $this->tpl->assign('target_month_list', OlutApp::getTargetMonthList($this->target_month));

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ReportOfArrivalOfGoodsForm.tpl');
    }

    /*
    *  �����ؿ�
    *
    */
    function printOut($formvars)
    {
        //
        // B4�λ極��������ꤷ�Ƥ��ޤ�������ϥݡ��ȥ졼�ȡ�
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
    *  �ƥڡ����Υإ��������
    */

    function printHeader($formvars)
    {
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        if(isset($formvars['target_year']))
        {
            $this->target_year  = $formvars['target_year'];
            $this->target_month = $formvars['target_month'];
        }
        else 
        {
            $this->target_year = date('Y');
            $this->target_month = date('m');
        }

        $year_from  = $this->target_year;
        $month_from = $this->target_month;
        $date_from  = "01";

        $year_to  = $this->target_year;
        $month_to = $this->target_month;
        $date_to  = OlutApp::getLastDate($this->target_year,$this->target_month);

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        //
        // ��ա�
        //  $date_to�θ���ܳѥ��ڡ�������$date_to��ɽ������ʤ���
        //  php�ΥХ����Ȼפ���Ⱦ�ѤΥ��ڡ�����������б��������ɽ������ʤ��Τ� - (�ϥ��ե�ˤȤ�����
        //
        $this->pdf->Cell(0,9,"$year_from/$month_from/$date_from - $year_to/$month_to/$date_to ��������",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no ",'',1,'R');
        $this->page_no++;
        //
        $this->current_y = 15;

        // �����衡�������ա�����������̾�����������̡���ñ������ۡ������Ҹ�

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"������");
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,"������");
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"����̾");
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,"����");
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,"ñ��");
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,"���");
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,"�����Ҹ�");

        // �������
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  �ǡ����䤤��碌�����ٹ԰�����
    */

    function printData($formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];

        $year_from  = $this->target_year;
        $month_from = $this->target_month;
        $date_from  = "01";

        $year_to  = $this->target_year;
        $month_to = $this->target_month;
        $date_to  = OlutApp::getLastDate($this->target_year,$this->target_month);


        $date_from = "$year_from/$month_from/$date_from";
        $date_to   = "$year_to/$month_to/$date_to";

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        $_query = "select v.name, t.act_date, com.name, t.amount, ";
        $_query .= "t.unit_price,t.total_price,w.name, u.name ";
        $_query .= " from t_main t, m_shipment_section s, m_commodity com, m_vendor v, m_warehouse w, m_unit u ";
        $_query .= " where t.orig_code >= $code_from and t.orig_code <= $code_to and s.code=t.ship_sec_code ";
        $_query .= " and com.code=t.com_code and v.code=t.orig_code ";
        $_query .= " and w.code=t.warehouse_code ";
        $_query .= " and u.code=com.unit_code";
        $_query .= " and (t.act_flag='1' or t.act_flag='2' or t.act_flag='3')";

        if(isset($date_from) && strlen($date_from))
        {
            $_query .= " and t.act_date >= '$date_from'";
        }
        if(isset($date_to) && strlen($date_to))
        {
            $_query .= " and t.act_date <= '$date_to'";
        }

        $_query .= " order by t.orig_code, t.act_date, t.warehouse_code";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->total        = 0;
            $this->grand_total  = 0;
            $this->vendor_total = 0;
            $this->lines        = 0;
            $this->current_y    = 30;
            // �롼�פ��롣
            foreach($this->sql->record as $rec)
            {
                // �����ǶȼԤ����åȤ���Ƥʤ��Ȥ���С������κǽ顣
                if(!isset($this->current_vendor))
                {
                    $this->current_vendor = $rec[0];
                    $this->current_date   = $rec[1];
                    $print_vendor = true;           // �ǽ�ʤΤǶȼԤ�������롣
                }
                else
                {
                    if(strcmp($this->current_date,$rec[1]))
                    {
                        // �ȡ��������1�Ԥˤʤ�Τ��������Ǥ��뤫��
                        $this->checkPageChange(1,$formvars,&$print_vendor);

                        // ���װ�����
                        $this->printTotal();
                        $this->current_date = $rec[1];
                    }

                    // �ȼԤ��ѹ����줿���ˤϥȡ������������ޤ���
                    if(strcmp($this->current_vendor,$rec[0]))
                    {
                        // �ȡ�������������Ԥˤʤ�Τ��������Ǥ��뤫��
                        $this->checkPageChange(2,$formvars,&$print_vendor);

                        // �ȡ����������
                        $this->printVendorTotal();

                        // ���ߤζȼԤȤ����ݻ���
                        $this->current_vendor = $rec[0];
                        $print_vendor = true;           // �ȼԤ��ؤ�ä��Τǰ������롣

                    }
                    else
                    {
                        $print_vendor = false;          // Ʊ���ȼԤʤΤǰ������ʤ���
                    }
                }

                // ���ٹԤ�������롣
                $this->checkPageChange(1,$formvars,&$print_vendor);
                $this->printLine($rec,$print_vendor);
            }

            // �롼�פ�ȴ�����餽��ޤǤξ���ʬ�Υȡ�����������
            $this->checkPageChange(1,$formvars,&$print_vendor);
            $this->printTotal();
            $this->checkPageChange(2,$formvars,&$print_vendor);
            $this->printVendorTotal();
            
            // ����
            $this->checkPageChange(2,$formvars,&$print_vendor);
            $this->printGrandTotal();

        }
        return true;
    }

    /*
    *  �ڡ����ؤ��Υ����å��ȼ¹ԡ�
    *  lines�������ꤷ���ꥤ�󥯥���Ȥ���Τ���ա�
    *
    */

    function checkPageChange($lines_to_add,$formvars,&$print_vendor)
    {
        if(($this->lines+$line_to_add) >= $this->num_of_item_per_page)
        {
            $this->pdf->AddPage('L');
            $this->printHeader($formvars);
            $this->lines = 0;
            $this->current_y = 30;
            $print_vendor = true;       // �ڡ������ؤ�ä��ΤǼ����̾�ϰ������롣
        }
        else {
            $this->lines += $lines_to_add;
        }
    }

    /*
    *  ���פ�������ޤ���(�������Υȡ����롣���ͳ�ǧ�ѡ�2005/8/9)
    */

    function printTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"*����*");

        //
        //  �����ۤΥȡ����롣
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,10)); //  ������


        //
        //  ��ʧ�ۥȡ�����
        //
        //$this->pdf->SetXY($this->x_pos[7],$this->current_y);
        //$this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  ������

        // �ȡ������ꥻ�åȡ�
        $this->total = 0;
        //$this->grand_total = 0;

        // ����ʬ�ʤ�Ƥ��뤳�Ȥ���ա�
        $this->current_y += $this->line_height;
    }

    /*
    *  ��פ�������ޤ����ʼ���褴�ȤΥȡ������
    */

    function printVendorTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"*���*");

        //
        //  �����ۤΥȡ����롣
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->vendor_total,10)); //  ������


        //
        //  ��ʧ�ۥȡ�����
        //
        //$this->pdf->SetXY($this->x_pos[7],$this->current_y);
        //$this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  ������

        // �ȡ������ꥻ�åȡ�
        $this->total = 0;
        $this->vendor_total = 0;

        // ����ʬ�ʤ�Ƥ��뤳�Ȥ���ա�
        $this->current_y += $this->line_height*2;
    }
    
    /*
     *      ���פ����
     */

    function printGrandTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"*����*");

        //
        //  �����ۤΥȡ����롣
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  ������

    }
        
    /*
    *  ���٣��Ԥ�������ޤ���
    */

    function printLine($rec,$print_vendor)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        //
        // ������/������/����̾/����/ñ��/���/�����Ҹ�
        //

        //
        // �����(=������ˡ���Ƭ24 byte�Ȥ��롣
        //
        if($print_vendor == true)
        {
            if(strlen($rec[0]>24)){
                $vendor = substr($rec[0],0,24);
            }
            else
            {
                $vendor = $rec[0];
            }
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$vendor); //  �����̾
        }

        //
        //  ���դ�
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,ereg_replace("-","/",$rec[1]));

        //
        //  ����̾
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);  //  ����̾

        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[3]-10,$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[3],8));  //  ���������
        $this->pdf->Write(20,$rec[7]);                           // ñ��

        //
        //  ñ��
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],8));  //  ñ��

        //
        //  ���
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[5],10)); //  ���

        // �Ͱ����ۤʤ�ǥȥ�󥶥����������äƤ��ʤ���@later!

        //
        // �����Ҹ�
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,$rec[6],20); //  �Ҹ�̾


        //
        // �ȡ�����η׻���
        //
        $this->total += $rec[5];
        $this->vendor_total += $rec[5];
        $this->grand_total  += $rec[5];

        // Y���֤ι�����
        $this->current_y += $this->line_height;

    }
}

?>