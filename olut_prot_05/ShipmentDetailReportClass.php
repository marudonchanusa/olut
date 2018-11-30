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
 * ���Υץ������ϥե꡼���եȥ������Ǥ������ʤ��Ϥ���򡢥ե꡼���եȥ���
 * �����Ĥˤ�ä�ȯ�Ԥ��줿 GNU ���̸������ѵ��������(�С������2������
 * ˾�ˤ�äƤϤ���ʹߤΥС������Τ����ɤ줫)��������β��Ǻ�����
 * �ޤ��ϲ��Ѥ��뤳�Ȥ��Ǥ��ޤ���
 *
 * ���Υץ�������ͭ�ѤǤ��뤳�Ȥ��ä����ۤ���ޤ�����*������̵�ݾ�* 
 * �Ǥ������Ȳ�ǽ�����ݾڤ��������Ū�ؤ�Ŭ�����ϡ������˼����줿��Τ��
 * ������¸�ߤ��ޤ��󡣾ܤ�����GNU ���̸������ѵ���������������������
 *
 * ���ʤ��Ϥ��Υץ������ȶ��ˡ�GNU ���̸������ѵ���������ʣ��ʪ�����
 * ������ä��Ϥ��Ǥ����⤷������äƤ��ʤ���С��ե꡼���եȥ��������Ĥ�
 * �����ᤷ�Ƥ�������(����� the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)��
 *
 *   Program name:
 *    �������ٽ�� - ShipmentDetailReportClass.php
 *
 *   Release History:
 *    2005/09/30  ver 1.00.00 Initial Release
 *    2005/10/05  ver 1.00.01 Changed year list logic.  
 *    2005/10/12  ver 1.00.02 added sort order by isdcd. 
 *    2005/10/13  ver 1.00.03 Added Division total.
 *    2005/10/15  ver 1.00.04 Add page change before the Division total. 
 *    2005/10/20  ver 1.00.05 �����ʤΥȡ����뤬̵���ä���
 *    2005/10/21  ver 1.00.06 1. �����ʤɤΥȡ�������ɲá�
 *                            2. �����Ȥ�Ź���������ͥ�褹��褦���ѹ���
 *    2005/12/05  ver 1.00.07 Added paramter support.
 *    2005/12/09  ver 1.00.08 Ź�ޥȡ�����Ǿ����Ƿ׻���Ź��̾���ץ쥹��
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF ���ܸ�Ķ�����
require_once('OlutAppLib.php');
require_once('SystemProfile.php');

// smarty configuration
class ShipmentDetailReport_Smarty extends Smarty
{
    function ShipmentDetailReport_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ShipmentDetailReport_SQL extends SQL
{
    function ShipmentDetailReport_SQL()
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
*       �����׾���
*/

class DivisionTotal
{
    var $code;      // divison code. 6 digits.
    var $total;
    var $material_total;
    var $supply_total;
    var $totals;      // �����Ȥ��ι�ס�����
    var $tax;
}

/*
*      �������饹��
*
*/

class ShipmentDetailReport extends OlutApp
{
    var $left_margin = 10;
    var $right_margin = 10;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 30;     // ���Ԥ˰�������ǡ����ιԿ���
    var $total = 0;                     // ������ι�ס�
    var $tax_total = 0;
    var $material_total = 0;            // ������Υȡ����롣�������ʤ������
    var $supply_total = 0;              // �����ʥȡ����롣
    var $monthly_total = 0;             // ��������
    var $material_monthly_total = 0;    // ��κ�����ι�ס�
    var $supply_monthly_total = 0;
    var $current_store;                 // ���߰�����ζȼ�̾���ݻ����ȼԤ��ؤ�ä����פ��������Τ�ɬ�ס�
    var $current_store_division;        // ���ߤ�Ź�����硣
    var $current_store_name;
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $page_no = 1;
    var $system_profile;
    var $isdcd_total = 0;
    var $isdcd_material_total = 0;
    var $isdcd_supply_total = 0;
    var $isdcd_tax_total    = 0;
    var $current_isdcd;
    var $divison_totals = array();      // ����ȡ�������ݻ����롣
    var $totals = array();              // ��������Υȡ����롣
    var $monthly_totals = array();      //
    var $isdcd_totals   = array();

    // ctor
    function ShipmentDetailReport()
    {
        $this->tpl =& new ShipmentDetailReport_Smarty;
        $this->sql =& new ShipmentDetailReport_SQL;

        // ���ܰ����������ꡣ
        $this->x_pos = array(10,60,85,110,135,160,185,210,235,265,290);
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
        $this->tpl->display('ShipmentDetailReportForm.tpl');
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
        
        if($formvars==null)
        {
            // �ѥ�᡼����ư�ξ��ե����ब̵����2005/12/5
            $target_year  = $this->target_year;
            $target_month = $this->target_month;
        }
        else 
        {
            $target_year  = $formvars['target_year'];
            $target_month = $formvars['target_month'];
        }

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        //
        //
        $this->pdf->Cell(0,9,"$target_year ǯ $target_month ��   �������ٽ�",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;

        //
        $this->current_y = 15;

        $header = array("Ź��̾","����","������","����","����","�ԣ�","����","�������","������","���","������");

        for($i=0; $i<11;$i++)
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
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  �ǡ����䤤��碌�����ٹ԰�����
    */


    function printData($formvars)
    {
        if($formvars == null)
        {
            //
            // 2005/12/5 added for paramter support.
            //
            $code_from = $this->target_code;
            $code_to   = $this->target_code;
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
        // 2005/10/12 ���������ɽ�Υ����Ȥ��ɲá�

        $_query  = "select st.name,sd.name,ss.name,-sum(total_price),st.isdcd ";
        $_query .= " from t_main t,m_shipment_section ss, m_store_division sd, m_store st ";
        $_query .= " where t.dest_code=st.code and sd.code=t.store_sec_code and ss.code=t.ship_sec_code";
        $_query .= " and t.dest_code >= '$code_from' and t.dest_code <= '$code_to' ";
        $_query .= " and (t.act_flag='5' or t.act_flag='6' or t.act_flag='7') ";
        $_query .= " and (t.act_date >= '$dt_from' and t.act_date <= '$dt_to') ";
        $_query .= " group by ss.code, ss.name, sd.code, sd.name, st.name, st.code, st.isdcd ";
        $_query .= " order by st.isdcd, sd.code, sd.name, st.code, st.name, ss.code";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            return false;
        }
        else
        {
            $this->isdcd_material_total = 0;
            $this->isdcd_total = 0;
            $this->total     = 0;
            $this->lines     = 0;
            $this->current_y = 30;
            $n = 0;

            // rec[0] => �ȼ�̾
            // rec[1] => �����
            // rec[2] => ���

            foreach($this->sql->record as $rec)
            {
                // ������Ź�ޡ�Ź�����礬���åȤ���Ƥʤ��Ȥ���С������κǽ顣
                if(!isset($this->current_store))
                {
                    $this->current_store = $rec[0];
                    $this->current_store_division = $rec[1];
                    $this->current_isdcd = $rec[4];   // Ź������������
                }

                // Ź�ޤޤ���Ź�������礬�Ѥ�ä��Τǥǡ����Ԥ�1�԰�����
                if(strcmp($this->current_store,$rec[0])!=0 || strcmp($this->current_store_division,$rec[1]) != 0)
                {
                    $this->checkPageChange(1,$formvars);
                    $this->printLine($values);

                    // Ź�ޤ��Ѥ�ä���Ź�ޤι�פ����
                    if(strcmp($this->current_store,$rec[0]) != 0)
                    {
                        // Ź�޹�װ���
                        $this->checkPageChange(2,$formvars);
                        $this->printStoreTotal();
                        $this->current_store_division = $rec[1];
                    }
                    
                    // �����������ɤ�Ź�����������ɣ�����Τ�������Ƭ����Ǥ���
                    // 
                    if(substr($this->current_isdcd,0,6) != substr($rec[4],0,6))
                    {
                        // ���������ȤΥȡ������Ǹ�˰�������Τ���¸���롣
                        $this->saveIsdcdTotal();
                        $this->current_isdcd = $rec[4];
                    }

                    $this->current_store = $rec[0];              // Ź�ޤȤ����ݻ���
                    $this->current_store_division = $rec[1];

                    unset($values);
                }


                // Ź�ޤ��Ѥ��ޤǤ�1�Ԥ˰����Ȥʤ롣
                $values[$rec[2]] = $rec[3];
                $n++;

            }
                        
            // �롼�פ�ȴ�����餽��ޤǤ�Ź�ޤˤĤ��ư�����
            $this->checkPageChange(1,$formvars);
            $this->printLine($values);

            // Ź�޹�װ���
            $this->checkPageChange(2,$formvars);
            $this->printStoreTotal();
            
                        
            // ����˷�Υȡ��������
            $this->checkPageChange(2,$formvars);
            $this->printMonthlyTotal();
            
            $this->saveIsdcdTotal();
            //
            // ���������ȤΥȡ����롣
            // 
            $this->printIsdcdTotal($formvars);

        }
        return true;
    }

    /*
    *  �ڡ����ؤ��Υ����å��ȼ¹ԡ�
    *  lines�������ꤷ���ꥤ�󥯥���Ȥ���Τ����ա�
    *
    */

    function checkPageChange($lines_to_add,$formvars)
    {
        if(($this->lines+$lines_to_add) >= $this->num_of_item_per_page)
        {
            $this->pageChange($formvars);
        }
        else {
            $this->lines += $lines_to_add;
        }
    }
    
    function pageChange($formvars)
    {
        $this->pdf->AddPage('L');
        $this->printHeader($formvars);
        $this->lines = 0;
        $this->current_y = 30;
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
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['������'],10));

        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['����'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['����'],10));
        //
        //  �ԣ�
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['�ԣ�'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->totals['����'],10));
        //
        //  ������ס�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->material_total,10)); //  ������

        // ���٥ȡ����롣
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->supply_total,10));
        //
        //  �������ޤ᤿���
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->total,10)); //  ������
        
        // 2005/12/09 �����ǤϤ����Ƿ׻���
        $this->tax_total = bcmul($this->total,$this->system_profile->tax,0);
                
        // �����ǡ�
        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->tax_total,10));  
        
        // ��Υȡ�����˲û���
        $this->material_monthly_total += $this->material_total;
        $this->monthly_total          += $this->total;
        $this->monthly_supply_total   += $this->supply_total;
        $this->monthly_tax_total      += $this->tax_total;
        
        // 2005/12/09 �����˰�ư��
        $this->isdcd_tax_total += $this->tax_total;
        
        // 2005/10/21
        // ����Υ�������Ʊ���ʤ��ñ�ʤ�û��Ƿ׻����Ƥ���뤬��
        // ����Υ��������㤦�ȡ��ʲ��Τ褦�ˡ����줾��û����ʤ������ܤʤ褦�Ǥ���
        //
        if(is_array($this->totals))
        {
            foreach($this->totals as $key => $ttl)
            {
                $this->monthly_totals[$key] += $ttl;
            }
        }
        
        // �ȡ������ꥻ�åȡ�
        $this->total = 0;
        $this->tax_total = 0;
        $this->material_total = 0;
        $this->supply_total = 0;
        $this->totals = array();

        // ����ʬ�ʤ�Ƥ��뤳�Ȥ����ա�
        $this->current_y += $this->line_height*2;
    }


    /*
     *  ��������פ���¸���ޤ���
     */

    function saveIsdcdTotal()
    {
        $division_total = new DivisionTotal();
        $division_total->code = substr($this->current_isdcd,0,6);
        $division_total->material_total = $this->isdcd_material_total;      //  ������ס�
        $division_total->total = $this->isdcd_total;                        //  �������ޤ᤿���
        $division_total->supply_total = $this->isdcd_supply_total;
        $division_total->tax          = $this->isdcd_tax_total;
        $division_total->totals       = $this->isdcd_totals;
        
        //
        //$this->pdf->SetXY($this->x_pos[7],$this->current_y);
        //$this->pdf->Write(20,OlutApp::formatNumber($this->isdcd_material_total,10)); //  ������

        // �ȡ������ꥻ�åȡ�
        $this->isdcd_total = 0;
        $this->isdcd_material_total = 0;
        $this->isdcd_supply_total   = 0;
        $this->isdcd_tax_total      = 0;
        $this->isdcd_totals         = array();
        
        $this->divison_totals[] =  $division_total;
    }
        
    /*
    *  ��������פ�������ޤ���
    */
    function printIsdcdTotal($formvars)
    {
        if(count($this->divison_totals) == 0)
        {
            return;
        }
        
        $this->pageChange($formvars);
        
        $current_company = substr($this->divison_totals[0]->code,0,2);
        $company_material_total = 0;
        $company_total = 0;
        $company_tax_total = 0;
        $company_supply_total = 0;
        $company_totals = array();
        $grand_material_total = 0;
        $grand_total          = 0;
        $grand_tax_total      = 0;
        $grand_totals         = array();
        
        foreach($this->divison_totals as $dv)
        {
            if($current_company != substr($dv->code,0,2))
            {
                $this->checkPageChange(2,$formvars);
                $this->printCompanyTotalLine($company_material_total, $company_total,$company_supply_total,$company_totals,$company_tax_total);
                $company_material_total = 0;
                $company_total          = 0;
                $company_supply_total   = 0;
                $company_tax_total      = 0;
                $company_totals         = array();
                $current_company = substr($dv->code,0,2);
            }
            $this->checkPageChange(1,$formvars);
            $this->printIsdcdTotalLine($dv);
            
            $company_material_total += $dv->material_total;
            $company_total          += $dv->total;
            $company_supply_total   += $dv->supply_total;
            $company_tax_total      += $dv->tax;
            
            $grand_material_total += $dv->material_total;
            $grand_total          += $dv->total;
            $grand_supply_total   += $dv->supply_total;
            $grand_tax_total      += $dv->tax;
            
            foreach($dv->totals as $key => $value)
            {
                $grand_totals[$key]   += $value;
                $company_totals[$key] += $value;
            }
        }
        
        // ��׺ư�����
        $this->checkPageChange(2,$formvars);
        $this->printCompanyTotalLine($company_material_total, $company_total,$company_supply_total,$company_totals,$company_tax_total);   
        
        //      
        $this->checkPageChange(1,$formvars);
        $this->printGrandTotalLine($grand_material_total, $grand_total,$grand_supply_total,$grand_totals,$grand_tax_total);   

    }
    
    function printIsdcdTotalLine($dv)
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,$this->getDivisionName($dv->code));

        //
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->totals['������'],10));

        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->totals['����'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->totals['����'],10));
        //
        //  �ԣ�
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->totals['�ԣ�'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->totals['����'],10));

        //
        //  ������ס�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->material_total,10)); //  ������

        // ���٥ȡ����롣
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->supply_total,10));

        //
        //  �������ޤ᤿���
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->total,10)); //  ������
        
        // �����ǡ�
        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($dv->tax,10));   
        
        $this->current_y += $this->line_height;
    }
    
    function printCompanyTotalLine($material_total,$total,$supply_total,$totals,$tax)
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"** �硡�� **");
        
        //
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['������'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['����'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['����'],10));
        //
        //  �ԣ�
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['�ԣ�'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['����'],10));
        //
        //  ������ס�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,10)); //  ������

        // ���٥ȡ����롣
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($supply_total,10));

        //
        //  �������ޤ᤿���
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($total,10)); //  ������
       
        // �����ǡ�
        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($tax,10));   
        
        $this->current_y += $this->line_height*2;
    }
    
    function printGrandTotalLine($material_total,$total,$supply_total,$totals,$tax)
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"** �����硡�� **");
        //
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['������'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['����'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['����'],10));
        //
        //  �ԣ�
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['�ԣ�'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals['����'],10));
        //
        //  ������ס�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,10)); //  ������
        
        // ���٥ȡ����롣
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($supply_total,10));

        //
        //  �������ޤ᤿���
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($total,10)); //  ������
        
        // �����ǡ�
        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($tax,10));        

        $this->current_y += $this->line_height;
    }
    
    function getDivisionName($code)
    {
        $_query = "select name from m_division where code='$code'";
        if(!$this->sql->query($_query,SQL_INIT))
        {
            return "���Ƚ�̾����: $code";
        }
        
        if($this->sql->record == null)
        {
            return "���Ƚ�̾����: $code";
        }
        
        return $this->sql->record[0];
    }
    
    function printMonthlyTotal()
    {
        //
        //  ��׹Լ��̡�
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"*����*");
        //
        //  ������
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_totals['������'],10));

        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_totals['����'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_totals['����'],10));
        //
        //  �ԣ�
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_totals['�ԣ�'],10));
        //
        //  ����
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_totals['����'],10));
        //
        //  ������ס�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->material_monthly_total,10)); //  ������

        // ���٥ȡ����롣
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_supply_total,10));
        //
        //  �ȡ�����
        //
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_total,10)); //  ������
        
        // �����ǡ�
        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->monthly_tax_total,10));
        
        // ����ʬ�ʤ�Ƥ��뤳�Ȥ����ա�
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
        // Ź��̾�����硡��������   ���ࡡ�����������ԣϡ�����������������ס��������ʡ���� ������
        //
        
        // 2005/12/09 Ź��̾���ץ쥹��
        if($this->current_store_name != $this->current_store)
        {
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$this->current_store); //  Ź��̾
            // 
            $this->current_store_name = $this->current_store;
        }

        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,$this->current_store_division); //  ����

        //
        //  ������
        //
        $v = $values['������'];
        if( isset($v) )
        {
            $material_total += $v;
            $this->totals['������'] += $v;            
            $this->isdcd_totals['������'] += $v;            
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));

        //
        //  ����
        //
        $v = $values['����'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['����'] += $v;
            $this->isdcd_totals['����'] += $v;
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
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
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        //  �ԣ�
        //
        $v = $values['�ԣ�'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['�ԣ�'] += $v;                
            $this->isdcd_totals['�ԣ�'] += $v;                            
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        //  ����
        //
        $v = $values['����'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['����'] += $v;              
            $this->isdcd_totals['����'] += $v;              
        }
        else
        {
            $v = 0;
        }
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));
        //
        // �������
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,10));

        $this->material_total += $material_total;
        $this->isdcd_material_total += $material_total;   // added 2005/10/12

        // ������
        $v = $values['������'];
        if(isset($v))
        {
            $material_total += $v;
            $this->totals['������'] += $v; 
            $this->isdcd_totals['������'] += $v; 
        }
        else
        {
            $v = 0;
        }
        $this->supply_total += $v;
        $this->isdcd_supply_total   += $v;  // $this->supply_total; �ְ�äƤ����� 2005/10/21 fix.       
        //
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($v,10));

        // �ԥȡ��������
        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($material_total,10));

        // 2005/12/09 �����Ǥ�Ź�ޤ��Ȥ˰��������ѹ���
        // ������ ==> ��Ψ������ե�����
        //$tax = bcmul($material_total,$this->system_profile->tax,0);    // changed to bcmul. 2005/10/20
        //$this->tax_total       += $tax;
        //$this->isdcd_tax_total += $tax;
        
        //$this->pdf->SetXY($this->x_pos[10],$this->current_y);
        //$this->pdf->Write(20,OlutApp::formatNumber($tax,10));

        //
        $this->total += $material_total;
        $this->isdcd_total += $material_total;      // added 2005/10/12.

        // Y���֤ι�����
        $this->current_y += $this->line_height;

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

            if(preg_match('/code=(\d*)/',$parm,$matches))
            {
                $this->target_code = $matches[1];
            }

        }

        if($this->target_year != null && $this->target_month != null)
        {
            return true;
        }
        return false;
    }        
}

?>