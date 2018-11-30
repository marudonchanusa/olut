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
 *    出荷データ変換 - ImportShipments.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

// require_once('c:\wampp2\php\pear\DB.php'); // PEAR DB
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

function checkStore($db,$code)
{
    $_query = "select count(*) from m_store where code='$code'";
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
    $fn = "c:\\ntc\\olut\\data\\ntc_data\\$target\\cfnouhin.txt";
    $fh = fopen($fn,"r");

    $db =& new SQL;
    $db->connect(OLUT_DSN);

    $target_date = substr($target,0,4) . '/' . substr($target,4,2) . '/' . "01";

    while (!feof($fh))
    {
        $line = fgets($fh);

        if(strlen($line) < 10)
        {
            break;
        }

        $data = split(",",$line);

        $slip_no  = $data[0];
        $line_no  = $data[1];
        $line_no--;             // ゼロオフセット。
        $tencd    = $data[2];
        $bmncd    = $data[3];
        $syocd    = $data[4];
        $shicd    = $data[5];   //01234567
        $otdate   = $data[6];   //+0031203

        $otdate = "200" . substr($otdate,3,1) . '/' . substr($otdate,4,2) . '/' . substr($otdate,6,2);

        // 今月ではないのでスキップ。
        if(strcmp(substr($otdate,0,7),substr($target_date,0,7)) != 0)
        {
            continue;
        }

        $nesakbn  = $data[7];

        $gaku  = $data[9];
        $suryo = $data[10];

        $gaku  = ereg_replace("^\+*","",$gaku);
        $suryo = ereg_replace("^\+*","",$suryo);

        if($suryo==0 && $gaku==0)
        {
            continue;
        }

        if(checkCommodity($db,$syocd)==false)
        {
            print "error";
            continue;
        }

        if(checkStore($db,$tencd)==false)
        {
            print "error";
            continue;
        }
        
        // 値下げ。
        if($nesakbn=='5')
        {
            $act_flag='5';
        }
        else 
        {
            $act_flag='6';
        }

        if($suryo != 0 && $gaku == 0)
        {
            // 以下はいらないみたい。

            //$unit_price = getUnitPrice($db,$syocd);
            //if($unit_price != 0)
            //{
            //    $gaku = $suryo * $unit_price;
            //}
        }
        else
        if($suryo == 0 && $gaku != 0)
        {
            $unit_price = getUnitPrice($db,$syocd);
            if($unit_price != 0)
            {
                $suryo  = $gaku / $unit_price;
            }
        }
        else
        {
            $unit_price = $gaku / $suryo;
        }

        $gaku *= -1;
        $suryo *= -1;

        $_query = "insert into t_main (act_date,act_flag,slip_no,line_no,dest_code,store_sec_code,com_code,ship_sec_code,amount,total_price,unit_price)";
        $_query .= " values('$otdate','$act_flag','$slip_no','$line_no','$tencd','$bmncd','$syocd','$shicd',$suryo,";
        $_query .= "  $gaku, $unit_price )";

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

foreach($months as $m)
{
    importShipments($m);
}

?>