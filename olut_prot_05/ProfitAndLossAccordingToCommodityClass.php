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
*    ������и˺߸�ɽ -  ProfitAndLossAccordingToCommodityClass.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*    2005/10/24 ver 1.00.01 1.�ڡ����������ʾ���ȼ��ڡ����ˤ��֤�Х�������
*                           2.���դ������������äƤ��ʤ�����˼����̵������
*                             ��ɽ������Х�������
*    2005/10/25 ver 1.00.02 �����פθ�˥ڡ����ؤ���
*    2005/11/16 ver 1.00.03 ǯ��κ߸ˤ�ɽ�������Զ�������
*    2006/01/11 ver 1.00.04 ���׷׻��Ρ�!)���ա�
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
class ProfitAndLossAccordingToCommodity_SQL extends SQL
{
    function ProfitAndLossAccordingToCommodity_SQL()
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
class ProfitAndLossAccordingToCommodity_Smarty extends Smarty
{
    function ProfitAndLossAccordingToCommodity_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

/*
*  �����Ծ�����ݻ����롣
*/

class LineInfo
{
    var $code;
    var $name;
    var $ship_sec_code;             // ����⥳����
    var $depletion_flag;            // ���ץե饰
    var $ship_amount;
    var $ship_price;
    var $arrival_amount;
    var $arrival_price;

    var $last_inventory_amount;     // ����߸�
    var $last_inventory_price;
    var $last_inventory_unit_price;

    var $inventory_amount;          // ����߸�
    var $inventory_price;
    var $inventory_unit_price;

    var $discount_price;
    var $discount_amount;
    var $sample_price;
    var $sample_amount;
}


class ProfitAndLossAccordingToCommodity extends OlutApp
{
    // ��ΰ��ִط������
    var $paper_width  = OLUT_B4_WIDTH;  // paper size in portlait.(not landscape)
    var $paper_height = OLUT_B4_HEIGHT; // paper size in portlait.(not landscape)
    var $left_margin  = 5.0;
    var $right_margin = 5.0;
    var $bottom_margin = 10.0;
    var $header_height = 25; // 15.0;
    var $cell_height   = 0;
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

    var $num_of_item_per_page = 12;     // ���Ԥ˰�������ǡ����ιԿ���(�إ���1�Խ�����
    var $section_total = array();       // ����ι�ס�
    var $total = array();               // ������ι�ס�
    var $material_total = 0;            // ������Υȡ����롣�������ʤ������
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $target_year;
    var $target_month;
    var $start_date;
    var $end_date;
    var $page_no = 1;
    var $current_ship_sec_code;         // ���ߤλ���⥳����

    // ctor
    function ProfitAndLossAccordingToCommodity()
    {
        $this->sql =& new ProfitAndLossAccordingToCommodity_SQL;
        $this->tpl =& new ProfitAndLossAccordingToCommodity_Smarty;


        // ���ܰ����������ꡣ
        // LAND SCAPE ����
        $paper_width  = $this->paper_height;
        $paper_height = $this->paper_width;

        //
        // ��ʿ������
        //
        $width = $paper_width - $this->right_margin - $this->left_margin;
        $x_delta = $width  / 12;

        $this->x_pos = array(5,44.5,64,96.5,135,175,200,220,250,280,310,340,359);
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

        // ���׳��ϡ�

        if(!$this->getInventoriesOfLastMonth())
        {
            print "error inventory sql";
            return;
        }

        if(!$this->getShipments())
        {
            print "error shioments sql";    // make error smarty template @later.
            return;
        }
        if(!$this->getArrivals())
        {
            print "error arrivals sql";
            return;
        }

        if(!$this->getSampleShipments())
        {
            print "sample error";
            return;
        }

        if(!$this->getDiscounts())
        {
            print "error discount sql";
            return;
        }

        if(!$this->getInventories())
        {
            print "error inventory sql";
            return;
        }

        // ����̾�ʤɥե饰��򥻥å�
        $this->getCommodityInfo();

        // ������ˡ�ΰ㤦CK�����롣
        $this->getCKCode();

        //
        //  �������ϡ־��ʡ�
        //

        //
        // B4�λ極��������ꤷ�Ƥ��ޤ�������ϥݡ��ȥ졼�ȡ�
        //
        $this->pdf=new MBFPDF('P','mm',array($this->paper_width,$this->paper_height));

        $this->pdf->AddPage('L');
        $this->pdf->SetRightMargin($this->right_margin);
        $this->pdf->SetLeftMargin($this->left_margin);
        $this->pdf->SetTopMargin(0);
        $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');

        if(isset($this->lines))
        {
            //
            $this->drawTitle();
            $this->drawMesh();
            $this->drawHeader();

            // Y���֤����ꡣ
            $this->current_y = $this->header_height;
            $this->current_y += $this->cell_height;

            if($this->line_info != null)
            {

                // sort line info??
                ksort($this->line_info);

                $this->lines = 0;
                // ��̤��������롼�ס�
                foreach($this->line_info as $line)
                {
                    //
                    if($this->current_ship_sec_code==null)
                    {
                        $this->current_ship_sec_code = $line->ship_sec_code;
                    }

                    // ����⤬�Ѥ�ä���
                    if($this->current_ship_sec_code != $line->ship_sec_code)
                    {
                        $this->checkPageChange(1);
                        $this->printSectionTotal();
                        $this->current_y += $this->cell_height;
                        $this->current_ship_sec_code = $line->ship_sec_code;
                        
                        // added 2005/10/25
                        $this->forcePageChange();
                    }

                    $this->checkPageChange(1);
                    $this->printLine($line);
                    $this->current_y += $this->cell_height;
                }

                // �����פ������
                $this->checkPageChange(1);
                $this->printSectionTotal();
                $this->current_y += $this->cell_height;

                //
                // ���פ������
                //
                $this->checkPageChange(1);
                $this->printTotal();
            }

            $this->pdf->Output();
        }
        else
        {
            print "no data";
        }

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
        $this->tpl->display('ProfitAndLossAccordingToCommodityForm.tpl');
    }
    
    /*
     *  �ڡ����ؤ��Υ����å��ȼ¹ԡ�
     *  lines�������ꤷ���ꥤ�󥯥���Ȥ���Τ���ա�
     *
     */
    function checkPageChange($lines_to_add)
    {
        if(($this->lines+$lines_to_add) > $this->num_of_item_per_page)
        {
            $this->forcePageChange();
            $this->lines = 1;

        }
        else {
            $this->lines += $lines_to_add;
        }
    }
    
    /*
     *  �����ڡ����ؤ�
     */
    function forcePageChange()
    {
        $this->pdf->AddPage('L');
        $this->pdf->SetTopMargin(0);
        // Y���֤����ꡣ
        $this->current_y = $this->header_height;

        //
        $this->drawTitle();
        $this->drawMesh();
        $this->drawHeader();

        $this->current_y += $this->cell_height;
        $this->lines = 0;        
    }

    /*
    *  ���դ��ط������ꡣ
    */

    function setupDates($formvars)
    {
        // �ʲ��ϲ������Ϥˤʤä��ΤǺ����
        //OlutApp::getCurrentProcessDate($this->sql,&$this->target_year,&$this->target_month);
        //$this->target_year = date('Y');
        //$this->target_month = "06"; // date('m');
        
        $this->target_year  = $formvars['target_year'];
        $this->target_month = $formvars['target_month'];
        
        // 0 ��������ɬ�פǤ�����2005/10/22
        $this->start_date = sprintf("%04d/%02d/01",$this->target_year,$this->target_month);
        $last = OlutApp::getLastDate($this->target_year,$this->target_month);
        $this->end_date   = sprintf("%04d/%02d/%02d",$this->target_year,$this->target_month,$last);
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

        $x = 0;
        $y = 10;

        $h = 10;
        //
        $this->pdf->SetFont(GOTHIC,'',14);
        $this->pdf->SetXY(130,$y);
        $this->pdf->Write($h,"$this->target_year ǯ $this->target_month �������и˺߸�ɽ");
        $this->pdf->SetXY(280,$y);
        $this->pdf->Write($h, "DATE: $year/$month/$date    PAGE: $this->page_no",'',1,'R');
        $this->page_no++;

    }

    /*
    *   ����̾�ʤɡ��إ�����1�Ԥ����񤯡�
    */

    function WriteHeaderCell($x_index,$title)
    {
        $this->pdf->SetFont(GOTHIC,'',12);
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$title);
    }

    /*
    *   ���ʲ�žΨ���ѡ�
    */

    function WriteHeaderDoubleLineCell($x_index,$title1,$title2)
    {
        $this->pdf->SetFont(GOTHIC,'',12);
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $h = $this->cell_height/2;

        $len = $this->pdf->GetStringWidth($title1);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$title1);

        $y += $h;
        $len = $this->pdf->GetStringWidth($title2);
        $offset = ($cell_width - $len)/2 - 1;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$title2);

    }
    /*
    * ��ۤΤ�ñ��ɽ���Υإ������롣��3ʬ�䡣��2ʬ�䡣����ʸ���ϸ���ǡ��ֿ��̡�ñ��(�ߡˡ����(�ߡ�
    *
    */
    function WriteHeaderMoneyAndUnitPriceCell($x_index,$title)
    {
        // ����ι⤵��3ʬ�䡣
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x,$y+$ch);
        $this->pdf->Write($ch,"   ����");

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x+$cell_width/2,$y+$ch);
        $this->pdf->Write($ch," ñ��(��)");

        $this->pdf->line($x,$y+$ch*2,$x+$cell_width,$y+$ch*2);

        $str = "�⡡��(��)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch*2);
        $this->pdf->Write($ch,$str);

        // �ĤΥ饤��
        $x += $cell_width/2;
        $this->pdf->line($x, $y+$ch, $x, $y+$ch*2);

    }
    /*
    *������ۤ�����ñ����̵�����롣3�ʡ��ĤΥ饤��ʤ���
    *
    */
    function WriteHeaderMoneyCell($x_index,$title)
    {
        // ����ι⤵��3ʬ�䡣
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $str = "����";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x+$offset,$y+$ch);
        $this->pdf->Write($ch,$str);

        $this->pdf->line($x,$y+$ch*2,$x+$cell_width,$y+$ch*2);

        $str = "�⡡��(��)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch*2);
        $this->pdf->Write($ch,$str);

    }

    /*
    *��������פΥ��롣3�ʡ��ĤΥ饤��ʤ���
    *
    */
    function WriteHeaderBalanceCell($x_index,$title)
    {
        // ����ι⤵��3ʬ�䡣
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $str = " 1��������(��)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',9);
        $this->pdf->SetXY($x+$offset,$y+$ch);
        $this->pdf->Write($ch,$str);

        $this->pdf->line($x,$y+$ch*2,$x+$cell_width,$y+$ch*2);

        $str = "�⡡��(��)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch*2);
        $this->pdf->Write($ch,$str);

    }
    /*
    *����2�ʡ�����ץ�Υ��롣
    *
    */
    function WriteHeaderDiscountCell($x_index,$title)
    {
        // ����ι⤵��3ʬ�䡣
        $ch = $this->cell_height / 3;
        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->header_height;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($ch,$title);

        $this->pdf->line($x,$y+$ch,$x+$cell_width,$y+$ch);

        $str = "�� ��(��)";
        $len = $this->pdf->GetStringWidth($str);
        $offset = ($cell_width - $len)/2 - 1;

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY($x+$offset,$y+$ch);
        $this->pdf->Write($ch*2,$str);
    }

    /*
    *
    */

    function drawHeader()
    {
        //
        // ����( $this->left_margin, $this->header_height ) ����إ�����񤯡�
        // ���Υإ����ϥ롼�פ��ƽ񤱤�Ȥ������Ȥ�̵���Ȼפ���

        $this->WriteHeaderCell(0,"����̾");
        $this->WriteHeaderCell(1,"ñ��̾");
        $this->WriteHeaderMoneyAndUnitPriceCell(2,"����۹�");
        $this->WriteHeaderMoneyAndUnitPriceCell(3,"�������˹�");
        $this->WriteHeaderMoneyAndUnitPriceCell(4,"����и˹�");
        $this->WriteHeaderMoneyCell(5,"�����ץ�");
        $this->WriteHeaderDiscountCell(6,"�����Ͱ�");
        $this->WriteHeaderMoneyAndUnitPriceCell(7,"����߸˹�");
        $this->WriteHeaderMoneyCell(8,"�����");
        $this->WriteHeaderMoneyAndUnitPriceCell(9,"����");
        $this->WriteHeaderBalanceCell(10,"�����");
        $this->WriteHeaderDoubleLineCell(11,"����","��žΨ");
    }

    /*
    *������12���롢��15���롣�ʺǾ��ʤϥإ�����
    */

    function drawMesh()
    {
        // LAND SCAPE ����
        $paper_width  = $this->paper_height;
        $paper_height = $this->paper_width;

        //
        // ��ʿ������
        //
        $width = $paper_width - $this->right_margin - $this->left_margin;
        $y = $this->header_height;
        $cell_height = ($paper_height - $this->header_height - $this->bottom_margin )/14.0;

        for($i=0; $i<14; $i++)
        {
            // �������
            $this->pdf->line($this->left_margin,$y,$width+$this->left_margin,$y);
            $y += $cell_height;
        }

        // ��ľ����
        $y = $this->header_height;
        $x_delta = $width  / 12;
        $height  = $cell_height*14.0 + 9.0 ;  // +4.0 �������

        for($i=0; $i<13; $i++)
        {
            $x = $this->x_pos[$i];
            $this->pdf->line($x,$y,$x,$height);
        }

        // ���饹�ѿ����᤹��
        $this->cell_height = $cell_height;
        $this->cell_width  = $x_delta;
    }


    /*
    *   ����̾�ʤɡ��إ�����1�Ԥ����񤯡�
    */

    function WriteCell($x_index,$title)
    {
        $this->pdf->SetFont(GOTHIC,'',12);

        $cell_width = $this->x_pos[$x_index+1] - $this->x_pos[$x_index];

        $len = $this->pdf->GetStringWidth($title);
        $offset = ($cell_width - $len)/2 - 1;

        $x = $this->x_pos[$x_index];
        $y = $this->current_y;
        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($this->cell_height,$title);
    }

    /*
    *  �߸ˤΥ����񤯡�(����۹⡢����߸˹��
    */
    function WriteInventoryCell($index,$amount,$unit_price,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        // �����岼�˳�롣
        $h = $this->cell_height /2;

        // ��κ��˿��̡�
        $x = $this->x_pos[$index] ;
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$formatted_amount");
        $offset = ($cell_width /2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$formatted_amount);

        $unit_price = number_format($unit_price,2);
        //
        $x = $this->x_pos[$index] + $cell_width/2;
        $len = $this->pdf->GetStringWidth("$unit_price");
        $offset = ($cell_width/2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$unit_price);

        // ��ۤ򲼤˱�·����
        $price = number_format($price);
        $x = $this->x_pos[$index];
        $y = $this->current_y + $h;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  ��ۡ����̤Υ����񤯡�(����۹⡢���˹⡢�и˹��ѡ�
    */
    function WriteMoneyAndUnitPriceCell($index,$amount,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        // �����岼�˳�롣
        $h = $this->cell_height / 2;

        // ��κ��˿��̡�
        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$formatted_amount");
        $offset = ($cell_width/2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$formatted_amount);

        // ��α���ʿ��ñ���������Ƿ׻����Ƥ�����Τ���
        if($amount != 0)
        {
            $unit_price = round($price / $amount,2);
            $unit_price = number_format($unit_price,2);
        }
        else
        {
            $unit_price = 0;
        }
        //
        $x = $this->x_pos[$index] + $cell_width/2;
        $len = $this->pdf->GetStringWidth("$unit_price");
        $offset = ($cell_width/2 - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$unit_price);

        // ��ۤ򲼤˱�·����
        $price = number_format($price);
        $x = $this->x_pos[$index] ;
        $y = $this->current_y + $h;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  ñ���Τʤ���ۤΥ����񤯡��ʥ���ץ�в١���
    */
    function WriteMoneyAndAmountCell($index,$amount,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        // �����岼�˳�롣
        $h = $this->cell_height / 2;

        // ��κ��˿��̡�
        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$formatted_amount");
        $offset = ($cell_width - $len - $this->cell_margin);

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$formatted_amount);


        // ��ۤ򲼤˱�·����
        $price = number_format($price);
        $x = $this->x_pos[$index];
        $y = $this->current_y + $h;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  ��ۤΤߤΥ����񤯡����Ͱ�����
    */
    function WriteMoneyCell($index,$amount,$price)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $formatted_amount = number_format($amount,2);
        $formatted_price  = number_format($price,2);

        $h = $this->cell_height;

        // ��ۤ򲼤˱�·����
        $price = number_format($price);
        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$price");
        $offset = $cell_width - $len - $this->cell_margin - 2 ;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$price);
    }

    /*
    *  ���ͤΤߤΥ����񤯡��ʾ��ʲ�žΨ��
    */
    function WriteNumberCell($index,$value)
    {
        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $h = $this->cell_height;

        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$value");
        $offset = $cell_width - $len - $this->cell_margin-2;  // special -2 ?

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$value);
    }

    /*
    *  �ȡ�����Ԥο��ͤΤߤΥ����񤯡�
    */
    function WriteTotalCell($index,$value)
    {
        $value = number_format($value,0);

        $this->pdf->SetFont(GOTHIC,'',$this->number_font_size);
        $cell_width = $this->x_pos[$index+1] - $this->x_pos[$index];

        $h = $this->cell_height;

        $x = $this->x_pos[$index];
        $y = $this->current_y;
        $len = $this->pdf->GetStringWidth("$value");
        $offset = $cell_width - $len - $this->cell_margin;  // special -2 ?

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write($h,$value);
    }
    /*
    *  ��νв٤����롣
    */
    function getShipments()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and (act_flag='5' or act_flag='7')  ";
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            //  $line_info = new LineInfo();
            //
            $line_info->code        = $rec[0];
            $line_info->ship_amount = $rec[1]*(-1);
            $line_info->ship_price  = $rec[2]*(-1);

            $this->line_info[$rec[0]] = $line_info;
        }

        return true;
    }


    /*
    *  ���٤����롣
    */
    function getArrivals()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and (act_flag='1' or act_flag='3')  ";    // �Ͱ����Ͻ�����
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code           = $rec[0];
            $line_info->arrival_amount = $rec[1];
            $line_info->arrival_price  = $rec[2];

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }


    function getInventoriesOfLastMonth()
    {
        //
        //  ���ߤλ��ͤǤ�����Σ�����ê���ǡ��������롣
        //

        $_query = "select com_code, amount, total_price, unit_price from t_main ";
        $_query .= " where act_date = '$this->start_date' ";
        $_query .= " and act_flag='0' ";
        $_query .= " order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code             = $rec[0];
            $line_info->last_inventory_amount = $rec[1];
            $line_info->last_inventory_price  = $rec[2];
            $line_info->last_inventory_unit_price = $rec[3];

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }
    /*
    * ����߸ˤ����롣
    */

    function getInventories()
    {
        //
        //  ���ߤλ��ͤǤ����Σ�����ê���ǡ��������롣
        //
        
        // 2005/11/16 fix.  ���substr�γ��Ϥ�6 -> 5��������
        
        $inventory_date = OlutApp::getFirstDayOfNextMonth(substr($this->start_date,0,4),substr($this->start_date,5,2));

        $_query  = "select com_code, amount, total_price, unit_price from t_main ";
        $_query .= " where act_date = '$inventory_date' ";
        $_query .= " and act_flag='0' ";
        $_query .= " order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code             = $rec[0];
            $line_info->inventory_amount = $rec[1];
            $line_info->inventory_price  = $rec[2];
            $line_info->inventory_unit_price = $rec[3];

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }

    /*
    *  ����ץ�в٤ξȲ�
    */
    function getSampleShipments()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and act_flag='6' ";
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code          = $rec[0];
            $line_info->sample_amount = $rec[1]*(-1);
            $line_info->sample_price  = $rec[2]*(-1);

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;
    }


    /*
    * �Ͱ����ξȲ�
    */
    function getDiscounts()
    {
        $_query  = "select com_code, sum(amount), sum(total_price) from t_main ";
        $_query .= " where act_date >= '$this->start_date' and act_date <= '$this->end_date' ";
        $_query .= " and act_flag='2' ";
        $_query .= " group by com_code order by com_code";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            return false;
        }

        foreach($this->sql->record as $rec)
        {
            if(isset($this->line_info[$rec[0]]))
            {
                $line_info = $this->line_info[$rec[0]];
            }
            else
            {
                $line_info = new LineInfo();
            }

            $line_info->code            = $rec[0];
            $line_info->discount_amount = $rec[1]*(-1);
            $line_info->discount_price  = $rec[2]*(-1);

            $this->line_info[$rec[0]] = $line_info;
        }
        return true;


    }
    /*
    *  ���ʥ����ɤ��龦��̾�����롣
    */

    function getCommodityName($code,&$name,&$ship_sec_code,&$depletion_flag)
    {
        $_query = "select name, ship_section_code, depletion_flag from m_commodity where code='$code' and deleted is null";
        if($this->sql->query($_query,SQL_INIT)==false)
        {
            return false;
        }

        //

        $name          = $this->sql->record[0];
        $ship_sec_code = $this->sql->record[1];

        if($this->sql->record[2]==null)
        {
            $depletion_flag = '0';
        }
        else
        {
            $depletion_flag = $this->sql->record[2];
        }

        return true;
    }

    /*
    *  ���ʥ����ɤ���ñ��̾�����롣
    */
    function getUnitName($code)
    {
        $_query = "select u.name from m_unit u, m_commodity c where c.unit_code=u.code and c.code='$code' and c.deleted is null";
        if($this->sql->query($_query,SQL_INIT)==false)
        {
            return null;
        }
        return $this->sql->record[0];
    }

    /*
    *   ���԰�����
    */

    function printLine($line)
    {
        // ���ʥ����ɤȾ���̾�������

        $h = $this->cell_height / 2;
        $x = $this->left_margin;
        $y = $this->current_y;

        // ���ͤ�ǥ����ɤ�񤯡�
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->pdf->SetXY($x,$y);
        $this->pdf->Write($h,$line->code);

        // Ʊ��������̾��
        $this->pdf->SetXY($x,$y+$h);
        $this->pdf->Write($h,$line->name);

        // ñ�̡�
        $unit = $this->getUnitName($line->code);
        $this->WriteCell(1,$unit);

        // ����۹�
        $this->WriteInventoryCell(2,$line->last_inventory_amount,$line->last_inventory_unit_price, $line->last_inventory_price);
        $this->section_total[0] += $line->last_inventory_price;

        // �������˹�
        $this->WriteMoneyAndUnitPriceCell(3,$line->arrival_amount,$line->arrival_price);
        $this->section_total[1] += $line->arrival_price;

        // ����и˹�
        $this->WriteMoneyAndUnitPriceCell(4,$line->ship_amount,$line->ship_price);
        $this->section_total[2] += $line->ship_price;

        // ����ץ�
        $this->WriteMoneyAndAmountCell(5,$line->sample_amount, $line->sample_price);
        $this->section_total[3] += $line->sample_price;

        // �Ͱ���
        $this->WriteMoneyCell(6,$line->discount_amount, $line->discount_price);
        $this->section_total[4] += $line->discount_price;

        //if($line->code=='17111')
        //{
           // print "xx";
          // $line->code = '17111';
        //}
        //
        // ����߸˹�  (= �׻��߸�)��
        //

        if($line->ship_sec_code == $this->ck_code || $line->depletion_flag=='1')
        {
            //
            // ����ȥ�륭�å���ο���ޤ��ϻ����ʤʤΤǡ��׻��߸ˡ�����ê���߸ˡ��Ȥ��롣(���פϾ�˥����
            //
            // ���ץե饰���å��оݤȤʤ뾦�ʥ����ɡ�
            //
            //  ���� ����"50002"  "60002"  "70002"  "75002".
            //  ������ ��"29800"  "39800"  "49800"  "89800".
            //
            $inventory_amount = $line->inventory_amount;
            $inventory_price  = $line->inventory_price;

            if($inventory_amount != 0)
            {
                $avg_unit_price = round($inventory_price / $inventory_amount);
            }
            else {
                $avg_unit_price = 0;
            }
        }
        else
        {

            //
            // ���٤Ⱥ߸ˤ���ʿ��ñ�������
            //
            if(($line->last_inventory_amount + $line->arrival_amount) != 0)
            {
                $avg_unit_price = round(($line->last_inventory_price + $line->arrival_price)/($line->last_inventory_amount + $line->arrival_amount),2);
                $avg = ($line->last_inventory_price + $line->arrival_price)/($line->last_inventory_amount + $line->arrival_amount);
            }
            else
            {
                $avg_unit_price = $avg = 0;
            }

            $inventory_amount = $line->last_inventory_amount + $line->arrival_amount - $line->ship_amount - $line->sample_amount;
            $inventory_price  = round($inventory_amount * $avg,0);

            // �ʲ��� N.G.
            // $inventory_price  = $line->last_inventory_price + $line->arrival_price - $line->ship_price - $line->sample_price - $line->discount_price;
            //
        }

        //if($line->code=='85160')
        //{
        //    print "xx";
        // }

        $this->WriteInventoryCell(7,$inventory_amount, $avg_unit_price, $inventory_price);
        $this->section_total[5] += $inventory_price;

        // ���ס��ᡡ�׻��߸ˡ�-���º߸ˡ�
        // 
        // 2006/1/11 �ʲ��Υ����ɤ�դˤ��ޤ�����
        // $loss_amount = $inventory_amount - $line->inventory_amount;
        // $loss_price  = $inventory_price  - $line->inventory_price;
        $loss_amount = $line->inventory_amount - $inventory_amount;
        $loss_price  = $line->inventory_price  - $inventory_price;
        //
        $this->WriteMoneyAndAmountCell(8,$loss_amount,$loss_price);
        $this->section_total[6] += $loss_price;


        // ���� = �������Ϻ߸�
        $this->WriteInventoryCell(9,$line->inventory_amount, $line->inventory_unit_price, $line->inventory_price);
        $this->section_total[7] += $line->inventory_price;

        // ����ס���ϴ��ꡡ- ( ����۹⡡�ܡ���������� - ����ê�����ˡܾ��ʸ��ܡ�-����»���ʸ��ס�
        $balance_amount = $line->ship_amount - ( $line->last_inventory_amount + $line->arrival_amount - $line->inventory_amount) + $line->sample_amount - $loss_amount;
        $balance_price  = $line->ship_price  - ( $line->last_inventory_price  + $line->arrival_price  - $line->inventory_price)  + $line->sample_price  - $loss_price;
        $this->WriteMoneyAndAmountCell(10,$balance_amount,$balance_price);
        $this->section_total[8] += $balance_price;

        // >    - �־��ʲ�žΨ�פη׻���ˡ�򤪶�������������
        // ������������̤����ͼθ����ʽвٷ��������������/���߸˷�����ˡ��Ǥ�
        $rate = 0;
        if( $line->inventory_amount != 0)
        {
            $rate = round($line->ship_amount * 100 / $line->inventory_amount,1);
        }
        $this->WriteNumberCell(11,number_format($rate,2));
    }

    /*
    *  �����פ������
    */

    function printSectionTotal()
    {
        // ���ʥ����ɤȾ���̾�������
        $h = $this->cell_height / 2;
        $x = $this->left_margin;
        $y = $this->current_y;

        $this->pdf->SetFont(GOTHIC,'',12);

        // Ʊ��������̾��
        $this->pdf->SetXY($x,$y+$h/2);
        $this->pdf->Write($h,"��  ��  ��  ��");

        // ����۹�
        $this->WriteTotalCell(2,$this->section_total[0]);

        // �������˹�
        $this->WriteTotalCell(3,$this->section_total[1]);

        // ����и˹�
        $this->WriteTotalCell(4,$this->section_total[2]);

        // ����ץ�
        $this->WriteTotalCell(5,$this->section_total[3]);

        // �Ͱ���
        $this->WriteTotalCell(6,$this->section_total[4]);

        $this->WriteTotalCell(7,$this->section_total[5]);

        $this->WriteTotalCell(8,$this->section_total[6]);

        // ���� = �������Ϻ߸�
        $this->WriteTotalCell(9,$this->section_total[7]);

        // ����ס�
        $this->WriteTotalCell(10,$this->section_total[8]);

        // ���ΤΥȡ������­����
        foreach($this->section_total as $key => $value)
        {
            $this->total[$key] += $value;
            $this->section_total[$key] = 0;    // reset.
        }

    }

    /*
    *  ���פ����
    */

    function printTotal()
    {
        // ���ʥ����ɤȾ���̾�������
        $h = $this->cell_height / 2;
        $x = $this->left_margin;
        $y = $this->current_y;

        $this->pdf->SetFont(GOTHIC,'',12);

        // Ʊ��������̾��
        $this->pdf->SetXY($x,$y+$h/2);
        $this->pdf->Write($h,"��  ��  ��");

        // ����۹�
        $this->WriteTotalCell(2,$this->total[0]);

        // �������˹�
        $this->WriteTotalCell(3,$this->total[1]);

        // ����и˹�
        $this->WriteTotalCell(4,$this->total[2]);

        // ����ץ�
        $this->WriteTotalCell(5,$this->total[3]);

        // �Ͱ���
        $this->WriteTotalCell(6,$this->total[4]);

        $this->WriteTotalCell(7,$this->total[5]);

        $this->WriteTotalCell(8,$this->total[6]);

        // ���� = �������Ϻ߸�
        $this->WriteTotalCell(9,$this->total[7]);

        // ����ס�
        $this->WriteTotalCell(10,$this->total[8]);
    }

    function getCKCode()
    {
        $this->sql->query("select code from m_shipment_section where ck_flag<>'0'",SQL_INIT);
        $this->ck_code = $this->sql->record[0];
    }

    function getCommodityInfo()
    {
        foreach($this->line_info as $key => $line)
        {
            $this->getCommodityName($line->code,&$this->line_info[$key]->name,
            &$this->line_info[$key]->ship_sec_code,
            &$this->line_info[$key]->depletion_flag);
        }
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