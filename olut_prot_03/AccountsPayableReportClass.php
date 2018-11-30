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
 *    ��ݶ����� - AccountsPayableReportClass.php
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
class AccountsPayableReport_Smarty extends Smarty
{
    function AccountsPayableReport_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class AccountsPayableReport_SQL extends SQL
{
    function AccountsPayableReport_SQL()
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

class AccountsPayableReport extends OlutApp
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
    var $total = 0;                     // ������ι�ס�
    var $material_total = 0;            // ������Υȡ����롣�������ʤ������
    var $current_vendor;                // ���߰�����ζȼ�̾���ݻ����ȼԤ��ؤ�ä����פ��������Τ�ɬ�ס�
    var $current_date;                  // ���߰���������դ����ݻ��������Ѥ�ä��龮�פ�������롣
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $page_no = 1;
    var $totals;

    // ctor
    function AccountsPayableReport()
    {
        $this->tpl =& new AccountsPayableReport_Smarty;
        $this->sql =& new AccountsPayableReport_SQL;

        // ���ܰ����������ꡣ
        $this->x_pos = array(10,100,130,160,190,220,250,280,310);
    }

    /*
    *  �����ϰ����ϲ��̤Υ������
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
        $this->tpl->display('AccountsPayableReportForm.tpl');
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

        $target_year  = $formvars['target_year'];
        $target_month = $formvars['target_month'];

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        //
        //
        $this->pdf->Cell(0,9,"$target_year ǯ $target_month ��   ��ݶ�����",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;
        //
        $this->current_y = 15;

        //
        // �����衡���������ꡡ�������ࡡ���������������ԣϡ�����������������������ס����������ʡ����������
        //
        $header = array("������","������","����","����","�ԣ�","����","�������","������","���");

        for($i=0; $i<9;$i++)
        {
            $this->pdf->SetXY($this->x_pos[$i],$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }

        // �������
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  �ǡ����䤤��碌�����ٹ԰�����
    */

    // SQL �Ϥ���ʴ����ˤʤ롣
    //
    // select sum(total_price),ss.name,v.name from t_main t,m_shipment_section ss, m_vendor v
    // where act_flag='1' and ss.code=t.ship_sec_code and v.code=t.orig_code group by ss.name, v.name;

    function printData($formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];

        $target_year  = $formvars['target_year'];
        $target_month = $formvars['target_month'];
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

        // ��ʧ���ե饰 0: ��ݡ�1: ����

        $_query  = "select v.name,ss.name,sum(total_price) ";
        $_query .= " from t_main t,m_shipment_section ss, m_vendor v ";
        $_query .= " where t.payment_flag='0' and ss.code=t.ship_sec_code and v.code=t.orig_code ";
        $_query .= " and t.orig_code >= '$code_from' and t.orig_code <= '$code_to' ";
        $_query .= " and (t.act_flag='1' or t.act_flag='2' or t.act_flag='3') ";
        $_query .= " and (t.act_date >= '$dt_from' and t.act_date <= '$dt_to') ";
        $_query .= " group by ss.code, ss.name, v.code, v.name order by v.code, ss.code";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->total     = 0;
            $this->lines     = 0;
            $this->current_y = 30;
            $n = 0;

            // rec[0] => �ȼ�̾
            // rec[1] => �����
            // rec[2] => ���

            foreach($this->sql->record as $rec)
            {
                // �����ǶȼԤ����åȤ���Ƥʤ��Ȥ���С������κǽ顣
                if(!isset($this->current_vendor))
                {
                    $this->current_vendor = $rec[0];
                }

                // �ȼԤ��Ѥ�ä��Τ�1�԰�����
                if(strcmp($this->current_vendor,$rec[0])!=0)
                {
                    $this->checkPageChange(1,$formvars);
                    $this->printLine($values);

                    $this->current_vendor = $rec[0];    // ���ߤΥ٥�����Ȥ����ݻ���
                    unset($values);
                }

                // �ȼԤ��Ѥ��ޤǤ�1�Ԥ˰����Ȥʤ롣
                $values[$rec[1]] = $rec[2];
                $n++;

            }

            // �롼�פ�ȴ�����餽��ޤǤμ����ˤĤ��ư�����
            $this->checkPageChange(1,$formvars);
            $this->printLine($values);

            // ����˷�Υȡ��������
            $this->checkPageChange(2,$formvars);
            $this->printMonthlyTotal();

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
        if(($this->lines+$line_to_add) >= $this->num_of_item_per_page)
        {
            $this->pdf->AddPage('L');
            $this->printHeader($formvars);
            $this->lines = 0;
            $this->current_y = 30;
        }
        else {
            $this->lines += $lines_to_add;
        }
    }

    /*
    *  ��פ�������ޤ����ʷ�Υȡ������
    */

    function printMonthlyTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"** ��ݶ⡡��� **");

        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['������'],11));

        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['����'],11));

        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['����'],11));

        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['�ԣ�'],11));

        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['����'],11));

        //
        //  �����ۤΥȡ����롣
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->material_total,11)); //  ������

        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['������'],11));
        //
        //  �ȡ�����
        //
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,11)); //  ������

        // �ȡ������ꥻ�åȡ�
        $this->total = 0;

        // ����ʬ�ʤ�Ƥ��뤳�Ȥ���ա�
        $this->current_y += $this->line_height*2;
    }

    /*
    *  ���٣��Ԥ�������ޤ���
    */

    function printLine($values)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        // �������
        $material_total = 0;

        //
        // ����衡�����ꡡ�������ࡡ���������������ԣϡ�����������������������ס����������ʡ����������
        //

        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,$this->current_vendor); //  �����̾

        //
        //  ������
        //
        $v = $values['������'];
        if( isset($v) )
        {
            $material_total += $v;
            $this->totals['������'] += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,11));

        //
        //  ����
        //
        $v = $values['����'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['����'] += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,11));
        //
        //  ����
        //
        $v = $values['����'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['����'] += $v;
        }
        else
        {
            $v =0;
        }
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,11));
        //
        //  �ԣ�
        //
        $v = $values['�ԣ�'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['�ԣ�'] += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,11));
        //
        //  ����
        //
        $v = $values['����'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['����'] += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,11));
        //
        // �������
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,11));

        $this->material_total += $material_total;


        // ������
        $v = $values['������'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['������'] += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,11));

        // �ԥȡ��������
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,11));

        //
        $this->total += $material_total;

        // Y���֤ι�����
        $this->current_y += $this->line_height;

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