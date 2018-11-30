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
*    Ǽ�ʽ���� - StatementsOfDeliveryClass.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*    2005/10/08 ver 1.00.01 �������� -> ���� ���ѹ���
*    2005/12/13 ver 1.00.02 ���դ����¤�ű�ѡ�
*    2005/12/14 ver 1.00.03 ��⤬Ĺ������2�Ԥˡ�
*
*/

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF ���ܸ�Ķ�����
require_once('OlutAppLib.php');
require_once('SystemProfile.php');

// database configuration
class StatementOfDelivery_SQL extends SQL
{
    function StatementOfDelivery_SQL()
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
class StatementOfDelivery_Smarty extends Smarty
{
    function StatementOfDelivery_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

/*
*  FPDF�Ǥ��������ѤΤޤ뤤�ͳѷ��ʤɤ����褹�뵡ǽ��̵���Τ�
*  ��åץ��饹�ǳ�����ǽ���ɲä��Ƥ��ޤ���
*/
class OlutPDF extends MBFPDF   // FPDF
{
    function SetDash($black=false,$white=false)
    {
        if($black and $white)
        $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
        else
        $s='[] 0 d';
        $this->_out($s);
    }

    function RoundedRect($x, $y, $w, $h,$r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
        $op='f';
        elseif($style=='FD' or $style=='DF')
        $op='B';
        else
        $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}

/*
*      �������饹��
*
*/

class StatementOfDelivery extends OlutApp
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
    var $line_height = 6;               // �Ԥ�Y������������
    var $lines = 0;                     // �����Կ���
    var $x_pos;
    var $error = null;
    var $system_profile;

    // ctor
    function StatementOfDelivery()
    {
        $this->sql =& new StatementOfDelivery_SQL();
        $this->tpl =& new StatementOfDelivery_Smarty();
    }

    // �����ᥤ��

    function printOut($codes)
    {
        if(is_array($codes))
        {
            //
            $this->pdf=new OlutPDF('P','mm',array($this->paper_width,$this->paper_height));
            $this->pdf->SetRightMargin($this->right_margin);
            $this->pdf->SetLeftMargin($this->left_margin);
            $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');

            // �����оݤϽ�����˸��ꤹ�롣
            // OlutApp::getCurrentProcessDate($this->sql,$year,$month);
            // $dt = "$year/$month/01";

            $this->system_profile =& new SystemProfile();

            foreach($codes as $code)
            {
                if($code != null)
                {
                    if(!$this->printStatementOfDelivery($code))
                    {
                        return false;
                    }
                }
            }
            //
            $this->sql->disconnect();
            //
            $this->pdf->Output();
            return true;
        }
        return false;
    }

    function printStatementOfDelivery($code)
    {
        if($this->getShipmentData($code))
        {
            //
            $this->preparePDF();
            //
            $this->x_pos = array(10,30,90,125,150,175,200);
            $this->printUpperPart($code);        // Ǽ�ʽ�
            $this->printCenterLine();
            //
            $this->x_pos = array(10,30,90,125,200);
            $this->printLowerPart($code);        // ���ν�
        }
        else
        {
            return false;
        }
        return true;
    }

    /*
    *   ���顼��å��������ϡ�
    */
    function renderErrorScreen()
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

    /*
    *   �в٥ǡ��������롣
    */
    function getShipmentData($code)
    {
        // 2005/12/13
        // ���դ��ˤ�븡�����Ϥʤ��Ȥ��롣
        //
        $_query  = "select t.com_code, c.name, t.amount*(-1), u.name, t.unit_price, t.total_price*(-1),t.memo,st.name,sd.name,t.act_date ";
        $_query .= " from t_main t, m_commodity c, m_unit u, m_store st, m_store_division sd ";
        $_query .= " where t.slip_no='$code' and t.com_code=c.code and u.code=c.unit_code";
        $_query .= "  and t.dest_code = st.code and sd.code=t.store_sec_code";
        $_query .= " order by t.line_no";

        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  PDF ����
    */

    function preparePDF()
    {
        // ����ϥݡ��ȥ졼�ȡ�
        $this->pdf->AddPage('P');
    }

    function printCenterLine()
    {
        // �����塣Bottom Margin �����åȤǤ��ʤ���
        // �ؤ��ڡ�������Τǲ�Ⱦʬ�򾯤��礭��ˤ�����

        $y = $this->paper_height / 2 - 8;
        $w = $this->paper_width - $this->right_margin;
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetXY($this->left_margin,$y);
        $this->pdf->SetDash(2,2);
        $this->pdf->line($this->left_margin, $y, $w, $y);
        $this->pdf->SetDash(0,0);
    }

    /*
    *  Ǽ�ʽ�����
    */

    function printUpperPart($code)
    {
        $this->pdf->SetLineWidth(0.3);

        $this->pdf->SetFont(GOTHIC,'U',12);
        $this->pdf->SetXY(10,10);
        $this->pdf->Write(20,"NO. $code");

        $this->pdf->SetFont(GOTHIC,'U',16);
        $this->pdf->SetXY(70,10);
        $this->pdf->Write(20,"��Ǽ�����ʡ�����");

        $this->pdf->RoundedRect(145,10,56,10,2,'');

        // �������᡼��
        $fn = OLUT_IMAGE_PATH . 'ntc_logo.jpg';
        $this->pdf->Image($fn,143,25,50,15,'jpg');

        // ���β��β��̾
        $this->pdf->SetFont(GOTHIC,'I',9);
        $this->pdf->SetXY(120,40);
        // $this->pdf->Write(10,"���̥ƥ������ȥ졼�ǥ��󥰡������ݥ졼�����");
        $this->pdf->Write(10,$this->system_profile->company_name);

        // ���β��˽���
        $this->pdf->SetFont(GOTHIC,'',8);
        $this->pdf->SetXY(140,45);
        $this->pdf->Write(10,$this->system_profile->company_address_1);
        // "��169-8539 ����Կ��ɶ�ɴ��Į2-25-13");


        // ����Υܥå���
        $this->pdf->RoundedRect(10,27,60,20,2,'');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->pdf->SetXY(55,38);
        $this->pdf->Write(10,"����");

        $this->pdf->SetLineWidth(0.4);
        // ��ʿ��8��
        $w = $this->paper_width - $this->right_margin;
        $y = 55;
        for($i=0; $i<8; $i++)
        {
            $this->pdf->line($this->left_margin,$y,$w,$y);
            $y += 10;
        }

        // ��ľ�� 7��
        $h = 10*8;
        $y = 55;
        for($i=0; $i<7; $i++)
        {
            if($i<3)
            {
                $h = 70;
            }
            else
            {
                $h = 80;
            }
            $this->pdf->line($this->x_pos[$i],$y,$this->x_pos[$i],$y+$h);
        }

        // ��פβ�����
        $this->pdf->line($this->x_pos[3],135,200,135);

        $this->pdf->SetFont(GOTHIC,'',11);
        $y = 55;
        $this->WriteHeaderCell(0,"������",$y);
        $this->WriteHeaderCell(1,"�����ʡ�̾",$y);
        $this->WriteHeaderCell(2,"������",$y);
        $this->WriteHeaderCell(3,"ñ����",$y);
        $this->WriteHeaderCell(4,"�⡡��",$y);
        $this->WriteHeaderCell(5,"Ŧ����",$y);   // fix 2005/12/14

        // ��פȽ񤤤Ƥߤ롣
        $y += 70;
        $this->WriteHeaderCell(3,"��  ��",$y);

        // ��������ǡ����ΰ����롼��
        if( $this->sql->record != null )
        {
            $y = 55;
            $y_index = 1;
            $total = 0;
            //
            foreach($this->sql->record as $rec)
            {
                if($y_index == 1)
                {
                    $this->printStoreName($rec[7],$rec[8],30);        // Ź��̾�����
                    // yyyy/mm/dd
                    // 0123456789
                    $year = substr($rec[9],0,4);
                    $month = substr($rec[9],5,2);
                    $day   = substr($rec[9],8,2);
                    //
                    $this->pdf->SetFont(GOTHIC,'',10);
                    $this->pdf->SetXY(147,10);
                    $this->pdf->Write(10,"������ $year ǯ $month �� $day ��");
                }
                $this->pdf->SetFont(GOTHIC,'',11);
                $this->WriteCell(0,$y_index,$rec[0],$y);    // ������
                $this->WriteCell(1,$y_index,$rec[1],$y);    // ����̾
                $amount = $rec[2] . " " . $rec[3];          // ���̤�ñ�̤򥳥󥫥�
                $this->WriteCellCenter(2,$y_index,$amount,$y);
                $this->WriteNumberCell(3,$y_index,$rec[4],$y);
                $this->WriteNumberCell(4,$y_index,$rec[5],$y);
                $this->WriteMemoCell(5,$y_index,$rec[6],$y);       // memo

                $total += $rec[5];      // ��ۤ�ȡ�����˲û�
                $y_index++;
            }

            // �ȡ���������
            $y_index = 7;
            $this->WriteNumberCell(4,$y_index,$total,$y);
        }
    }

    function WriteHeaderCell($index,$title,$y)
    {
        $x = $this->x_pos[$index];
        $w = $this->x_pos[$index+1] - $x;
        $len = $this->pdf->GetStringWidth($title);

        $offset = ($w-$len)/2-1;

        $this->pdf->SetXY($x+$offset, $y);
        $this->pdf->Write(10,$title);
    }

    /*
    *  ����ɽ��
    */
    function WriteNumberCell($x_index,$y_index,$value, $y_start)
    {
        $value = number_format($value);
        $y = $y_start + $y_index*10;
        $x = $this->x_pos[$x_index];
        $w = $this->x_pos[$x_index+1] - $x;
        $len = $this->pdf->GetStringWidth($value);
        $offset = ($w - $len)-5 ;   // ������

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write(10,$value);
    }

    /*
    *  ���̤�ʸ���󡡡ʺ�������
    */
    function WriteCell($x_index, $y_index, $value, $y_start)
    {
        $y = $y_start + $y_index*10;
        $x = $this->x_pos[$x_index] + 2;

        $this->pdf->SetXY($x,$y);
        $this->pdf->Write(10,$value);
    }
    
    /*
     *      ������ѡ�ʸ��Ĺ������Ķ������2�Ԥ˰�����
     */
    function WriteMemoCell($x_index, $y_index, $value, $y_start)
    {
        $x = $this->x_pos[$x_index] + 2;

        if(mb_strlen($value) <= 8)
        {
            $y = $y_start + $y_index*10;
            $this->pdf->SetXY($x,$y);
            $this->pdf->Write(10,$value);
        }
        else
        {
            $str1 = mb_substr($value,0,8);
            $str2 = mb_substr($value,8);
            
            $y = $y_start + $y_index*10 - 2.5;

            $this->pdf->SetXY($x,$y);
            $this->pdf->Write(10,$str1);

            $y = $y_start + $y_index*10 + 2.5;
            
            $this->pdf->SetXY($x,$y);
            $this->pdf->Write(10,$str2);

        }
    }
    

    /*
    *  ���̤�ʸ���󡡡ʺ�������
    */
    function WriteCellCenter($x_index, $y_index, $value, $y_start)
    {
        $y = $y_start + $y_index*10;
        $x = $this->x_pos[$x_index];
        $w = $this->x_pos[$x_index+1] - $x;
        $len = $this->pdf->GetStringWidth($value);
        $offset = ($w-$len)/2;

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write(10,$value);
    }

    /*
    *  �������������
    */
    function printStoreName($name,$store_sec,$y)
    {
        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY(15,$y);
        $this->pdf->Write(10,$name);
        $this->pdf->SetXY(15,$y+5);
        $this->pdf->Write(10,$store_sec);
    }

    /*
    *  ���ν�����
    */

    function printLowerPart($code)
    {
        $y_offset = 140;

        $this->pdf->SetLineWidth(0.3);

        $this->pdf->SetFont(GOTHIC,'U',12);
        $this->pdf->SetXY(10,10+$y_offset);
        $this->pdf->Write(20,"NO. $code");

        $this->pdf->SetFont(GOTHIC,'U',16);
        $this->pdf->SetXY(70,10+$y_offset);
        $this->pdf->Write(20,"�������Ρ���");

        $this->pdf->RoundedRect(145,10+$y_offset,56,10,2,'');

        // �������᡼��
        $fn = OLUT_IMAGE_PATH . 'ntc_logo.jpg';
        $this->pdf->Image($fn,143,25+$y_offset,50,15,'jpg');

        // ���β��β��̾
        $this->pdf->SetFont(GOTHIC,'I',9);
        $this->pdf->SetXY(120,40+$y_offset);
        $this->pdf->Write(10,$this->system_profile->company_name); // "���̥ƥ������ȥ졼�ǥ��󥰡������ݥ졼�����");
        // ���β��˽���
        $this->pdf->SetFont(GOTHIC,'',8);
        $this->pdf->SetXY(140,45+$y_offset);
        $this->pdf->Write(10,$this->system_profile->company_address_1); //"��169-8539 ����Կ��ɶ�ɴ��Į2-25-13");


        // ����Υܥå���
        $this->pdf->RoundedRect(10,27+$y_offset,60,20,2,'');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->pdf->SetXY(55,38+$y_offset);
        $this->pdf->Write(10,"����");

        $this->pdf->SetLineWidth(0.4);
        // ��ʿ��8��
        $y = 55+$y_offset;
        for($i=0; $i<8; $i++)
        {
            if($i<2 || $i==7)
            {
                $w = $this->paper_width - $this->right_margin;
            }
            else
            {
                $w = $this->x_pos[3] - $this->x_pos[0] + $this->left_margin;

            }
            $this->pdf->line($this->left_margin,$y,$w,$y);
            $y += 10;
        }

        // ��ľ�� 5��
        $h = 10*8;
        $y = 55+$y_offset;
        $h = 70;
        for($i=0; $i<5; $i++)
        {
            $this->pdf->line($this->x_pos[$i],$y,$this->x_pos[$i],$y+$h);
        }

        $this->pdf->SetFont(GOTHIC,'',11);
        $y = 55+$y_offset;
        $this->WriteHeaderCell(0,"������",$y);
        $this->WriteHeaderCell(1,"�����ʡ�̾",$y);
        $this->WriteHeaderCell(2,"������",$y);
        $this->WriteHeaderCell(3,"��  ��  ��",$y);

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY(140,123+$y_offset);
        $this->pdf->Write(10,"�嵭���ʤ���Τ��ޤ�����");

        // ��������ǡ����ΰ����롼��
        if( $this->sql->record != null )
        {
            $y = 55+$y_offset;
            $y_index = 1;
            //
            foreach($this->sql->record as $rec)
            {
                if($y_index == 1)
                {
                    $this->printStoreName($rec[7],$rec[8],$y_offset+30);        // Ź��̾�����
                    // yyyy/mm/dd
                    // 0123456789
                    $year = substr($rec[9],0,4);
                    $month = substr($rec[9],5,2);
                    $day   = substr($rec[9],8,2);
                    //
                    $this->pdf->SetFont(GOTHIC,'',10);
                    $this->pdf->SetXY(147,10+$y_offset);
                    $this->pdf->Write(10,"������ $year ǯ $month �� $day ��");
                }
                $this->pdf->SetFont(GOTHIC,'',11);
                $this->WriteCell(0,$y_index,$rec[0],$y);    // ������
                $this->WriteCell(1,$y_index,$rec[1],$y);    // ����̾
                $amount = $rec[2] . " " . $rec[3];          // ���̤�ñ�̤򥳥󥫥�
                $this->WriteCellCenter(2,$y_index,$amount,$y);

                $y_index++;
            }
        }
    }
}
?>