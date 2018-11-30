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
 *    システムプロファイル - SystemProfile.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

class SystemProfile
{
    var $shipment_warehouse_code;       // 出荷時の倉庫コード
    var $tax;                           // 消費税　0.05
    var $company_name;                  // 会社名
    var $company_address_1;             // address-1
    var $company_address_2;             // address-2
    var $account_start_month;           // 会計開始月
    var $show_after_close;              // 店舗閉店後に店舗マスター参照で表示する期間

    var $profile_name;

    function SystemProfile()
    {
        $this->profile_name = OLUT_SYSTEM_PROFILE;
        $this->readFromXMLProfile();
        
        // giving defaults.
        if($this->account_start_month==null)
        {
            $this->account_start_month='01';
        }
        
        if($this->shipment_warehouse_code==null)
        {
            $this->shipment_warehouse_code='01';
        }

    }

    function startElement($parser, $name, $attribs)
    {
        if((strcmp($name,'SETTING') == 0) && sizeof($attribs))
        {
            switch($attribs['NAME'])
            {
                case 'tax':
                $this->tax = $attribs['VALUE'];
                break;
                
                case 'account_start_month':
                $this->account_start_month = $attribs['VALUE'];
                break;
                
                case 'shipment_warehouse_code':
                $this->shipment_warehouse_code = $attribs['VALUE'];
                break;

                case 'company_name':
                $this->company_name = mb_convert_encoding($attribs['VALUE'],"EUC-JP","SJIS");
                break;

                case 'company_address_1':
                $this->company_address_1 = mb_convert_encoding($attribs['VALUE'],"EUC-JP","SJIS");
                break;

                case 'company_address_2':
                $this->company_address_2 = mb_convert_encoding($attribs['VALUE'],"EUC-JP","SJIS");
                break;
                
                case 'show_after_close':
                $this->show_after_close = $attribs['VALUE'];
                break;

            }
        }
    }

    function endElement($parser, $name)
    {
        // nop...
    }

    function readFromXMLProfile()
    {
        $xml_parser = xml_parser_create();
        // use case-folding so we are sure to find the tag in $map_array
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($xml_parser,array(&$this,'startElement'), array(&$this, 'endElement'));
        if (!($fp = fopen($this->profile_name, "r")))
        {
            die("could not open XML input");
        }

        while ($data = fread($fp, 4096))
        {
            if (!xml_parse($xml_parser, $data, feof($fp)))
            {
                die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($xml_parser)),
                xml_get_current_line_number($xml_parser)));
            }
        }
        xml_parser_free($xml_parser);
    }
}


?>
