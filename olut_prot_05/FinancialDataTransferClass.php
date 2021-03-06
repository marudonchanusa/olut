<?php
/*
 * Olut inventory management system
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
 *    財務データ作成 - FinancialDataTransferClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *    2005/11/1  ver 1.00.01 在庫レコードを出力。
 *
 */

require_once('OlutAppLib.php');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

class FinancialDataTransfer_SQL extends SQL
{
    function FinancialDataTransfer_SQL()
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

class FinancialDataTransfer extends OlutApp
{
    var $sql;

    //
    function FinancialDataTransfer()
    {
        $this->sql =& new FinancialDataTransfer_SQL();
    }

    /*
    *   CSV出力
    */
    function transferToCSV()
    {
        // 当月を得る。  ==> should see master @later.
        
        OlutApp::GetCurrentProcessDate($this->sql,&$year,&$month);
        
        // $year  = Date('Y');
        // $month = Date('m');
        
        $last  = OlutApp::getLastDate($year,$month);

        $date_from = "$year/$month/01";
        $date_to   = "$year/$month/$last";

        $_query  = "select * from t_main where act_flag<>'0' ";
        $_query .= " and deleted is null ";
        $_query .= " and act_date>='$date_from' and act_date<='$date_to'";
        $_query .= " order by act_date, com_code";

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            print "error";
            return false;
        }
        
        if($this->sql->record == null)
        {
            print "no data for $year/$month";
            return false;
        }
        
        $fn = $year . "_" . $month . ".csv";
        
        header("content-type: application/csv;");
        header("content-disposition: attachment; filename=$fn");

        $line_no = 0;

        while(1)
        {
            if($line_no==0)
            {
                // ヘダー行を印刷。
                $is_first = true;
                //
                foreach($this->sql->record as $key=>$value)
                {
                    if($is_first)
                    {
                        $is_first = false;
                    }
                    else
                    {
                        print ",";
                    }
                    print trim($key);
                }
                print "\r\n";
            }

            $is_first = true;
            //
            foreach($this->sql->record as $key=>$value)
            {
                if($is_first)
                {
                    $is_first = false;
                }
                else
                {
                    print ",";
                }
                print trim($value);

            }
            print "\r\n";

            if(!$this->sql->next())
            {
                break;
            }
            $line_no++;
        }
        
        //
        // 棚卸レコードの出力を追加。
        //
        
        $first_day_of_next_month = OlutApp::getNextMonth($date_from);
        
        $_query  = "select * from t_main where act_flag='0' ";
        $_query .= " and deleted is null ";
        $_query .= " and act_date='$first_day_of_next_month'";
        $_query .= " order by com_code";

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            print "error";
            return false;
        }

        while(1)
        {
            $is_first = true;
            //
            foreach($this->sql->record as $key=>$value)
            {
                if($is_first)
                {
                    $is_first = false;
                }
                else
                {
                    print ",";
                }
                print trim($value);

            }
            print "\r\n";

            if(!$this->sql->next())
            {
                break;
            }
            $line_no++;
        }        
    }
}

?>
