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
 *    �����ޥ����� - VendorMasterClass.php
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
        // Smarty�� escape|strip �򤷤Ƥ�ʤ������ڡ�����ʸ�����Ǹ������夯�Τǡ�
        // �ʲ���trim��¹Ԥ��뤳�Ȥˤ�����
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
            $this->error = '�����ɤ�5��ǿ��������Ϥ��Ƥ�������';
            return false; 
        }   
        
        // test if "Name" is empty
        if(strlen($formvars['name']) == 0) {
            $this->error = '̾��������Ǥ���';
            return false; 
        }

        // test if "Name_Kana" is empty
        if(strlen($formvars['name_k']) == 0) {
            $this->error = '̾�����ɤ߲�̾������Ǥ���';
            return false; 
        }
        
        // ���ѥ��ʡ�
        if(!IsZenkakuKana($formvars['name_k'])){
            $this->error = '̾�����ɤ߲�̾�����ѥ������ʤ����Ϥ��Ƥ�������';
        	return false;
        }
        
        // ���ͥ����å�
        if(!IsDigit($formvas['code'])){
            $this->error = '�����ɤ�������Ϥ��Ƥ���������';
            return false;
        }
        
        // 
        if(!IsDigit($formvas['zip'])){
            $this->error = '͹���ֹ��������Ϥ��Ƥ���������';
            return false;
        }

        // 
        if(!IsDigit($formvas['tel'])){
            $this->error = '�����ֹ��������Ϥ��Ƥ���������';
            return false;
        }

        if(!IsDigit($formvas['fax'])){
            $this->error = '�ե��å����ֹ��������Ϥ��Ƥ���������';
            return false;
        }
        
        // form passed validation
        return true;
    }
    
    //
    // ��¸�ܥ��������
    //
    
    function saveEntry($formvars){
                
        $id = $formvars['code'];
        $this->sql->query(sprintf("select count(*) from m_vendor where code='%s' and deleted is null",$id),SQL_ALL,SQL_ASSOC);
        	
        // $x = $this->sql->numRows();  // record['count'];
        //$x = $this->sql->record[0]['count'];
        //print $x;
        
        if( $this->sql->record[0]['count'] > 0 )
        {
            // �쥳���ɤ�����Τ�update
            return $this->updateEntry($formvars);
        }
        // ������Ͽ��
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
    //  ����ɽ�����
    // 
    function findEntryExact($formvars) {
    	
        $id = pg_escape_string($formvars['code']);  
        $_query =  sprintf("select code,ckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,web_url from m_vendor where code = '%s' and deleted is null",$id);
        $this->sql->query($_query,SQL_INIT,SQL_ASSOC);
        return $this->sql->record;   
    }
    
    //
    // ��������
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
