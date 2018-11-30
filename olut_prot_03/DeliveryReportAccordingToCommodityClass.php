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
 *    商品別出庫一覧表 - DeliveryReportAccordingToCommodityClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF 日本語環境設定
require_once('OlutAppLib.php');

// smarty configuration
class DeliveryReportAccordingToCommodity_Smarty extends Smarty
{
    function DeliveryReportAccordingToCommodity_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class DeliveryReportAccordingToCommodity_SQL extends SQL
{
    function DeliveryReportAccordingToCommodity_SQL()
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

/*
 *      印刷クラス。
 *
 */

class DeliveryReportAccordingToCommodity extends OlutApp
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
    var $num_of_item_per_page = 25;     // １行に印字するデータの行数。
    var $current_commodity;             // 現在印刷中の店舗を保持。同じ店舗は印字しない。
    var $line_height = 6;               // 行のY方向サイズ。
    var $lines = 0;                     // 印刷行数。
    var $amount_total;              // 個数合計
    var $price_total;               // 金額合計
    var $page_no = 1;

    // ctor
    function DeliveryReportAccordingToCommodity()
    {
        $this->tpl =& new DeliveryReportAccordingToCommodity_Smarty;
        $this->sql =& new DeliveryReportAccordingToCommodity_SQL;

        // 項目印字位置設定。
        $this->x_pos = array(10,90,120,200,220,260,280,310);
    }

    /*
    *  印刷範囲入力画面のレンダリング
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
        $this->tpl->display('DeliveryReportAccordingToCommodityForm.tpl');
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
    /*
    *  印刷関数
    *
    */
    function printOut($formvars)
    {
        //
        // B4の紙サイズを指定しています。指定はポートレート。
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
    *  各ページのヘダーを印刷
    */

    function printHeader($formvars)
    {
        $year  = date('Y');
        $month = date('m');
        $date  = date('d');

        if(isset($formvars['target_year']))
        {
            $target_year = $formvars['target_year'];
        }
        else
        {
            $target_year = $year;
        }

        if(isset($formvars['target_month']))
        {
            $target_month = $formvars['target_month'];
        }
        else
        {
            $target_month = $month;
        }

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $h = 9;

        //
        // 注意：
        //  $date_toの後に倍角スペースだと$date_toが表示されない。
        //  phpのバグだと思う。半角のスペースを入れて対応。~も表示されないので - (ハイフン）とした。
        //
        $this->pdf->Cell(0,9,"$target_year 年 $target_month 月 商品別出庫一覧表",'B',1,'C');
        $this->pdf->SetXY($x,$y);
        $this->pdf->Cell(0,$h, "DATE:$year/$month/$date    PAGE: $this->page_no ",'',1,'R');
        $this->page_no++;
        //
        $this->current_y = 15;

        // 商品名　扱い先　　店舗名　　区分　　数量　　単位　金額
        //
        $header = array("商品名","扱い先","店舗名","区分","数量","単位","金額");

        for($i=0; $i<7; $i++)
        {
            $this->pdf->SetXY($this->x_pos[$i],$this->current_y);
            $this->pdf->Write(20,$header[$i]);
        }
        //$this->pdf->SetXY($this->x_pos[6],$this->current_y);
        //$this->pdf->Write(20,"入庫倉庫");

        // 線を引く
        $width = $this->paper_height - $this->right_margin;
        $this->pdf->line($this->x_pos[0],$this->current_y+15,$width,$this->current_y+15);

    }

    /*
    *  データ問い合わせ＆明細行印刷。
    */

    function printData($formvars)
    {
        // 印刷項目の、
        // [区分]は店舗内部門。(store_sec_code<-m_store_division table)
        // [扱い先」は本部部門。(m_shipment_section)

        $target_year  = $formvars['target_year'];
        $target_month = $formvars['target_month'];
        $target_last_date = OlutApp::getLastDate($target_year,$target_month);

        $dt_from = "$target_year/$target_month/01";
        $dt_to   = "$target_year/$target_month/$target_last_date";

        // 商品名　扱い先　　店舗名　　区分　　数量　　単位　金額

        $_query = "select com.name, ss.name, st.name, sd.name, -sum(t.amount), u.name, -sum(t.total_price) ";
        $_query .= " from t_main t, m_store st, m_shipment_section ss, m_commodity com, m_store_division sd, m_unit u ";
        $_query .= " where t.act_date>= '$dt_from' and t.act_date<= '$dt_to' ";
        $_query .= " and ss.code=t.ship_sec_code ";
        $_query .= " and com.code=t.com_code ";
        $_query .= " and sd.code=t.store_sec_code ";
        $_query .= " and st.code=t.dest_code ";
        $_query .= " and u.code=com.unit_code ";
        $_query .= " and (t.act_flag='5' or t.act_flag='6' or t.act_flag='7')";
        $_query .= " group by com.name, ss.name, st.name, sd.name, u.name, t.com_code, t.dest_code";

        $_query .= " order by t.com_code,t.dest_code";

        //print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            //print $_query;
            return false;
        }
        else
        {
            $this->lines     = 0;
            $this->current_y = 30;
            // ループする。
            foreach($this->sql->record as $rec)
            {
                if(!isset($this->current_commodity))
                {
                    $this->current_commodity = $rec[0];
                }

                if(strcmp($rec[0],$this->current_commodity)!=0)
                {
                    // 商品が変わった。
                    $this->checkPageChange(2,$formvars);
                    $this->printTotal();
                    $this->current_commodity = $rec[0];
                }

                // 明細行を印字する。
                $this->checkPageChange(1,$formvars);
                $this->printLine($rec);
            }
            // 商品終わり。トータル印字。
            $this->checkPageChange(2,$formvars);
            $this->printTotal();

        }
        return true;
    }

    /*
    *  ページ替えのチェックと実行。
    *  linesを初期設定したりインクリメントするので注意。
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
    *  明細１行を印刷します。
    */

    function printLine($rec)
    {
        //
        $this->pdf->SetFont(GOTHIC,'',12);

        //
        // 商品名　扱い先　　店舗名　　区分　　数量　　単位　金額
        //

        //
        // 商品名。
        //
        if($this->current_store == null || strcmp($this->current_store,$rec[0]))
        {
            $this->pdf->SetXY($this->x_pos[0],$this->current_y);
            $this->pdf->Write(20,$rec[0]);
            $this->current_store = $rec[0];
        }

        //
        //  扱い先
        //
        $this->pdf->SetXY($this->x_pos[1],$this->current_y);
        $this->pdf->Write(20,$rec[1]);

        //
        //  店舗名
        //
        $this->pdf->SetXY($this->x_pos[2],$this->current_y);
        $this->pdf->Write(20,$rec[2]);

        //
        //  区分
        //
        $this->pdf->SetXY($this->x_pos[3],$this->current_y);
        $this->pdf->Write(20,$rec[3]);

        //
        //  数量
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[4],8,2));

        //
        //  単位
        //
        $this->pdf->SetXY($this->x_pos[5],$this->current_y);
        $this->pdf->Write(20,$rec[5]);

        //
        //  金額
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($rec[6],10)); //  金額

        // totals
        $this->amount_total += $rec[4];
        $this->price_total  += $rec[6];

        // Y位置の更新。
        $this->current_y += $this->line_height;

    }


    /*
    *  トータルを印字します。
    */

    function printTotal()
    {
        //
        //  合計行識別。
        //
        $this->pdf->SetXY($this->x_pos[0],$this->current_y);
        $this->pdf->Write(20,"合計"); //  仕入額

        //
        //  個数のトータル。
        //
        $this->pdf->SetXY($this->x_pos[4],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->amount_total,8,2));


        //
        //  金額トータル
        //
        $this->pdf->SetXY($this->x_pos[6],$this->current_y);
        $this->pdf->Write(20,OlutApp::formatNumber($this->price_total,10));

        // トータルをリセット。
        $this->amount_total = 0;
        $this->price_total  = 0;

        // ２行分進めていることに注意。
        $this->current_y += $this->line_height*2;
    }
}

?>