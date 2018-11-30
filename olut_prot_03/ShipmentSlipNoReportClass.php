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
 *    Źɼ��������ٽ� - ShipmentSlipNolReportClass.php
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
require_once('SystemProfile.php');

// smarty configuration
class ShipmentSlipNoReport_Smarty extends Smarty
{
    function ShipmentSlipNoReport_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ShipmentSlipNoReport_SQL extends SQL
{
    function ShipmentSlipNoReport_SQL()
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

class ShipmentSlipNoReport extends OlutApp
{
    var $left_margin = 10;
    var $right_margin = 10;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_A4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_A4_HEIGHT; // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 37;     // ���Ԥ˰�������ǡ����ιԿ���
    var $total = 0;                     // ������ι�ס�
    var $section_total = 0;             //
    var $material_total = 0;            // ������Υȡ����롣�������ʤ������
    var $monthly_total = 0;             // �������
    var $current_store;                 // ���߰�����ζȼ�̾���ݻ����ȼԤ��ؤ�ä����פ��������Τ�ɬ�ס�
    var $current_store_division;        // ���ߤ�Ź�����硣
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $page_no = 1;
    var $system_profile;
    var $target_year;
    var $target_month;

    // ctor
    function ShipmentSlipNoReport()
    {
        $this->tpl =& new ShipmentSlipNoReport_Smarty;
        $this->sql =& new ShipmentSlipNoReport_SQL;

        // ���ܰ����������ꡣ
        $this->x_pos = array(10,70,100,125,145,170,195,220,245,275,300);
    }

    function Dispose()
    {
        $this->sql->disconnect();
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
        $this->tpl->display('ShipmentSlipNoReportForm.tpl');
    }

    /*
    *  �����ؿ�
    *
    */
    function printOut($formvars)
    {
        //
        $this->system_profile =& new SystemProfile();

        //
        // B4�λ極��������ꤷ�Ƥ��ޤ��������A4�ݡ��ȥ졼�ȡ�
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        //$this->pdf->SetButtomMargin(0);

        $this->pdf->AddPage('P');
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->printHeader($formvars);
        if($this->printData($formvars)==true)
        {
            header("content-type: application/pdf;");
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
        $this->pdf->Cell(0,9,"$target_year ǯ $target_month ��   Źɼ��������ٽ�",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;

        //
        $this->current_y = 15;

        $header = array("Ź��̾","����","����","��ɼ�ֹ�","  ���");

        for($i=0; $i<sizeof($header);$i++)
        {
            if($i>1){
                $delta = 5;
            }else {
                $delta = 0;
            }

            $this->pdf->SetXY($this->x_pos[$i]+$delta,$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }

        // �������
        $width = $this->paper_width - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  �ǡ����䤤��碌�����ٹ԰�����
    */


    function printData($formvars)
    {
        if($this->target_year != null && $this->target_month != null)
        {
            $code_from = '00000';
            $code_to   = '99999';
            $target_year  = $this->target_year;
            $target_month = $this->target_month;
        }
        else
        {
            $code_from = $formvars['code_from'];
            $code_to   = $formvars['code_to'];
            $target_year  = $formvars['target_year'];
            $target_month = $formvars['target_month'];
        }
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

        // �иˤν��ס�
        // act_flag=5,6,7 ���оݡ��ʤ���Ǥ����Τ���ǧ @later)
        //

        $_query  = "select st.name,sd.name,act_date,slip_no,-sum(total_price) ";
        $_query .= " from t_main t, m_store_division sd, m_store st ";
        $_query .= " where t.dest_code=st.code and sd.code=t.store_sec_code ";
        $_query .= " and t.dest_code >= '$code_from' and t.dest_code <= '$code_to' ";
        $_query .= " and (t.act_flag='5' or t.act_flag='6' or t.act_flag='7') ";
        $_query .= " and (t.act_date >= '$dt_from' and t.act_date <= '$dt_to') ";
        $_query .= " group by sd.code, sd.name, st.name, st.code,act_date,slip_no ";
        $_query .= " order by st.code, sd.code, act_date, slip_no";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            $this->error = $this->sql->error;
            return false;
        }
        else
        {
            $this->total     = 0;
            $this->lines     = 0;
            $this->current_y = 30;
            $n = 0;

            // rec[0] => Ź��̾
            // rec[1] => ����
            // rec[2] => ��
            //    [3] => ��ɼ�ֹ�
            //    [4] => ���

            foreach($this->sql->record as $rec)
            {
                // Ź�ޤ��Ѥ�ä���Ź�ޤι�פ����
                if($this->current_store != null && strcmp($this->current_store,$rec[0]) != 0)
                {
                    // ����ȡ��������
                    $this->checkPageChange(1,$formvars);
                    $this->printSectionTotal();
                    // Ź�޹�װ���
                    $this->checkPageChange(2,$formvars);
                    $this->printStoreTotal();
                }
                else
                if($this->current_store_division != null && strcmp($this->current_store_division,$rec[1]) != 0)
                {
                    // ����ȡ��������
                    $this->checkPageChange(1,$formvars);
                    $this->printSectionTotal();
                }

                $this->checkPageChange(1,$formvars);
                $this->printLine($rec);

            }

            // ����ȡ��������
            $this->checkPageChange(1,$formvars);
            $this->printSectionTotal();
            // Ź�޹�װ���
            $this->checkPageChange(2,$formvars);
            $this->printStoreTotal();

            // ����˷�Υȡ��������
            // $this->checkPageChange(2,$formvars);
            // $this->printMonthlyTotal();

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
            $this->pdf->AddPage('P');
            $this->printHeader($formvars);
            $this->lines = 0;
            $this->current_y = 30;
            $this->current_store = null;  // force store name printed.
        }
        else {
            $this->lines += $lines_to_add;
        }
    }

    /*
    *  ��פ�������ޤ�����Ź�Υȡ������
    */

    function printStoreTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*Ź�޹��*");

        //
        //  ���
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,11)); //  ������

        // ��Υȡ�����˲û���
        $this->material_monthly_total += $this->material_total;
        $this->monthly_total += $this->total;

        // �ȡ������ꥻ�åȡ�
        $this->total = 0;

        // ����ʬ�ʤ�Ƥ��뤳�Ȥ���ա�
        $this->current_y += $this->line_height*2;
    }

    /*
    *  ��פ�������ޤ���������Υȡ������
    */

    function printSectionTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*������*");

        //
        //  ���
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->section_total,11)); //  ������

        // ��Υȡ�����˲û���
        $this->material_monthly_total += $this->material_total;
        $this->monthly_total += $this->section_total;

        // �ȡ������ꥻ�åȡ�
        $this->section_total = 0;

        //
        $this->current_y += $this->line_height;
    }

    function printMonthlyTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*����*");

        //
        //  ������ס�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->material_monthly_total,10)); //  ������


        //
        //  �ȡ�����
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_total,10)); //  ������

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
        // Ź��̾�����硡�����ա���ɼ�ֹ桡���
        //
        // rec[0] => Ź��̾
        // rec[1] => ����
        // rec[2] => ��
        //    [3] => ��ɼ�ֹ�
        //    [4] => ���

        if($this->current_store != $rec[0])
        {
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$rec[0]); //  Ź��̾
            $this->current_store = $rec[0];
            $this->current_store_division = null;
        }

        if($this->current_store_division != $rec[1])
        {
            $this->pdf->SetXY($this->x_pos[1],$this->current_y);
            $this->pdf->Write(20,$rec[1]); //  ����
            $this->current_store_division = $rec[1];
        }

        $rec[2] = ereg_replace('-','/',$rec[2]);

        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);

        $this->pdf->SetXY($this->x_pos[3]+5,$this->current_y);
        $this->pdf->Write(20,$rec[3]);

        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],11));
        //
        $this->total += $rec[4];
        $this->section_total += $rec[4];

        // Y���֤ι�����
        $this->current_y += $this->line_height;

    }

    /*
    *  �ѥ�᡼���������դ����롣
    */

    function parseParameter()
    {
        $parm = $_SERVER['QUERY_STRING'];
        $this->target_year = null;
        $this->target_month = null;

        if($parm != null)
        {
            if(preg_match('/year=(\d*)/',$parm,$matches))
            {
                $this->target_year = $matches[1];
            }

            if(preg_match('/month=(\d*)/',$parm,$matches))
            {
                $this->target_month = $matches[1];

            }
        }

        if($this->target_year != null && $this->target_month != null)
        {
            return true;
        }
        return false;
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