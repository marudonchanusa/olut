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
 *    取引先マスター - VendorMasterClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

class Vendor {

	// database object
    var $sql = null;
	// smarty template object
    var $tpl = null;
	// error messages
    var $error = null;
    
    /**
     * class constructor
     */
    function Vendor() {

	// instantiate the sql object
        $this->sql =& new Vendor_SQL;

 	// instantiate the template object
        $this->tpl =& new Vendor_Smarty;
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
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('VendorMasterForm.tpl');
    }
    
    /**
     * fix up form data if necessary
     *
     * @param array $formvars the form variables
     */
    function mungeFormData(&$formvars) {

        // trim off excess whitespace
        $formvars['code'] = trim($formvars['code']);
        $formvars['ckd'] = trim($formvars['ckd']);        
        $formvars['name'] = trim($formvars['name']);
        $formvars['name_k'] = trim($formvars['name_k']);
        $formvars['tel'] = trim($formvars['tel']);
        $formvars['fax'] = trim($formvars['fax']);        
        $formvars['zip'] = trim($formvars['zip']);
        $formvars['address'] = trim($formvars['address']);
        $formvars['mail_address'] = trim($formvars['mail_address']);
        $formvars['web_url'] = trim($formvars['web_url']);
        $formvars['isdcd'] = trim($formvars['isdcd']);

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
        
        // 
        if(!IsDigit($formvas['zip'])){
            $this->error = '郵便番号を数値入力してください。';
            return false;
        }

        // 
        if(!IsDigit($formvas['tel'])){
            $this->error = '電話番号を数値入力してください。';
            return false;
        }

        if(!IsDigit($formvas['fax'])){
            $this->error = 'ファックス番号を数値入力してください。';
            return false;
        }
        
        // form passed validation
        return true;
    }
    
    //
    // 保存ボタン処理。
    //
    
    function saveEntry($formvars){
                
        $id = $formvars['code'];
        $this->sql->query(sprintf("select count(*) from m_vendor where code='%s' and deleted is null",$id),SQL_ALL,SQL_ASSOC);
        	
        // $x = $this->sql->numRows();  // record['count'];
        //$x = $this->sql->record[0]['count'];
        //print $x;
        
        if( $this->sql->record[0]['count'] > 0 )
        {
            // レコードがあるのでupdate
            return $this->updateEntry($formvars);
        }
        // 新規登録。
        return    $this->addEntry($formvars);
    }
    
    /**
     * add a new vendor entry
     *
     * @param array $formvars the form variables
     */
    function addEntry($formvars) {

        $_query = sprintf(
            "insert into m_vendor (code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url,updated) values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',NOW())",
            pg_escape_string($formvars['code']),
            pg_escape_string($formvars['ckd']),
            pg_escape_string($formvars['name']),
            pg_escape_string($formvars['name_k']),
            pg_escape_string($formvars['isdcd']),            
            pg_escape_string($formvars['tel']),           
            pg_escape_string($formvars['fax']),           
            pg_escape_string($formvars['zip']),           
            pg_escape_string($formvars['address']),
            pg_escape_string($formvars['mail_address']),
            pg_escape_string($formvars['web_url'])
        );
        
        // print $_query;
        
        return $this->sql->query($_query);
    }
    
    
    /**
     * update entry
     *
     * @param array $formvars the form variables
     */
    function updateEntry($formvars) {

        $_query = sprintf(
            "update m_vendor set ckd='%s',name='%s',name_k='%s',isdcd='%s',tel='%s',fax='%s',zip='%s',address='%s',mail_address='%s',web_url='%s',updated=NOW() where code='%s' and deleted is null",
            pg_escape_string($formvars['ckd']),    
            pg_escape_string($formvars['name']),
            pg_escape_string($formvars['name_k']),
            pg_escape_string($formvars['isdcd']),            
            pg_escape_string($formvars['tel']),           
            pg_escape_string($formvars['fax']),           
            pg_escape_string($formvars['zip']),           
            pg_escape_string($formvars['address']),
            pg_escape_string($formvars['mail_address']),
            pg_escape_string($formvars['web_url']),
            pg_escape_string($formvars['code'])         
        );
        // print $_query;
        return $this->sql->query($_query);
        
    }

    /*
     * delete vendor entry.
     */
    
    function deleteEntry($formvars)
    {
    	$code = pg_escape_string($formvars['code']);
    	$_query = sprintf("update m_vendor set deleted=now() where code='%s'",$code);
    	// print $_query;
    	return $this->sql->query($_query);
    }
        
    /**
     * get the vendor entries
     */
    function getEntries() {

        $this->sql->query(
			"select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where deleted is null order by code DESC",
			SQL_ALL,
			SQL_ASSOC
		);

        return $this->sql->record;   
    }

    function getPrevEntry($formvars) {

        $id = pg_escape_string($formvars['code']);
        if(!isset($id) || $id=='')
        {
            $_query = "select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where deleted is null order by code desc";
        }else{
            $_query =  sprintf("select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where code < '%s' and deleted is null order by code desc",$id);
        }
        //print "id = $id  $_query";
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        //
        return $this->sql->record;
    }

    function getNextEntry($formvars) {

        $id = pg_escape_string($formvars['code']);
        if(!isset($id) || $id=="" ){
            $_query =  "select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where deleted is null order by code  asc";
        }else{
            $_query =  sprintf("select code,ckd,name,name_k,tel,fax,zip,address,mail_address,web_url from m_vendor where code > '%s'  and deleted is null order by code asc",$id);
        }

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
        $_query =  sprintf("select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where code = '%s' and deleted is null",$id);
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        return $this->sql->record;   
    }
    
    //
    // 検索処理
    //
                 
    function findEntry($formvars) {
    	
        $id = pg_escape_string($formvars['scode']);  
        $_query =  sprintf("select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where code like '%s%%' and deleted is null",$id);
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        return $this->sql->record;   
    }
            
    /**
     * display the vendor
     *
     * @param array $data the vendor data
     */
    function display($data = array()) {

        $this->tpl->assign('post', $data);
        $this->tpl->display('VendorMasterForm.tpl');      

    }
}

?>
