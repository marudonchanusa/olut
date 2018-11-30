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
 *    棚卸データ変換 - ImportInventory.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

// require_once('c:\wampp2\php\pear\DB.php'); // PEAR DB
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB


function getPrice($db,$code,$date)
{
    if($db->query("select price from m_annual_inventory where com_code='$code' and dt='$date'",SQL_ALL))
    {
        //
        if( $db->record[0] != null)
        {
            return $db->record[0][0];
        }
    }
    return 0;
}

function getAmount($db,$code,$date)
{
    if($db->query("select amount from m_annual_inventory where com_code='$code' and dt='$date'",SQL_ALL))
    {
        //
        if($db->record[0] != null)
        {
            return $db->record[0][0];
        }
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

function importInventory($target)
{
    $fn = "c:\\ntc\\olut\\data\\ntc_data\\$target\\cftana.txt";
    $fh = fopen($fn,"r");

    $db =& new SQL;
    $db->connect(OLUT_DSN);

    $trx_date = substr($target,0,4) . '/' . substr($target,4,2) . '/' . "01";
    $target_date = getNextMonth($trx_date);

    while (!feof($fh))
    {
        $line = fgets($fh);

        if(strlen($line) < 10)
        {
            break;
        }

        $data = split(",",$line);

        $shicd = $data[0];
        $skocode = $data[1];
        $syocd   = $data[2];
        $gaku    = $data[3];
        $suryo   = $data[4];

        $gaku  = ereg_replace("^\+0*","",$gaku);
        $suryo = ereg_replace("^\+0*","",$suryo);

        if($suryo==0 && $gaku==0)
        {
            continue;
        }

        if($suryo != 0 && $gaku == 0)
        {
            $gaku = getPrice($db,$syocd,$trx_date);
        }
        else
        if($suryo == 0 && $gaku != 0)
        {
            $suryo = getAmount($db,$syocd,$trx_date);
        }

        if($suryo != 0)
        {
            $unit_price = $gaku/$suryo;
        }
        else
        {
            $unit_price = 0;
        }

        $_query = "insert into t_main (act_date,act_flag,com_code,ship_sec_code,warehouse_code,amount,total_price,unit_price)";
        $_query .= " values('$target_date','0','$syocd','$shicd','$skocode',$suryo,";
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

//$months = array("200507","200508");

$months = array("200508"); // "200508");

foreach($months as $m)
{
    importInventory($m);
}

?>