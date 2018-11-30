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
* このプログラムはフリーソフトウェアです。あなたはこれを、フリーソフトウェ
* ア財団によって発行された GNU 一般公衆利用許諾契約書(バージョン2か、希
* 望によってはそれ以降のバージョンのうちどれか)の定める条件の下で再頒布
* または改変することができます。
*
* このプログラムは有用であることを願って頒布されますが、*全くの無保証*
* です。商業可能性の保証や特定の目的への適合性は、言外に示されたものも含
* め全く存在しません。詳しくはGNU 一般公衆利用許諾契約書をご覧ください。
*
* あなたはこのプログラムと共に、GNU 一般公衆利用許諾契約書の複製物を一部
* 受け取ったはずです。もし受け取っていなければ、フリーソフトウェア財団ま
* で請求してください(宛先は the Free Software Foundation, Inc., 59
* Temple Place, Suite 330, Boston, MA 02111-1307 USA)。
*
*   Program name:
*    納品書印刷 - StatementsOfDeliveryClass.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*    2005/10/08 ver 1.00.01 配送日付 -> 日付 と変更。
*    2005/12/13 ver 1.00.02 日付け制限を撤廃。
*    2005/12/14 ver 1.00.03 メモが長い場合は2行に。
*
*/

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
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
            $this->error = "データベース接続エラー(" + $dsn + ")";
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
*  FPDFでは点線、角のまるい四角形などを描画する機能が無いので
*  ラップクラスで該当機能を追加しています。
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
*      印刷クラス。
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
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $x_pos;
    var $error = null;
    var $system_profile;

    // ctor
    function StatementOfDelivery()
    {
        $this->sql =& new StatementOfDelivery_SQL();
        $this->tpl =& new StatementOfDelivery_Smarty();
    }

    // 印字メイン

    function printOut($codes)
    {
        if(is_array($codes))
        {
            //
            $this->pdf=new OlutPDF('P','mm',array($this->paper_width,$this->paper_height));
            $this->pdf->SetRightMargin($this->right_margin);
            $this->pdf->SetLeftMargin($this->left_margin);
            $this->pdf->AddMBFont(GOTHIC ,'EUC-JP');

            // 印刷対象は処理月に限定する。
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
            $this->printUpperPart($code);        // 納品書
            $this->printCenterLine();
            //
            $this->x_pos = array(10,30,90,125,200);
            $this->printLowerPart($code);        // 受領書
        }
        else
        {
            return false;
        }
        return true;
    }

    /*
    *   エラーメッセージ出力。
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
    *   出荷データを得る。
    */
    function getShipmentData($code)
    {
        // 2005/12/13
        // 日付けによる検索条件はなしとする。
        //
        $_query  = "select t.com_code, c.name, t.amount*(-1), u.name, t.unit_price, t.total_price*(-1),t.memo,st.name,sd.name,t.act_date ";
        $_query .= " from t_main t, m_commodity c, m_unit u, m_store st, m_store_division sd ";
        $_query .= " where t.slip_no='$code' and t.com_code=c.code and u.code=c.unit_code";
        $_query .= "  and t.dest_code = st.code and sd.code=t.store_sec_code";
        $_query .= " order by t.line_no";

        return $this->sql->query($_query,SQL_ALL);
    }

    /*
    *  PDF 生成
    */

    function preparePDF()
    {
        // 指定はポートレート。
        $this->pdf->AddPage('P');
    }

    function printCenterLine()
    {
        // 少し上。Bottom Margin がセットできない。
        // 替えページするので下半分を少し大きめにした。

        $y = $this->paper_height / 2 - 8;
        $w = $this->paper_width - $this->right_margin;
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetXY($this->left_margin,$y);
        $this->pdf->SetDash(2,2);
        $this->pdf->line($this->left_margin, $y, $w, $y);
        $this->pdf->SetDash(0,0);
    }

    /*
    *  納品書を印字
    */

    function printUpperPart($code)
    {
        $this->pdf->SetLineWidth(0.3);

        $this->pdf->SetFont(GOTHIC,'U',12);
        $this->pdf->SetXY(10,10);
        $this->pdf->Write(20,"NO. $code");

        $this->pdf->SetFont(GOTHIC,'U',16);
        $this->pdf->SetXY(70,10);
        $this->pdf->Write(20,"　納　　品　　書　");

        $this->pdf->RoundedRect(145,10,56,10,2,'');

        // ロゴ・イメージ
        $fn = OLUT_IMAGE_PATH . 'ntc_logo.jpg';
        $this->pdf->Image($fn,143,25,50,15,'jpg');

        // ロゴの下の会社名
        $this->pdf->SetFont(GOTHIC,'I',9);
        $this->pdf->SetXY(120,40);
        // $this->pdf->Write(10,"エヌティー・トレーディング・コーポレーション");
        $this->pdf->Write(10,$this->system_profile->company_name);

        // その下に住所
        $this->pdf->SetFont(GOTHIC,'',8);
        $this->pdf->SetXY(140,45);
        $this->pdf->Write(10,$this->system_profile->company_address_1);
        // "〒169-8539 東京都新宿区百人町2-25-13");


        // 御中のボックス
        $this->pdf->RoundedRect(10,27,60,20,2,'');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->pdf->SetXY(55,38);
        $this->pdf->Write(10,"御中");

        $this->pdf->SetLineWidth(0.4);
        // 水平線8本
        $w = $this->paper_width - $this->right_margin;
        $y = 55;
        for($i=0; $i<8; $i++)
        {
            $this->pdf->line($this->left_margin,$y,$w,$y);
            $y += 10;
        }

        // 垂直線 7本
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

        // 合計の下の線
        $this->pdf->line($this->x_pos[3],135,200,135);

        $this->pdf->SetFont(GOTHIC,'',11);
        $y = 55;
        $this->WriteHeaderCell(0,"コード",$y);
        $this->WriteHeaderCell(1,"商　品　名",$y);
        $this->WriteHeaderCell(2,"数　量",$y);
        $this->WriteHeaderCell(3,"単　価",$y);
        $this->WriteHeaderCell(4,"金　額",$y);
        $this->WriteHeaderCell(5,"摘　要",$y);   // fix 2005/12/14

        // 合計と書いてみる。
        $y += 70;
        $this->WriteHeaderCell(3,"合  計",$y);

        // ここからデータの印字ループ
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
                    $this->printStoreName($rec[7],$rec[8],30);        // 店舗名を印字
                    // yyyy/mm/dd
                    // 0123456789
                    $year = substr($rec[9],0,4);
                    $month = substr($rec[9],5,2);
                    $day   = substr($rec[9],8,2);
                    //
                    $this->pdf->SetFont(GOTHIC,'',10);
                    $this->pdf->SetXY(147,10);
                    $this->pdf->Write(10,"　日付 $year 年 $month 月 $day 日");
                }
                $this->pdf->SetFont(GOTHIC,'',11);
                $this->WriteCell(0,$y_index,$rec[0],$y);    // コード
                $this->WriteCell(1,$y_index,$rec[1],$y);    // 商品名
                $amount = $rec[2] . " " . $rec[3];          // 数量と単位をコンカチ
                $this->WriteCellCenter(2,$y_index,$amount,$y);
                $this->WriteNumberCell(3,$y_index,$rec[4],$y);
                $this->WriteNumberCell(4,$y_index,$rec[5],$y);
                $this->WriteMemoCell(5,$y_index,$rec[6],$y);       // memo

                $total += $rec[5];      // 金額をトータルに加算
                $y_index++;
            }

            // トータルを印字
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
    *  数値表示
    */
    function WriteNumberCell($x_index,$y_index,$value, $y_start)
    {
        $value = number_format($value);
        $y = $y_start + $y_index*10;
        $x = $this->x_pos[$x_index];
        $w = $this->x_pos[$x_index+1] - $x;
        $len = $this->pdf->GetStringWidth($value);
        $offset = ($w - $len)-5 ;   // 右そろえ

        $this->pdf->SetXY($x+$offset,$y);
        $this->pdf->Write(10,$value);
    }

    /*
    *  普通の文字列　（左そろえ）
    */
    function WriteCell($x_index, $y_index, $value, $y_start)
    {
        $y = $y_start + $y_index*10;
        $x = $this->x_pos[$x_index] + 2;

        $this->pdf->SetXY($x,$y);
        $this->pdf->Write(10,$value);
    }
    
    /*
     *      メモ専用。文字長が５を超えたら2行に印刷。
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
    *  普通の文字列　（左そろえ）
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
    *  御中の中を印字。
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
    *  受領書を印字
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
        $this->pdf->Write(20,"　受　領　書　");

        $this->pdf->RoundedRect(145,10+$y_offset,56,10,2,'');

        // ロゴ・イメージ
        $fn = OLUT_IMAGE_PATH . 'ntc_logo.jpg';
        $this->pdf->Image($fn,143,25+$y_offset,50,15,'jpg');

        // ロゴの下の会社名
        $this->pdf->SetFont(GOTHIC,'I',9);
        $this->pdf->SetXY(120,40+$y_offset);
        $this->pdf->Write(10,$this->system_profile->company_name); // "エヌティー・トレーディング・コーポレーション");
        // その下に住所
        $this->pdf->SetFont(GOTHIC,'',8);
        $this->pdf->SetXY(140,45+$y_offset);
        $this->pdf->Write(10,$this->system_profile->company_address_1); //"〒169-8539 東京都新宿区百人町2-25-13");


        // 御中のボックス
        $this->pdf->RoundedRect(10,27+$y_offset,60,20,2,'');
        $this->pdf->SetFont(GOTHIC,'',12);
        $this->pdf->SetXY(55,38+$y_offset);
        $this->pdf->Write(10,"御中");

        $this->pdf->SetLineWidth(0.4);
        // 水平線8本
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

        // 垂直線 5本
        $h = 10*8;
        $y = 55+$y_offset;
        $h = 70;
        for($i=0; $i<5; $i++)
        {
            $this->pdf->line($this->x_pos[$i],$y,$this->x_pos[$i],$y+$h);
        }

        $this->pdf->SetFont(GOTHIC,'',11);
        $y = 55+$y_offset;
        $this->WriteHeaderCell(0,"コード",$y);
        $this->WriteHeaderCell(1,"商　品　名",$y);
        $this->WriteHeaderCell(2,"数　量",$y);
        $this->WriteHeaderCell(3,"受  領  印",$y);

        $this->pdf->SetFont(GOTHIC,'',11);
        $this->pdf->SetXY(140,123+$y_offset);
        $this->pdf->Write(10,"上記の品を受領しました。");

        // ここからデータの印字ループ
        if( $this->sql->record != null )
        {
            $y = 55+$y_offset;
            $y_index = 1;
            //
            foreach($this->sql->record as $rec)
            {
                if($y_index == 1)
                {
                    $this->printStoreName($rec[7],$rec[8],$y_offset+30);        // 店舗名を印字
                    // yyyy/mm/dd
                    // 0123456789
                    $year = substr($rec[9],0,4);
                    $month = substr($rec[9],5,2);
                    $day   = substr($rec[9],8,2);
                    //
                    $this->pdf->SetFont(GOTHIC,'',10);
                    $this->pdf->SetXY(147,10+$y_offset);
                    $this->pdf->Write(10,"　日付 $year 年 $month 月 $day 日");
                }
                $this->pdf->SetFont(GOTHIC,'',11);
                $this->WriteCell(0,$y_index,$rec[0],$y);    // コード
                $this->WriteCell(1,$y_index,$rec[1],$y);    // 商品名
                $amount = $rec[2] . " " . $rec[3];          // 数量と単位をコンカチ
                $this->WriteCellCenter(2,$y_index,$amount,$y);

                $y_index++;
            }
        }
    }
}
?>