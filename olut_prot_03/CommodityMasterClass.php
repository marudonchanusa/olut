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
 *    商品マスター登録 - CommodityMasterClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */
require_once('OlutAppLib.php');

class Commodity extends OlutApp
{

    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;

    var $all_cols = null;
    
    /**
     * class constructor
     */
    function Commodity() {

        // instantiate the sql object
        $this->sql =& new Commodity_SQL;

        // instantiate the template object
        $this->tpl =& new Commodity_Smarty;
        
        //
        $this->all_cols = "code,name,name_k,dist_flag,unit_code,order_unit_code,order_amount,lot_unit_code,";
        $this->all_cols .= "lot_amount,ship_section_code,accd,itmcd,com_class_code,order_sheet_flag,stock_flag,";
        $this->all_cols .= "current_unit_price,next_unit_price,jancd,depletion_flag,note,note_k,updated";
        
    }

    /**
     * display the vendor entry form
     *
     * @param array $formvars the form variables
     */
    function displayForm($formvars = array()) {

        //
        // Smartyで escape|strip をしてもなぜかスペース１文字が最後尾に着くので、
        // 以下でtrimを実行することにした。
        //

        $this->mungeFormData($formvars);

        // assign the form vars
        $this->tpl->assign('post',$formvars);
        $this->tpl->assign('dist_flag_list',$this->getDistributionFlagList($formvars));
        $this->tpl->assign('unit_code_list',$this->getUnitCodeList($formvars,'unit_code'));
        $this->tpl->assign('lot_unit_code_list',$this->getUnitCodeList($formvars,'lot_unit_code'));
        $this->tpl->assign('order_unit_code_list',$this->getUnitCodeList($formvars,'order_unit_code'));     
        $this->tpl->assign('order_sheet_flag_list',$this->getOrderSheetFlagList($formvars));
        $this->tpl->assign('stock_flag_list',$this->getStockFlagList($formvars));       
        $this->tpl->assign('ship_section_code_list',$this->getShipmentSectionList($formvars));   
        $this->tpl->assign('depletion_flag_list', $this->getDepletionFlagList($formvars));    
        
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('CommodityMasterForm.tpl');
    }
    
    /**
     * fix up form data if necessary
     *
     * @param array $formvars the form variables
     */
    function mungeFormData(&$formvars) {

        // trim off excess whitespace
        $formvars['code'] = trim($formvars['code']);
        $formvars['name'] = trim($formvars['name']);
        $formvars['name_k'] = trim($formvars['name_k']);
        $formvars['dist_flag'] = trim($formvars['dist_flag']);
        $formvars['unit_code'] = trim($formvars['unit_code']);
        $formvars['order_unit_code'] = trim($formvars['order_unit_code']);
        $formvars['lot_unit_code'] = trim($formvars['lot_unit_code']);
        $formvars['lot_amount'] = trim($formvars['lot_amount']);
        $formvars['ship_section_code'] = trim($formvars['ship_section_code']);
        $formvars['accd'] = trim($formvars['accd']);
        $formvars['itmcd'] = trim($formvars['itmcd']);
        $formvars['com_class_code'] = trim($formvars['com_class_code']);
        $formvars['order_sheet_flag'] = trim($formvars['order_sheet_flag']);
        $formvars['stock_flag'] = trim($formvars['stock_flag']);
        $formvars['current_unit_price'] = trim($formvars['current_unit_price']);
        $formvars['next_unit_price'] = trim($formvars['next_unit_price']);
        $formvars['jancd'] = trim($formvars['jancd']);
        $formvars['note'] = trim($formvars['note']);
        $formvars['note_k'] = trim($formvars['note_k']);
    }

    /**
     * test if form information is valid
     *
     * @param array $formvars the form variables
     */
    function isValidForm($formvars) {

        // reset error message
        $this->error = null;
     		
        // test if "Code" is empty
        if(strlen($formvars['code']) < 5) {
            $this->error = 'コードを5桁で数字を入力してください';
            return false; 
        }   
        
        // test if "Name" is empty
        if(strlen($formvars['name']) == 0) {
            $this->error = '名前が空欄です。';
            return false; 
        }

        // test if "Name_Kana" is empty
        if(strlen($formvars['name_k']) == 0) {
            $this->error = '名前の読み仮名が空欄です。';
            return false; 
        }
        
        // 全角カナ？
        if(!IsZenkakuKana($formvars['name_k'])){
            $this->error = '名前の読み仮名を全角カタカナで入力してください';
        	return false;
        }
        
        // 数値チェック
        if(!IsDigit($formvas['code'])){
            $this->error = 'コードを数値入力してください。';
            return false;
        }
        
        // 減耗フラグ
        if($formvars['depletion_flag'] != '0' && $formvars['depletion_flag'] != '1')
        {
            $this->error = '減耗フラグを正しく入力してください。';
            return false;
        }

        return true;
    }
    
    //
    // 保存ボタン処理。
    //
    
    function saveEntry($formvars){
                
        $rc = 0;
        $id = $formvars['code'];
        $this->sql->query(sprintf("select count(*) from m_commodity where code='%s' and deleted is null",$id),SQL_ALL,SQL_ASSOC);
        	
        if( $this->sql->record[0]['count'] > 0 )
        {
            // レコードがあるのでupdate
            $rc = $this->updateEntry($formvars);
        }
        else 
        {
            // 新規登録。
            $rc = $this->addEntry($formvars);
        }
        if(!$rc)
        {
            $this->error = $this->sql->error;
        }
        return $rc;
    }
    
    /**
     * add a new store entry
     *
     * @param array $formvars the form variables
     */
    function addEntry($formvars) {

        $_query = "insert into m_commodity ($this->all_cols) ";
        $_query .= sprintf("values('%s','%s','%s','%s','%s','%s',%s,'%s',%s,'%s','%s','%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s',NOW())",
            pg_escape_string($formvars['code']),
            pg_escape_string($formvars['name']),
            pg_escape_string($formvars['name_k']),
            pg_escape_string($formvars['dist_flag']),     
            pg_escape_string($formvars['unit_code']),                             
            pg_escape_string($formvars['order_unit_code']),           
            pg_escape_string(OlutApp::paddZero($formvars['order_amount'])),           
            pg_escape_string($formvars['lot_unit_code']),
            pg_escape_string(OlutApp::paddZero($formvars['lot_amount'])),
            pg_escape_string($formvars['ship_section_code']),
            pg_escape_string($formvars['accd']),
            pg_escape_string($formvars['itmcd']),
            pg_escape_string($formvars['com_class_code']),
            pg_escape_string($formvars['order_sheet_flag']),
            pg_escape_string($formvars['stock_flag']),
            pg_escape_string($formvars['current_unit_price']),
            pg_escape_string($formvars['next_unit_price']),
            pg_escape_string($formvars['jancd']),
            pg_escape_string($formvars['depletion_flag']),           
            pg_escape_string($formvars['note']),
            pg_escape_string($formvars['note_k'])                     
            );
        
       //print $_query;
        
        return $this->sql->query($_query);
    }
    
    
    /**
     * update entry
     *
     * @param array $formvars the form variables
     */
    function updateEntry($formvars) {

        $_query = sprintf(
            "update m_commodity set name='%s',name_k='%s',dist_flag='%s',unit_code='%s',order_unit_code='%s',order_amount=%s,lot_unit_code='%s',lot_amount=%s,ship_section_code='%s',accd='%s',itmcd='%s',com_class_code='%s',order_sheet_flag='%s',stock_flag='%s',current_unit_price=%s,next_unit_price=%s,jancd='%s',depletion_flag='%s',note='%s',note_k='%s',updated=NOW() where code='%s' and deleted is null",
            pg_escape_string($formvars['name']),
            pg_escape_string($formvars['name_k']),
            pg_escape_string($formvars['dist_flag']), 
            pg_escape_string($formvars['unit_code']),           
            pg_escape_string($formvars['order_unit_code']),           
            pg_escape_string(OlutApp::paddZero($formvars['order_amount'])),           
            pg_escape_string($formvars['lot_unit_code']),
            pg_escape_string(OlutApp::paddZero($formvars['lot_amount'])),
            pg_escape_string($formvars['ship_section_code']),
            pg_escape_string($formvars['accd']),
            pg_escape_string($formvars['itmcd']),
            pg_escape_string($formvars['com_class_code']),
            pg_escape_string($formvars['order_sheet_flag']),
            pg_escape_string($formvars['stock_flag']),
            pg_escape_string($formvars['current_unit_price']),
            pg_escape_string($formvars['next_unit_price']),
            pg_escape_string($formvars['jancd']),
            pg_escape_string($formvars['depletion_flag']),            
            pg_escape_string($formvars['note']),
            pg_escape_string($formvars['note_k']),  
            pg_escape_string($formvars['code'])         
        );
        //print $_query;
        return $this->sql->query($_query);
        
    }

    /*
     * delete vendor entry.
     */
    
    function deleteEntry($formvars)
    {
    	$code = pg_escape_string($formvars['code']);
    	$_query = sprintf("update m_commodity set deleted=now() where code='%s'",$code);
    	// print $_query;
    	return $this->sql->query($_query);
    }
        
    /**
     * get the vendor entries
     */
    function getEntries() {

        $this->sql->query(
			"select $this->all_cols from m_commodity where deleted is null order by code DESC",
			SQL_ALL,
			SQL_ASSOC
		);

        return $this->sql->record;   
    }

    function getPrevEntry($formvars) {

        $id = pg_escape_string($formvars['code']);
        if(!isset($id) || $id=='')
        {
            $_query = "select $this->all_cols from m_commodity where deleted is null order by code desc";
        }else{
            $_query =  sprintf("select $this->all_cols from m_commodity where code < '%s' and deleted is null order by code desc",$id);
        }
        
        // print "id = $id  $_query";
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        //
        return $this->sql->record;
    }

    function getNextEntry($formvars) {

        $id = pg_escape_string($formvars['code']);
        if(!isset($id) || $id=="" ){
            $_query =  "select $this->all_cols from m_commodity where deleted is null order by code  asc";
        }else{
            $_query =  sprintf("select $this->all_cols from m_commodity where code > '%s'  and deleted is null order by code asc",$id);
        }
        
        // print $_query;
        
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        // debug.
        //$x = $this->sql->record['fax'];
        //print $x;
        return $this->sql->record;
    }

    //
    //  リロード処理。
    // 
    function findEntryExact($formvars) {
    	
        $id = pg_escape_string($formvars['code']);  
        $_query =  sprintf("select $this->all_cols from m_commodity where code = '%s' and deleted is null",$id);
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        return $this->sql->record;   
    }
    
    //
    // 検索処理
    //
                 
    function findEntry($formvars) {
    	
        $id = pg_escape_string($formvars['scode']);  
        $_query =  sprintf("select $this->all_cols from m_commodity where code like '%s%%' and deleted is null",$id);
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        return $this->sql->record;   
    }
            
    /**
     * display the commodity
     *
     * @param array $data the vendor data
     */
    function display($data = array()) {

        $this->tpl->assign('post', $data);
        $this->tpl->display('CommodityMasterForm.tpl');      

    }
    
    /*
     *  物流指定区分のリストを得る。
     */
    function getDistributionFlagList($formvars)
    {
        if($formvars['dist_flag'] == '1')
        {
            $selected0 = "";
            $selected1 = "selected";            
        }
        else
        {
            $selected0 = "selected";
            $selected1 = "";                        
        }
        
        $result = "";
        $result .= "<option value=0 $selected0>通常商品</option>";
        $result .= "<option value=1 $selected1>物流指定商品</option>";

        return $result;
    }    
    
    /*
     *  単位コードのリストを得る。
     */
    function getUnitCodeList($formvars,$id)
    {
        //
        $selected_code = $formvars[$id];
        
        $_query = "select code,name from m_unit order by code";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            foreach($this->sql->record as $rec)
            {
                $unit_code = trim($rec[0]);
                if(!strcmp($unit_code,$selected_code))
                {
                    $result .= sprintf("<option value='%s' selected>",$unit_code);
                }
                else
                {
                    $result .= sprintf("<option value='%s'>",$unit_code);
                }
                $result .= $rec[1];
                $result .= "</option>";
            }            
            return $result;
        }
        return null;
    }

    /*
     *  在庫引き落とし区分のリストを得る。
     */
    function getStockFlagList($formvars)
    {
        if($formvars['stock_flag'] == '1')
        {
            $selected0 = "";
            $selected1 = "selected";            
        }
        else
        {
            $selected0 = "selected";
            $selected1 = "";                        
        }
        
        $result = "";
        $result .= "<option value=0 $selected0>出荷、出庫のみ引落</option>";
        $result .= "<option value=1 $selected1>入出荷、入出庫とも引落</option>";

        return $result;
    }
        
    /*
     *  指示書区分のリストを得る。
     */
    function getOrderSheetFlagList($formvars)
    {
        $values = array('T' =>'定貫在庫品', 'F' => '不定貫在庫品' ,'C'=> '受注後発注品','M'=> '受注後生産品');

        $selected_val = $formvars['order_sheet_flag'];
        $result = '';
        
        while (list ($key, $val) = each ($values))
        {
            if(!strcmp($key,$selected_val))
            {
                $result .= "<option value=$key selected>$val</option>";
            }
            else 
            {
                $result .= "<option value=$key>$val</option>";
            }
        }
        return $result;
    }      
    
    /*
     *  本部部門のリストを得る。
     */
    function getShipmentSectionList($formvars)
    {
        //
        $selected_code = $formvars['ship_section_code'];
        
        $_query = "select code,name from m_shipment_section order by code";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            foreach($this->sql->record as $rec)
            {
                $section_code = trim($rec[0]);
                if(!strcmp($section_code,$selected_code))
                {
                    $result .= sprintf("<option value='%s' selected>",$section_code);
                }
                else
                {
                    $result .= sprintf("<option value='%s'>",$section_code);
                }
                $result .= $rec[1];
                $result .= "</option>";
            }            
            return $result;
        }
        return null;
    }      
    
            
    /*
     *  減耗フラグのリストを得る。
     */
    function getDepletionFlagList($formvars)
    {
        $values = array('0' =>'減耗計算なし', '1' => '減耗計算あり');

        $selected_val = $formvars['depletion_flag'];
        $result = '';
        
        while (list ($key, $val) = each ($values))
        {
            if(!strcmp($key,$selected_val))
            {
                $result .= "<option value=$key selected>$val</option>";
            }
            else 
            {
                $result .= "<option value=$key>$val</option>";
            }
        }
        return $result;
    }    
}

?>
