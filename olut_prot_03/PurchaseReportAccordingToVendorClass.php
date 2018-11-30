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
 *    �ȼ��̻������� -  PurchaseReportAccordingToVendorClass.php
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
class PurchaseReportAccordingToVendor_Smarty extends Smarty
{
    function PurchaseReportAccordingToVendor_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class PurchaseReportAccordingToVendor_SQL extends SQL
{
    function PurchaseReportAccordingToVendor_SQL()
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

class PurchaseReportAccordingToVendor extends OlutApp
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
    var $num_of_item_per_page = 29;     // ���Ԥ˰�������ǡ����ιԿ���
    var $total;                         // ������ι�ס�
    var $grand_total;                   // ��ʧ�ۤι�ס�
    var $current_vendor;
    var $current_commodity;             //
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $page_no = 1;
    var $last_printed_vendor;
    var $cash_count = 0;            // ����ʧ�������
    var $credit_count = 0;          // ��ݷ����
    var $cash_arrivals_total    = 0;
    var $credit_arrivals_total  = 0;
    var $cash_discount_total   = 0;
    var $credit_discount_total = 0;
    var $cash_paid_total = 0;
    var $credit_paid_total = 0;

    // ctor
    function PurchaseReportAccordingToVendor()
    {
        $this->tpl =& new PurchaseReportAccordingToVendor_Smarty;
        $this->sql =& new PurchaseReportAccordingToVendor_SQL;

        // ���ܰ����������ꡣ
        $this->x_pos = array(10,80,100,190,220,250,280,310);
    }

    /*
    *  �����ϰ����ϲ��̤Υ������
    */

    function renderScreen($formvars)
    {
        $year  = date('Y');
        $month = date('m');
        $last_date = OlutApp::getLastDate($year,$month);

        $date_from = $year . $month . "01" ;
        $date_to   = $year . $month . $last_date;
        //
        $this->tpl->assign('date_from',$date_from);
        $this->tpl->assign('date_to', $date_to);
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('PurchaseReportAccordingToVendorForm.tpl');
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
        if($this->printData($formvars)==false)
        {
            // to show error messages.
            $this->renderScreen($formvars);
        }
        else
        {
            header("content-type: application/pdf;");
            $this->pdf->Output();
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

        if(isset($formvars['date_from']))
        {
            $target_month = substr($formvars['date_from'],4,2);
            $target_year  = substr($formvars['date_from'],0,4);
        }
        else
        {
            $target_month = $month;
            $target_year  = $date;
        }

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        $this->pdf->Cell(0,9,"$target_year ǯ $target_month ��ȼ��̻�������ɽ",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;
        //
        $this->current_y = 15;

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"������");
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,"������");
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,"����̾");
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,"��������");
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,"ʿ��ñ��");
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,"������");
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,"�Ͱ���");
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,"��ʧ���");

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

        if(OlutApp::checkDate($formvars['date_from'])==false)
        {
            $this->error = "�������դ��������Ǥ�";
            return false;
        }

        if(OlutApp::checkDate($formvars['date_to'])==false)
        {
            $this->error = "��λ���դ��������Ǥ�";
            return false;
        }

        $date_from = OlutApp::formatDate($formvars['date_from']); // �ϥ��ե���ǥե����ޥå�
        $date_to   = OlutApp::formatDate($formvars['date_to']);

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        $_query = "select v.name, s.name, c.name, sum(t.amount), sum(t.total_price) ";
        // ���������Ͱ��������Ȳ�
        $_query .= ",(select total_price from t_main where t.slip_no=slip_no and act_flag='2'";
        $_query .= " and t.slip_no=slip_no and t.com_code=com_code) as discount";
        $_query .= ",t.payment_flag";

        //
        $_query .= " from t_main t,m_commodity c, m_vendor v , m_shipment_section s ";
        $_query .= " where (t.act_flag='1' or t.act_flag='3')";
        $_query .= " and t.orig_code >= '$code_from' and t.orig_code <= '$code_to'";


        if(isset($date_from) && strlen($date_from))
        {
            $_query .= " and t.act_date >= '$date_from'";
        }
        if(isset($date_to) && strlen($date_from))
        {
            $_query .= " and t.act_date <= '$date_to'";
        }

        $_query .= " and t.com_code=c.code and v.code=t.orig_code and ";
        $_query .= " s.code=t.ship_sec_code ";
        $_query .= " group by t.com_code,c.name,v.name,s.name,t.orig_code,discount,t.payment_flag ";
        $_query .= " order by t.orig_code,s.name, t.com_code" ;

        //print $_query;


        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->lines     = 0;
            $this->current_y = 30;
            // �롼�פ��롣
            foreach($this->sql->record as $rec)
            {
                // �����ǶȼԤ����åȤ���Ƥʤ��Ȥ���С������κǽ顣
                if($this->current_vendor == null)
                {
                    $this->current_vendor = $rec[0];
                    $this->current_commodity = $rec[2];
                }
                // �ȼԤ��ѹ����줿���ˤϥȡ������������ޤ���
                if(strcmp($this->current_vendor,$rec[0]))
                {
                    // �ȡ�������������Ԥˤʤ�Τ��������Ǥ��뤫��
                    $this->checkPageChange(2,$formvars);

                    // �ȡ����������
                    $this->printTotal();

                    // ���ߤζȼԤȤ����ݻ���
                    $this->current_vendor = $rec[0];
                }

                // ���ٹԤ�������롣
                $this->checkPageChange(1,$formvars);
                $this->printLine($rec);


            }

            // �롼�פ�ȴ�����餽��ޤǤζȼ�ʬ�Υȡ�����������
            $this->checkPageChange(2,$formvars);
            $this->printTotal();

            $this->checkPageChange(3,$formvars);
            $this->printGrandTotal($formvars);

        }
        return true;
    }

    /*
    *  �ڡ����ؤ��Υ����å��ȼ¹ԡ�
    *  lines�������ꤷ���ꥤ�󥯥���Ȥ���Τ���ա�
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
            $last_printed_vendr = null;       // �ڡ������ؤ�ä��ΤǼ����̾�ϰ������롣
        }
        else {
            $this->lines += $lines_to_add;
        }
    }

    /*
    *  �ȡ������������ޤ���
    */

    function printTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"���"); //  ������

        //
        //  �����ۤΥȡ����롣
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,10)); //  ������


        //
        //  ��ʧ�ۥȡ�����
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->grand_total,10)); //  ������

        // �ȡ������ꥻ�åȡ�
        $this->total = 0;
        $this->grand_total = 0;

        // ����ʬ�ʤ�Ƥ��뤳�Ȥ���ա�
        $this->current_y += $this->line_height*2;
    }

    /*
    *  ���٣��Ԥ�������ޤ���
    */

    function printLine($rec)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        //
        // ����� / ������ / ����̾��/ �������̡�/��ʿ��ñ����/�������ۡ�/���Ͱ��ۡ�/����ʧ���
        //

        //
        // �������Ƭ24 byte�Ȥ��롣
        //
        if(strcmp($this->last_printed_vendor,$rec[0]))
        //if(true)
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
        $this->last_printed_vendor = $rec[0];

        //
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,$rec[1]); //  ������

        //
        //  ����̾
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);  //  ����̾

        //
        //  ���������
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[3],8,2));  //  ���������

        //$this->pdf->Write(20,OlutApp::formatNumber($rec[3],8));  //  ���������

        //
        //  ʿ��ñ��
        //
        if($rec[3] != 0)
        {
            $average = round($rec[4] / $rec[3],2);
        }
        else
        {
            $average =0;
        }
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($average,12,2));  //  ʿ��ñ��

        //
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],10)); //  ������

        // �Ͱ����� => ���˶�ʬ'2' �Υ쥳���ɡ�
        $discount = $rec[5] * (-1);
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($discount,10));

        // �Ͱ����ϥޥ��ʥ������äƤ���Τǡ��ʲ��ϲû��Ȥʤ롣
        $paid_total = $rec[4]+$rec[5];
        //
        //  ��ʧ�ۡ�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($paid_total,10));


        //
        // �ȡ�����η׻���
        //
        $this->total += $rec[4];
        $this->grand_total += $paid_total;

        if($rec[6] == '0')
        {
            $this->credit_arrivals_total  += $rec[4];
            $this->credit_discount_total -= $rec[5];
            $this->credit_paid_total     += $paid_total;
        }
        else
        {
            $this->cash_arrivals_total  += $rec[4];
            $this->cash_discount_total -= $rec[5];
            $this->cash_paid_total     += $paid_total;
        }

        // Y���֤ι�����
        $this->current_y += $this->line_height;

    }


    function printGrandTotal($formvars)
    {
        $this->credit_count = $this->getPaymentCount('0',$formvars);
        $this->cash_count   = $this->getPaymentCount('1',$formvars);

        // three lines.
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"��ݹ��");

        $this->pdf->SetXY(50,$this->current_y);
        $this->pdf->Write(20,"�������: $this->credit_count");

        $credit_arrivals_total = OlutApp::formatNumber($this->credit_arrivals_total,11);
        $this->pdf->SetXY(90,$this->current_y);
        $this->pdf->Write(20,"������: $credit_arrivals_total");

        $credit_discount_total = OlutApp::formatNumber($this->credit_discount_total,11);
        $this->pdf->SetXY(140,$this->current_y);
        $this->pdf->Write(20,"�Ͱ���: $credit_discount_total");

        $credit_paid_total = OlutApp::formatNumber($this->credit_paid_total,11);
        $this->pdf->SetXY(190,$this->current_y);
        $this->pdf->Write(20,"��ʧ��: $credit_paid_total");

        // Y���֤ι�����
        $this->current_y += $this->line_height;

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"������");

        $this->pdf->SetXY(50,$this->current_y);
        $this->pdf->Write(20,"�������: $this->cash_count");

        $cash_arrivals_total = OlutApp::formatNumber($this->cash_arrivals_total,11);
        $this->pdf->SetXY(90,$this->current_y);
        $this->pdf->Write(20,"������: $cash_arrivals_total");

        $cash_discount_total = OlutApp::formatNumber($this->cash_discount_total,11);
        $this->pdf->SetXY(140,$this->current_y);
        $this->pdf->Write(20,"�Ͱ���: $cash_discount_total");

        $cash_paid_total = OlutApp::formatNumber($this->cash_paid_total,11);
        $this->pdf->SetXY(190,$this->current_y);
        $this->pdf->Write(20,"��ʧ��: $cash_paid_total");

        // Y���֤ι�����
        $this->current_y += $this->line_height;

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"�����");

        $grand_total_count = $this->credit_count+ $this->cash_count;

        $this->pdf->SetXY(50,$this->current_y);
        $this->pdf->Write(20,"�������: $grand_total_count");

        $grand_total_arrivals = $this->cash_arrivals_total+$this->credit_arrivals_total;
        $grand_total_arrivals = OlutApp::formatNumber($grand_total_arrivals,11);
        $this->pdf->SetXY(90,$this->current_y);
        $this->pdf->Write(20,"������: $grand_total_arrivals");

        $grand_total_discount = $this->cash_discount_total + $this->credit_discount_total;
        $grand_total_discount = OlutApp::formatNumber($grand_total_discount,11);
        $this->pdf->SetXY(140,$this->current_y);
        $this->pdf->Write(20,"�Ͱ���: $grand_total_discount");

        $grand_paid_total = $this->cash_paid_total + $this->credit_paid_total;

        $grand_paid_total = OlutApp::formatNumber($grand_paid_total,11);
        $this->pdf->SetXY(190,$this->current_y);
        $this->pdf->Write(20,"��ʧ��: $grand_paid_total");

    }

    function getPaymentCount($payment_flag,$formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];
        $date_from = OlutApp::formatDate($formvars['date_from']); // �ϥ��ե���ǥե����ޥå�
        $date_to   = OlutApp::formatDate($formvars['date_to']);

        //
        if(!isset($code_from) || !strlen($code_from))
        {
            $code_from = '00000';
        }
        if(!isset($code_to) || !strlen($code_to))
        {
            $code_to = '99999';
        }

        $_query = "select count(*) from t_main t ";
        $_query .= " where t.payment_flag='$payment_flag' and (t.act_flag='1' or t.act_flag='3')";
        $_query .= " and t.orig_code >= '$code_from' and t.orig_code <= '$code_to'";


        if(isset($date_from) && strlen($date_from))
        {
            $_query .= " and t.act_date >= '$date_from'";
        }
        if(isset($date_to) && strlen($date_from))
        {
            $_query .= " and t.act_date <= '$date_to'";
        }

        // print $_query;

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            return 0;
        }
        return $this->sql->record['count'];
    }
}

?>