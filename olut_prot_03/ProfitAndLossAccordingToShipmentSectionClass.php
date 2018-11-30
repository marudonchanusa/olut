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
 *    ���вٺ��׹����ɽ -  ProfitAndLossAccordingToShipmentSectionClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

//
//  ��ա����Υ������κǸ�˶��ιԤ��֤��ʤ����ȡ�PDF���ϥ��顼�ˤʤ�ޤ���
//

//
// ���ܸ�Ķ�����
//
require_once(OLUT_DIR . 'mbfpdf.php');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB
require_once('OlutAppLib.php');


// database configuration
class ProfitAndLossAccordingToShipmentSection_SQL extends SQL
{
    function ProfitAndLossAccordingToShipmentSection_SQL()
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


class ProfitAndLossAccordingToShipmentSection extends OlutApp
{
    var $left_margin  = 10;
    var $right_margin = 10;
    var $cell_height  = 8.0;
    var $tpl;
    var $sql;
    var $pdf;
    var $current_y;
    var $x_pos;
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $num_of_item_per_page = 25;     // ���Ԥ˰�������ǡ����ιԿ���
    var $total = 0;                     // ������ι�ס�
    var $material_total = 0;            // ������Υȡ����롣�������ʤ������
    var $current_vendor;                // ���߰�����ζȼ�̾���ݻ����ȼԤ��ؤ�ä����פ��������Τ�ɬ�ס�
    var $current_date;                  // ���߰���������դ����ݻ��������Ѥ�ä��龮�פ�������롣
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $target_year;
    var $target_month;
    var $start_date;
    var $end_date;
    var $start_date_of_last_year;
    var $end_date_of_last_year;
    var $shipment_section_names;
    var $shipment_section_codes;
    var $ck_flags;                      // ����ȥ�륭�å���Υե饰������CK.�ʡῩ����
    var $invs;                          // ����ê���ǡ������ݻ���
    var $page_no = 1;
    var $counted_invs;                  // �׻��߸�
    var $depletion;                     // ���ס�����Ǥ���

    // ctor
    function ProfitAndLossAccordingToShipmentSection()
    {
        $this->sql =& new ProfitAndLossAccordingToShipmentSection_SQL;
        // ���ܰ����������ꡣ
        $this->x_pos = array(10,50,75,100,125,150,175,200,225,255,280,305,330);
    }

    /*
    *  �����Υᥤ��
    */
    function printOut()
    {
        //
        //  �����ϰϤ����դ���׻���
        //
        $this->setupDates();


        //
        //  �������ϡ����������
        //
        $this->setupShipmentSection();

        //
        // B4�λ極��������ꤷ�Ƥ��ޤ�������ϥݡ��ȥ졼�ȡ�
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);

        $this->pdf->AddPage('L');
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');
        $this->pdf->SetFont(GOTHIC,'',12);

        // ���ס���Ⱦʬ��������ϡ�
        $this->printHeader();

        if($this->printMonthlyReport()==true)
        {
            // ǯ��ס���Ⱦʬ��������ϡ�
            $this->printMiddleHeader();

            if($this->printAnnualReport()==true)
            {
                $this->pdf->Output();
            }
        }
        else
        {
            // $this->renderScreen($formvars);
        }
        $this->sql->disconnect();        
    }


    /*
    *  ���դ��ط������ꡣ
    */

    function setupDates()
    {
        //$this->target_year = date('Y');
        //$this->target_month = '06';  // date('m');

        OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
    }

    //
    function setupMonthlyRange()
    {
        //
        // 4ǯʬ�η��ϰϤ�׻��� ����ǥå�����0����-3�ޤǤˤʤ롣
        //

        for($i=0; $i > -4; $i--)
        {
            $year = $this->target_year + $i;
            $last = OlutApp::getLastDate($year,$this->target_month);
            $this->start_date[$i] = "$year/$this->target_month/01";
            $this->end_date[$i]   = "$year/$this->target_month/$last";
        }
    }

    /*
    *  ���ѷ׻��Τ���1������ȡ��������ȷ�ϥץ�ե�����ˤ��롣@later
    */

    function setupAnnualRange()
    {
        $month = "01";
        for($i=0; $i > -4; $i--)
        {
            $year = $this->target_year + $i;
            $last = OlutApp::getLastDate($year,$this->target_month);
            $this->start_date[$i] = "$year/$month/01";
            $this->end_date[$i]   = "$year/$this->target_month/$last";
        }
    }

    /*
    *  ������������ꡣ
    */

    function setupShipmentSection()
    {
        $_query = "select name,code,ck_flag from m_shipment_section order by isdcd";   // isdcd �ˤ��뤫�� �Ϥ���
        $this->sql->query($_query,SQL_ALL);

        foreach($this->sql->record as $rec)
        {
            $this->shipment_section_names[] = $rec[0];
            $this->shipment_section_codes[] = $rec[1];
            $this->ck_flags[$rec[0]] = $rec[2];
        }
    }

    /*
    *  �إ����������
    */

    function printHeader()
    {
        // �������������롣
        $year = date('Y');
        $month = date('m');
        $date  = date('d');

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = $this->cell_height;

        //
        $this->pdf->Cell(0,$h,"$this->target_year ǯ $this->target_month ����вٺ��׹����ɽ",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE: $year/$month/$date    PAGE: $this->page_no ",'',1,'R');
        $this->page_no++;

        $header = array("����֡�","���ϴ���","��ǯ��","�ؿ�","����۹�","���������",
        "���ʸ���","��»��","����ê��","���׹�","��ǯ��","�ؿ�");

        $this->current_y = 13;

        for($i=0; $i<12;$i++)
        {
            $this->pdf->SetXY($this->x_pos[$i],$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }


        // �������
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);
    }

    /*
    *  �в٤ι�פ��䤤��碌���ʲ��4ǯʬ�䤤��碌�ˤʤ��
    */
    function queryMonthlyShipments($start_date, $end_date)
    {
        $_query = "select ss.name,-sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and (t.act_flag='5' or t.act_flag='7')";  // '6' ����ץ�ޤ�Ƥ褤�Τ��� => ȴ���Τ�����2005/9/4
        $_query .= " and t.act_date>='$start_date'";
        $_query .= " and t.act_date<='$end_date'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  ����ץ�в٤ι�פ��䤤��碌��
    */
    function querySampleShipments($start_date, $end_date)
    {
        $_query = "select ss.name,-sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and t.act_flag='6'";
        $_query .= " and t.act_date>='$start_date'";
        $_query .= " and t.act_date<='$end_date'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  �߸����϶�ۤ��䤤��碌
    */

    function queryMonthlyInventories($start_date)
    {
        //
        // �߸˥쥳���ɤ��ס���������η�ΤϤ�������ˤ��롣
        // ���Ȥ���8��1���ˤ�7��ʬ��ê���쥳���ɤ����롣
        //
        $sd = OlutApp::getNextMonth($start_date);

        $_query = "select ss.name,sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and t.act_flag='0'";
        $_query .= " and t.act_date='$sd'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";

        //print $_query;
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  �׻��߸ˤ����롣��ưʿ�ѡ�
    *
    *  ::::�ʲ��ϻȤäƤ��ʤ�:::::
    *  ��ͳ�ϡ��߷פǤϸ��ߤ�NTC�Υ��å��˹��פ��ʤ����ᡣñ��ǤϹ��ס�
    *
    */

    function queryCountedInventories($start_date,$end_date)
    {
        foreach($this->shipment_section_codes as $key => $sc)
        {
            //
            // �ǽ�˾�����˷׻��߸ˤ���롣
            //
            $_query = "select t.com_code,sum(t.amount)*" ;
            $_query .= "(select  case when sum(amount)=0 then 0 else sum(total_price)/sum(amount) end from t_main where ((act_flag='1' or act_flag='2' or act_flag='3')  ";
            $_query .= " and act_date>='$start_date' and act_date<='$end_date'";
            $_query .= " and t.com_code=com_code and amount <> 0) or";
            $_query .= " (act_flag='0' and act_date='$start_date' and t.com_code=com_code and amount<>0))";
            $_query .= " from t_main t, m_commodity c where t.act_date>='$start_date' and t.act_date<='$end_date'";
            $_query .= " and c.code=t.com_code and c.depletion_flag<>'1'";
            $_query .= " and t.ship_sec_code='$sc' group by t.com_code order by t.com_code";

            if(!$this->sql->query($_query,SQL_ALL))
            {
                print $_query;
                return false;
            }

            $total = 0;

            foreach($this->sql->record as $rec)
            {
                $total += $rec[1];
            }

            //
            //  �׻��߸ˡ�ê���߸ˡ��Ȥ��뾦��
            //
            $_query = "select t.com_code," ;
            $_query .= "(select total_price from t_main where com_code=t.com_code ";
            $_query .= " and act_date='$start_date' and act_flag='0')";
            $_query .= " from t_main t, m_commodity c where t.act_date>='$start_date' and t.act_date<='$end_date'";
            $_query .= " and c.code=t.com_code and c.depletion_flag='1'";
            $_query .= " and t.ship_sec_code='$sc' group by t.com_code order by t.com_code";

            if(!$this->sql->query($_query,SQL_ALL))
            {
                print $_query;
                return false;
            }

            foreach($this->sql->record as $rec)
            {
                $total += $rec[1];
            }

            $ssn = $this->shipment_section_names[$key];
            $this->counted_invs[] = array($ssn,round($total,0));
        }
        return true;
    }

    //
    //      ���פη׻�����̤� this->depletion ������
    //
    function queryDepletion($start_date,$end_date)
    {
        // �������
        $this->depletion = array();
        // �ǽ�ϻ����Υ롼�פǤ���
        foreach($this->shipment_section_codes as $key => $sc)
        {
            $ssn = $this->shipment_section_names[$key];
            if($this->ck_flags[$ssn] == '1')
            {
                // ����ȥ�륭�å���ʤΤǸ��פ򥼥�Ȥ��롣
                $ssn = $this->shipment_section_names[$key];
                $this->depletion[] = array($ssn,0);
            }
            else
            {
                //
                // ������ˤ���ȥ�󥶥�����󤫤龦�ʥ����ɤ���С�
                // ����˸��ץե饰������å����Ƥ��뤳�Ȥ���ա�
                //
                
                $_query  = "select distinct t.com_code from t_main t, m_commodity c ";
                $_query .= " where t.act_date>='$start_date' and t.act_date<='$end_date' ";
                $_query .= " and c.code = t.com_code";
                $_query .= " and t.ship_sec_code='$sc' and c.depletion_flag='0' and t.deleted is null order by t.com_code";
                
                // print $_query;
                
                if($this->sql->query($_query,SQL_ALL) && $this->sql->record != null)
                {
                    // �ȥ�󥶥�����󤢤�ޤ�����
                    $commodity_codes = $this->sql->record;

                    $depletion_total = 0;

                    foreach($commodity_codes as $code)
                    {
                        if($this->calcDepletionByCommodity($code[0],$start_date,$end_date,&$dep))
                        {
                            $depletion_total += $dep;
                        }
                    }
                    $ssn = $this->shipment_section_names[$key];
                    $this->depletion[] = array($ssn,round($depletion_total,0));

                }
                else
                {
                    // ���󤬤����Τǡ�
                    $ssn = $this->shipment_section_names[$key];
                    $this->depletion[] = array($ssn,0);
                }
            }
        }
        return true;
    }

    //
    // �����⤫�ġ����꾦�ʤθ��פ���������롣
    //
    function calcDepletionByCommodity($code,$sd,$ed,&$depletion)
    {
        $depletion = 0;
        $init_price  = 0;
        $init_amount = 0;

        //  yyyy/mm/dd format.
        $num_of_month = substr($ed,5,2) - substr($sd,5,2);
        $cm = $sd;   // current month.

        // ��ǥ롼��
        for($i=0; $i<=$num_of_month; $i++)
        {
            //
            // ��κǽ��������롣
            //
            $last = OlutApp::getLastDate(substr($cm,0,4),substr($cm,5,2));
            $last_of_cm = substr($cm,0,8) . $last;

            // ����߸�
            $_query = "select amount,total_price from t_main where act_flag=0 and com_code=$code and act_date='$cm'";
            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record != null)
            {
                $init_amount = $this->sql->record[0];
                $init_price  = $this->sql->record[1];
            }
            else
            {
                $init_amount = 0;
                $init_price  = 0;
            }

            // ������߸ˡ���ñ�̤ΰ�ưʿ�Ѥ���롣

            // ���ˤζ�ۤȿ���
            $_query  = "select sum(amount),sum(total_price) from t_main where act_flag=1 and com_code=$code ";
            $_query .= " and act_date>='$cm' and act_date<='$last_of_cm'";

            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record == null)
            {
                $arrival_price  = 0;
                $arrival_amount = 0;
            }
            else {
                $arrival_price  = $this->sql->record[1];
                $arrival_amount = $this->sql->record[0];
            }

            if($arrival_amount + $init_amount != 0)
            {
                // ʿ��ñ����
                $avg_price = round(($arrival_price+$init_price)/($arrival_amount+$init_amount),2);
            }
            else
            {
                $avg_price = 0;
            }

            // �вٿ���
            $_query  = "select sum(-amount) from t_main where (act_flag=5 or act_flag=6) and com_code=$code ";
            $_query .= " and act_date>='$cm' and act_date<='$last_of_cm'";

            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record == null)
            {
                $ship_amount = 0;
            }
            else {
                //
                $ship_amount = $this->sql->record[0];
            }

            //
            // ����ê������
            //

            $next_month = OlutApp::getNextMonth($cm);
            $_query = "select amount,total_price from t_main where act_flag=0 and com_code=$code and act_date='$next_month'";
            if($this->sql->query($_query,SQL_INIT)==false)
            {
                return false;
            }

            if($this->sql->record != null)
            {
                $inventory = $this->sql->record[0];
            }
            else {
                $inventory = 0;
            }

            //
            $depletion += ($inventory - ($arrival_amount + $init_amount - $ship_amount)) * $avg_price;

            // print "$i,$inventory,$arrival_amount,$ship_amount,$avg_price\n";

            $cm = $next_month;
        }
        return true;        // done.
    }

    /*
    *  ���ˤ��䤤��碌
    */

    function queryMonthlyArrivalOfGoods($start_date,$end_date)
    {
        $_query = "select ss.name,sum(total_price) ";
        $_query .= " from t_main t, m_shipment_section ss ";
        $_query .= " where ss.code=t.ship_sec_code and (t.act_flag='1' or t.act_flag='2' or t.act_flag='3')";  // ���Υե饰�Ǥ����Τ��ʡ���
        $_query .= " and t.act_date>='$start_date'";
        $_query .= " and t.act_date<='$end_date'";
        $_query .= " group by ss.name,ss.isdcd";
        $_query .= " order by ss.isdcd";
        //
        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  ��Ⱦʬ�η��ϰϥ�ݡ��Ȥ������
    */

    function printMonthlyReport()
    {
        $this->setupMonthlyRange();
        $start_y = 25;
        return $this->printReportBody($start_y,false);
    }

    /*
    *  ��Ⱦʬ��ǯ�ϰϤΥ�ݡ��Ȥ������
    */

    function printAnnualReport()
    {
        $this->setupAnnualRange();
        $start_y = $this->current_y + 10;
        return $this->printReportBody($start_y,true);
    }

    /*
    *  ��ݡ������Τΰ�����
    */

    function printReportBody($start_y,$is_annual)
    {
        $h = $this->cell_height;
        //
        // 4ǯʬ�νв٤���롣
        //
        for($i=0; $i>-4; $i--)
        {
            if( $this->queryMonthlyShipments($this->start_date[$i],$this->end_date[$i]) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $ships[$i][$rec[0]] = $rec[1];
                }
            }
        }
        //
        //  4ǯʬ������ޤǤκ߸˶�ۤ���롣
        //
        for($i=0; $i>-4; $i--)
        {
            $last_start = OlutApp::getPrevMonth($this->start_date[$i]);
            $last_end = OlutApp::getPrevMonth($this->end_date[$i]);

            if( $this->queryMonthlyInventories($last_start) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $prev_invs[$i][$rec[0]] = $rec[1];
                }
            }
        }

        /*
        *  ǯ���߷פǤʤ���������ê���߸ˤ���롣��̤ϥ��饹�ѿ�������롣
        */

        if($is_annual==false)
        {
            //
            //  4ǯʬ�κ߸˶�ۤ���롣
            //
            for($i=0; $i>-4; $i--)
            {
                if( $this->queryMonthlyInventories($this->start_date[$i]) == true )
                {
                    foreach($this->sql->record as $rec)
                    {
                        $this->invs[$i][$rec[0]] = $rec[1];
                    }
                }
            }
        }

        //
        //  4ǯʬ�η׻��߸ˤ���롣
        //
        //for($i=0; $i>-4; $i--)
        //{
        //    if( $this->queryCountedInventories($this->start_date[$i],$this->end_date[$i]) == true )
        //    {
        //        foreach($this->counted_invs as $rec)
        //        {
        //            $counted_invs[$i][$rec[0]] = $rec[1];
        //        }
        //    }
        //}
        
        $depletion = array();

        // 2ǯʬ�θ��פ���롣
        for($i=0; $i>-2; $i--)
        {
            // 
            if($this->queryDepletion($this->start_date[$i],$this->end_date[$i])==true)
            {
                foreach($this->depletion as $rec)
                {
                    $depletion[$i][$rec[0]] = $rec[1];
                }
            }
        }


        // 4ǯʬ�����˶�ۤ���롣
        for($i=0; $i>-4; $i--)
        {
            if( $this->queryMonthlyArrivalOfGoods($this->start_date[$i],$this->end_date[$i]) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $arg[$i][$rec[0]] = $rec[1];
                }
            }
        }

        // 4ǯʬ�Υ���ץ�и˶�ۤ���롣
        for($i=0; $i>-4; $i--)
        {
            if( $this->querySampleShipments($this->start_date[$i],$this->end_date[$i]) == true )
            {
                foreach($this->sql->record as $rec)
                {
                    $samples[$i][$rec[0]] = $rec[1];
                }
            }
        }

        // ��ñ�̤˰������ϡ�
        $this->current_y = $start_y;

        foreach($this->shipment_section_names as $ssn)
        {
            //
            //  ��������̾�����
            //
            $this->pdf->SetFont(GOTHIC,'',10);
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$ssn);

            $this->pdf->SetFont(GOTHIC,'',9);

            // ===== COL 1 ======
            //
            //  ��ǯ����νвٹ��
            //
            $v = $ships[0][$ssn];
            $this->pdf->SetXY($this->x_pos[1],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($v,11));
            $totals[0][0] += $v;

            //
            //  ��ǯ����νвٹ��
            //
            $v = $ships[-1][$ssn];
            $this->pdf->SetXY($this->x_pos[1],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($v,11));
            $totals[-1][0] += $v;

            // ====== COL 2 ======
            // ��ǯ��ξ��ʡ������ϴ����-����ǯ���ϴ����
            $ratio1 =  $ships[0][$ssn] -  $ships[-1][$ssn];
            $this->pdf->SetXY($this->x_pos[2],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($ratio1,11));
            $totals[0][1] += $ratio1;

            // ��ǯ��β��ʡ������ϴ����-����ǯ���ϴ����   ==> ���ס�
            //$ratio2 =  $ships[-1][$ssn] -  $ships[-2][$ssn];
            //$this->pdf->SetXY($this->x_pos[2],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($ratio2,11));
            //$totals[-1][1] += $ratio2;

            // ======= COL3 ========
            // ����
            //
            // �ؿ���=�����ϴ��� / ��ǯƱ��νвٶ�۹�ס�*�����������򾮿��������ǻͼθ���
            if(isset($ships[-1][$ssn]) && ($ships[-1][$ssn] != 0))
            {
                $index = round($ships[0][$ssn]/$ships[-1][$ssn]*100,1);
            }
            else
            {
                $index = 0;
            }
            $this->pdf->SetXY($this->x_pos[3],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            $totals[0][2] += $index;

            //
            // ����
            //
            // �ؿ���=�����ϴ��� / ��ǯƱ��νвٶ�۹�ס�*�����������򾮿��������ǻͼθ���
            //if(isset($ships[-2][$ssn]) && ($ships[-2][$ssn] != 0))
            //{
            //    $index = round($ships[-1][$ssn]/$ships[-2][$ssn]*100,1);
            // }
            //else
            //{
            //    $index = 0;
            //}
            //$this->pdf->SetXY($this->x_pos[3],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            //$totals[-1][2] += $index;

            // ======= COL�� ========
            // ����
            //
            // ����۹�
            $this->pdf->SetXY($this->x_pos[4],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($prev_invs[0][$ssn],11));
            $totals[0][3] += $prev_invs[0][$ssn];

            //
            // ����
            //
            // ����۹�
            $this->pdf->SetXY($this->x_pos[4],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($prev_invs[-1][$ssn],11));
            $totals[-1][3] += $prev_invs[-1][$ssn];

            // ==== COL 5 ======
            // ����
            //
            // ���������
            $this->pdf->SetXY($this->x_pos[5],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($arg[0][$ssn],11));
            $totals[0][4] += $arg[0][$ssn];


            //
            // ����
            //
            // ���������
            $this->pdf->SetXY($this->x_pos[5],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($arg[-1][$ssn],11));
            $totals[-1][4] += $arg[-1][$ssn];

            // ==== COL 6 ======
            // ����
            //
            // ����ץ�и�
            $this->pdf->SetXY($this->x_pos[6],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($samples[0][$ssn],11));
            $totals[0][5] += $samples[0][$ssn];

            //
            // ����
            //
            // ����ץ�и�
            $this->pdf->SetXY($this->x_pos[6],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($samples[-1][$ssn],11));
            $totals[-1][5] += $samples[-1][$ssn];

            // ==== COL 7 ====
            // ����
            //
            // ��»���� ���פ�Ʊ���������׻��߸ˡ�-���º߸ˡ�
            //
            //   �嵭�ϵդΤ褦�˻פ��ޤ����º߸ˡ�-���׻��߸�
            //
            //
            //if($this->ck_flags[$ssn]=='1')      // CK �ϡ���»�ʤ���
            //{
            //    $loss1 = 0;
            //}
            //else
            //{
            //    $loss1 =  $this->invs[0][$ssn] - $counted_invs[0][$ssn];
            //}

            $loss1 = $depletion[0][$ssn];
            //
            $this->pdf->SetXY($this->x_pos[7],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($loss1,11));
            $totals[0][6] += $loss1;

            //
            // ����
            //
            // ��»����
            //if($this->ck_flags[$ssn]=='1')
            //{
            //    $loss2 = 0;
            //}
            //else
            //{
            //    $loss2 = $this->invs[-1][$ssn] - $counted_invs[-1][$ssn];
            //}

            $loss2 = $depletion[-1][$ssn];

            $this->pdf->SetXY($this->x_pos[7],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($loss2,11));
            $totals[-1][6] += $loss2;

            // ����ǯ����»��׻����Ƥ����ޤ���
            //if($this->ck_flags[$ssn]=='1')
            //{
            //    $loss3 = 0;
            //}
            //else
            //{
            //    $loss3 = $this->invs[-2][$ssn] - $counted_invs[-2][$ssn];
            //}

            // ==== COL 8 ====
            // ����
            // ����ê��
            //
            // ��ա�����ê����ǯ�߷פǤ��ַפǤ�Ʊ����Τ��������Τǡ�$this->invs�򻲾Ȥ��롣
            //
            $this->pdf->SetXY($this->x_pos[8],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($this->invs[0][$ssn],11));
            $totals[0][7] += $this->invs[0][$ssn];

            //
            // ����
            //
            // ����ê��
            $this->pdf->SetXY($this->x_pos[8],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($this->invs[-1][$ssn],11));
            $totals[-1][7] += $this->invs[-1][$ssn];

            // ==== COL 9 ====
            // ����
            // ���׹�=���ϴ��ꡡ- ( ����۹⡡�ܡ���������� - ����ê�����ˡܾ��ʸ��ܡ�-����»���ʸ��ס�

            $profit1 = $ships[0][$ssn] - ($prev_invs[0][$ssn] + $arg[0][$ssn] - $this->invs[0][$ssn]) - $loss1 + $samples[0][$ssn];
            $this->pdf->SetXY($this->x_pos[9],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($profit1,11));
            $totals[0][8] += $profit1;

            // ����
            // ���׹�=���ϴ��ꡡ- ( ����۹⡡�ܡ���������� - ����ê�����ˡܾ��ʸ��ܡ�-����»���ʸ��ס�

            $profit2 = $ships[-1][$ssn] - ($prev_invs[-1][$ssn] + $arg[-1][$ssn] - $this->invs[-1][$ssn]) - $loss2 + $samples[-1][$ssn];
            $this->pdf->SetXY($this->x_pos[9],$this->current_y+$h/2);
            $this->pdf->Write(20,OlutApp::formatNumber($profit2,11));
            $totals[-1][8] += $profit2;

            // ����ǯ�κ��׹��׻����Ƥ�����
            $profit3 = $ships[-2][$ssn] - ($prev_invs[-2][$ssn] + $arg[-2][$ssn] - $this->invs[-2][$ssn]) - $loss3 + $samples[-2][$ssn];

            // ==== COL 10 ====
            // ����
            // ��ǯ�桡   -> ���׹⡡-����ǯƱ��κ��׹�

            $ratio1 = $profit1 - $profit2;
            $this->pdf->SetXY($this->x_pos[10],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($ratio1,11));
            $totals[0][9] += $ratio1;

            // ��ǯ��β��ʤ��פ�ʤ���
            // ����
            $ratio2 = $profit2 - $profit3;
            //$this->pdf->SetXY($this->x_pos[10],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($ratio2,11));
            $totals[-1][9] += $ratio2;
            //
            // ==== COL 11 ====
            //
            // �ؿ��ẹ�׹⡡ / ��ǯƱ��κ��׹��ס�*�����������򾮿��������ǻͼθ���
            //
            if($profit2 != 0)
            {
                $index = round($profit1 / $profit2 * 100,1);
            }
            else
            {
                $index = 0;
            }
            $this->pdf->SetXY($this->x_pos[11],$this->current_y);
            $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            $totals[0][10] += $index;

            //if($profit3 != 0)
            //{
            //    $index = round($profit2 / $profit3 * 100,1);
            //}
            //else
            //{
            //    $index = 0;
            //}
            //$this->pdf->SetXY($this->x_pos[11],$this->current_y+$h/2);
            //$this->pdf->Write(20,OlutApp::formatNumber($index,11,1));
            //$totals[-1][10] += $index;

            // ���ιԤء�
            $this->current_y += ($h+1);
        }

        // �ȡ��������
        $this->printTotals($totals);

        return true;
    }

    /*
    *  �����Υȡ��������
    *
    */

    function printTotals($totals)
    {
        $h = $this->cell_height;
        //
        //  ��������̾�����
        //
        $this->pdf->SetFont(GOTHIC,'',10);
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"��������");

        // chage font here....
        $this->pdf->SetFont(GOTHIC,'',9);
        // ===== COL 1 ======
        //
        //  ��ǯ����νвٹ��
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][0],11));

        //
        //  ��ǯ����νвٹ��
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][0],11));

        // ====== COL 2 ======
        // ��ǯ��ξ��ʡ������ϴ����-����ǯ���ϴ����
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][1],11));

        // ��ǯ��β��ʡ������ϴ����-����ǯ���ϴ����  ==> ����
        //$this->pdf->SetXY($this->x_pos[2],$this->current_y+$h/2);
        //$this->pdf->Write(20,OlutApp::formatNumber($totals[-1][0],11));

        // ======= COL3 ========
        // ����
        //
        // �ؿ���=�����ϴ��� / ��ǯƱ��νвٶ�۹�ס�*�����������򾮿��������ǻͼθ���

        if(isset($totals[-1][0]) && ($totals[-1][0] != 0))
        {
            $index = round($totals[0][0]/$totals[-1][0]*100,1);
        }
        else
        {
            $index = 0;
        }
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));

        //
        // ����
        //
        // �ؿ���=�����ϴ��� / ��ǯƱ��νвٶ�۹�ס�*�����������򾮿��������ǻͼθ���

        // �֥�󥯤Ǥ����Ϥ���

        // ======= COL�� ========
        // ����
        //
        // ����۹�
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][3],11));

        //
        // ����
        //
        // ����۹�
        $this->pdf->SetXY($this->x_pos[4],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][3],11));

        // ==== COL 5 ======
        // ����
        //
        // ���������
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][4],11));


        //
        // ����
        //
        // ���������
        $this->pdf->SetXY($this->x_pos[5],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][4],11));

        // ==== COL 6 ======
        // ����
        //
        // ����ץ�и�
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][5],11));

        //
        // ����
        //
        // ����ץ�и�
        $this->pdf->SetXY($this->x_pos[6],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][5],11));

        // ==== COL 7 ====
        // ����
        //
        // ��»���� ���פ�Ʊ���������׻��߸ˡ�-���º߸ˡ�
        //
        $this->pdf->SetXY($this->x_pos[7],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][6],11));

        //
        // ����
        //
        // ��»����
        $this->pdf->SetXY($this->x_pos[7],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][6],11));

        // ==== COL 8 ====
        // ����
        // ����ê��
        $this->pdf->SetXY($this->x_pos[8],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][7],11));

        //
        // ����
        //
        // ����ê��
        $this->pdf->SetXY($this->x_pos[8],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][7],11));

        // ==== COL 9 ====
        // ����
        // ���׹�=���ϴ��ꡡ- ( ����۹⡡�ܡ���������� - ����ê�����ˡܾ��ʸ��ܡ�-����»���ʸ��ס�

        $this->pdf->SetXY($this->x_pos[9],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][8],11));

        // ����
        // ���׹�=���ϴ��ꡡ- ( ����۹⡡�ܡ���������� - ����ê�����ˡܾ��ʸ��ܡ�-����»���ʸ��ס�

        $this->pdf->SetXY($this->x_pos[9],$this->current_y+$h/2);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[-1][8],11));

        // ==== COL 10 ====
        // ����
        // ��ǯ�桡   -> ���׹⡡-����ǯƱ��κ��׹�

        $this->pdf->SetXY($this->x_pos[10],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($totals[0][9],11));

        // �������ס�
        //$this->pdf->SetXY($this->x_pos[10],$this->current_y+$h/2);
        //$this->pdf->Write(20,OlutApp::formatNumber($totals[-1][9],11));

        //
        // ==== COL 11 ====
        //
        // �ؿ��ẹ�׹⡡ / ��ǯƱ��κ��׹��ס�*�����������򾮿��������ǻͼθ���
        //
        if($totals[-1][8] != 0)
        {
            $index = round($totals[0][8] /$totals[-1][8] * 100,1);
        }
        else
        {
            $index = 0;
        }
        $this->pdf->SetXY($this->x_pos[11],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($index,11,1));

        // ���ʤϥ֥�󥯤Ǥ����Ϥ���

        // ���ιԤء�
        $this->current_y += ($h+1);
    }

    /*
    *  �߷��ѤΥإ��������
    *
    */
    function printMiddleHeader()
    {

        $this->current_y += 5;

        $this->pdf->SetFont(GOTHIC,'',12);

        $header = array("���ߡ��ס�","���ϴ���","��ǯ��",
        "�ؿ�","��ǯ���۹�","����������","���ʸ���","��»��","����ê��","���׹�","��ǯ��","�ؿ�");

        for($i=0; $i<12;$i++)
        {
            $this->pdf->SetXY($this->x_pos[$i],$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }

        // �������
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+5 ,$width,$this->current_y+5 );
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);
    }
}

?>