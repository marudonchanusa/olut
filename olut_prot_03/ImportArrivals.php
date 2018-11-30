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
 *    出荷データ変換 - ImportArrivals.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB


function getUnitPrice($db,$code)
{
    if($db->query("select current_unit_price from m_commodity where code='$code'",SQL_ALL,SQL_ASSOC))
    {
        //
        return $db->record[0]['current_unit_price'];
    }
    return 0;
}

function getNextMonth($dd)  // expect "yyyy/mm/dd' format
{
    $year = substr($dd,0,4);
    $month = substr($dd,5,2);
    $date = substr($dd,8,2);
    $prev = mktime (0,0,0,$month+1,$date,$year);
    $year = date('Y',$prev);
    $month = date('m',$prev);
    $date  = date('d',$prev);

    return "$year/$month/$date";
}

function checkCommodity($db,$code)
{
    $_query = "select count(*) from m_commodity where code='$code'";
    if(!$db->query($_query,SQL_INIT))
    {
        return false;
    }
    if($db->record == null)
    {
        return false;
    }
    if($db->record[0] == 0)
    {
        return false;
    }
    return true;
}

function checkVendor($db,$code)
{
    $_query = "select count(*) from m_vendor where code='$code'";
    if(!$db->query($_query,SQL_INIT))
    {
        return false;
    }
    if($db->record == null)
    {
        return false;
    }
    if($db->record[0] == 0)
    {
        return false;
    }
    return true;
}

function importShipments($target)
{
    $count = 0;
    $compare_target = substr($target,0,4) . '/' . substr($target,4,2);
    $fn = "c:\\ntc\\olut\\data\\ntc_data\\$target\\cfidou.txt";
    $fh = fopen($fn,"r");

    $db =& new SQL;
    $db->connect(OLUT_DSN);

    while (!feof($fh))
    {
        $count++;

        $line = fgets($fh);

        if(strlen($line) < 10)
        {
            break;
        }

        $data = split(",",$line);
        
        // 先頭が、伝票日付け、
        // 8番目が入力日付け。

        $inpdate = $data[8];    // 仕様書が間違い、との情報あり。 0 <-> 8
        $inpdate = "200" . substr($inpdate,3,1) . '/' . substr($inpdate,4,2) . '/' . substr($inpdate,6,2);
        //
        
        $slip_no  = ereg_replace("^\+*","",$data[1]);
        $line_no  = '0';        // $data[2];
        $syocd    = $data[2];
        $shicd    = $data[3];   //01234567
        $inskocd  = $data[4];   //

        $ottoricd = $data[7];   //
        $actdate  = $data[0];   // 
        $actdate = "200" . substr($actdate,3,1) . '/' . substr($actdate,4,2) . '/' . substr($actdate,6,2);

        $nesakbn  = $data[9];
        $payment_flag = $data[10];

        $gaku  = $data[11];
        $suryo = $data[12];

        $gaku  = ereg_replace("^\+*","",$gaku);
        $suryo = ereg_replace("^\+*","",$suryo);
        
        // 当月分ではない場合は登録しない。
        if(strncmp($compare_target,$actdate,7) != 0)
        // if(strncmp($compare_target,$inpdate,7) != 0)
        {
            continue;
        }

        if($suryo==0 && $gaku==0)
        {
            continue;
        }
        
        if($nesakbn != '1')
        {
            print "nesage $nesakbn \n";
            continue;
        }
        
        // 商品は存在するか？
        if(!checkCommodity($db,$syocd))
        {
            print "inconsistent commodity at $target, $count";
            continue;
        }

        // 取引先は存在するか？
        if(!checkVendor($db,$ottoricd))
        {
            print "inconsistent vendor at $target, $count";
            continue;
        }

        if($suryo != 0 && $gaku == 0)
        {
            $unit_price = getUnitPrice($db,$syocd);
            if($unit_price != 0)
            {
              //  $gaku = $suryo * $unit_price;
            }
        }
        else
        if($suryo == 0 && $gaku != 0)
        {
            $unit_price = getUnitPrice($db,$syocd);
            if($unit_price != 0)
            {
                // print "hoge";
                // $suryo  = $gaku / $unit_price;
            }
        }
        else
        {
            $unit_price = $gaku / $suryo;
        }

        $_query = "insert into t_main (act_date,act_flag,slip_no,line_no,orig_code,ship_sec_code,warehouse_code,com_code,amount,total_price,unit_price,payment_flag)";
        $_query .= " values('$actdate','1','$slip_no','$line_no','$ottoricd','$shicd','$inskocd','$syocd',$suryo,";
        $_query .= "  $gaku, $unit_price,$payment_flag )";

        if($db->query($_query)==false)
        {
            print "$_query<br>";
            break;
        }
    }

    $db->disconnect();

    fclose($fh);

}

// main.

//$months = array("200312","200401","200402","200403",
//"200404", "200405", "200406","200407","200408",
//"200409","200410","200411","200412","200501",
//"200502","200503","200504","200505","200506");

$months = array("200507","200508");

// $months= array("200504");
foreach($months as $m)
{
    importShipments($m);
}

?>