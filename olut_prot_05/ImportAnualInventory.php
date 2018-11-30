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
 *    年間入出荷ファイル取込 - ImportAnnualInventory.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB


function importAnualInventory($fn)
{
    $fn = "c:\\ntc\\olut\\data\\cdnennyu\\$fn";
    $fh = fopen($fn,"r");

    $db =& new SQL;
    $db->connect(OLUT_DSN);

    while (!feof($fh))
    {
        $line = fgets($fh);

        if(strlen($line) < 10)
        {
            break;
        }

        $data = split(",",$line);

        $com_code = $data[1];       // 商品コード

        if($data[2] != '9')         // 減耗レコードじゃない。
        {
            continue;
        }

        // 2005　アレイ　１
        // 2002　アレイ　２
        // 2003　アレイ　３
        // 2004　アレイ　４

        $years = array(2005,2002,2003,2004);

        for($j=0; $j<4; $j++)
        {
            $year = $years[$j];

            for($i=0; $i<12; $i++)
            {
                $dt = mktime(0,0,0,$i+1,1,$year);
                $dt = date('Y/m/d',$dt);

                $amount = $data[$i+4+$j*13];
                $price  = $data[$i+4+13*4+$j*13];
                
                if($com_code=='23500')
                {
                    print "hoge";
                }

                $_query = "insert into m_annual_inventory(com_code,dt,amount,price)" ;
                $_query .= " values('$com_code','$dt',$amount,$price)";

                if($db->query($_query)==false)
                {
                    print "$_query<br>";
                    break;
                }
            }
        }
    }

    $db->disconnect();

    fclose($fh);

}

// main.

$files = array("cdnennyu_1.csv","\cdnennyu_2.csv","\cdnennyu_3.csv");

foreach($files as $fn)
{
    importAnualInventory($fn);
}

?>