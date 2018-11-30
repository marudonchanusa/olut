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
 *    取引先データ変換 - ImportVendor.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

// require_once('c:\wampp2\php\pear\DB.php'); // PEAR DB
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

function convertVendor()
{
    $fn = 'c:\ntc\olut\master_data\bin\cftori.txt';
    $fh = fopen($fn,"r");
    
    $db =& new SQL;
    $db->connect(OLUT_DSN);


    while (!feof($fh))
    {
        $line = fgets($fh);
        
        if(strlen($line)<3)
        {
            break;
        }
        
        $code = substr($line,0,5);
        $ckd    = substr($line,5,1);
        $name_n = substr($line,6,30);
        $adrs_n = substr($line,36,40);
        $tel    = substr($line,76,20);
        
        $name_k = substr($line,96,30);  // オフセット狂ってる？
        
        //
        $name_n = mb_convert_encoding($name_n,"EUC-JP","SJIS");
        $adrs_n = mb_convert_encoding($adrs_n,"EUC-JP","SJIS");
        $name_k = mb_convert_encoding($name_k,"EUC-JP","SJIS");

        // (括弧）内も含めて削除。＝＞マスター参照に具合が悪い。
        //  $name_k = mb_ereg_replace("(\S*)","",$name_k);
        //  上記のような正規表現はバグって使えない。
        
        if(substr($name_k,0,1)=='(')
        {
            $end_index = strchr($name_k,')');
            if($end_index != false)
            {
              $name_k = substr($end_index,1);
            }
        }
        
        // 全角に変換
        $name_k = mb_convert_kana($name_k,"KV");
        

        print "$name_n<br>";
        
        $_query = "insert into m_vendor (code,ckd,";
        $_query .= " name,name_k,address,tel,updated) ";
        $_query .= " values('$code','$ckd',";
        $_query .= " '$name_n','$name_k','$adrs_n','$tel',now())";
        
        // print "$_query<br>";
        
        if($db->query($_query)==false)
        {
            break;
        }
    }
    
    $db->disconnect();

    fclose($fh);

}

// main.

convertVendor();


?>