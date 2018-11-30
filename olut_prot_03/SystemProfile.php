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
 *    �����ƥ�ץ�ե����� - SystemProfile.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

class SystemProfile
{
    var $shipment_warehouse_code;       // �вٻ����Ҹ˥�����
    var $tax;                           // �����ǡ�0.05
    var $company_name;                  // ���̾
    var $company_address_1;             // address-1
    var $company_address_2;             // address-2
    var $account_start_month;           // ��׳��Ϸ�
    var $show_after_close;              // Ź����Ź���Ź�ޥޥ��������Ȥ�ɽ���������

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
