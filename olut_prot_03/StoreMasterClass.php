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
 *    Ź�ޥޥ�������Ͽ - StoreMasterClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once('OlutAppLib.php');

class Store extends OlutApp
{

    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;
    // array of store section code.
    var $store_section_codes;

    /**
     * class constructor
     */
    function Store() {

        // instantiate the sql object
        $this->sql =& new Store_SQL;

        // instantiate the template object
        $this->tpl =& new Store_Smarty;
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
        $this->tpl->assign('store_section_list',$this->getStoreSectionList());

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('StoreMasterForm.tpl');
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

        $formvars['scode'] = trim($formvars['scode']);
        $formvars['sckd'] = trim($formvars['sckd']);
        $formvars['isdcd'] = trim($formvars['isdcd']);

        $formvars['name'] = trim($formvars['name']);
        $formvars['name_k'] = trim($formvars['name_k']);
        $formvars['tel'] = trim($formvars['tel']);
        $formvars['fax'] = trim($formvars['fax']);
        $formvars['zip'] = trim($formvars['zip']);
        $formvars['address'] = trim($formvars['address']);

        $formvars['print_flag'] = trim($formvars['print_flag']);
        $formvars['open_date'] = trim($formvars['open_date']);
        $formvars['close_date'] = trim($formvars['close_date']);

        $formvars['mail_address'] = trim($formvars['mail_address']);
        $formvars['web_url'] = trim($formvars['web_url']);

    }

    /**
     * test if form information is valid
     *
     * @param array $formvars the form variables
     */
    function isValidForm($formvars) {

        // reset error message
        $this->error = null;

        // Ź��������
        for($i=0; $i<16; $i++)
        {
            //
            $id="store_section_$i";
            $this->store_section_codes[$i] = $formvars[$id];
        }

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

        // ���������ե饰�� 0 or 1
        if($formvars['print_flag'] != '0' && $formvars['print_flag'] != '1')
        {
            $this->error = '���������ե饰���ͤ������Ǥ���(0�ޤ���1�����Ϥ��Ƥ���������';
            return false;
        }

        // ��ʣ�����å���
        for($i=0; $i<15; $i++)
        {
            for($j=0; $j<15; $j++)
            {
                if(($i != $j) && ($this->store_section_codes[$i] != -1))        // ��ʬ�����
                {
                    if($this->store_section_codes[$i] == $this->store_section_codes[$j])
                    {
                        $this->error = "Ź��������˽�ʣ���꤬����ޤ�";
                        return false;
                    }
                }
            }
        }

        // form passed validation
        return true;
    }

    //
    // ��¸�ܥ��������
    //

    function saveEntry($formvars){

        $id = $formvars['code'];
        $this->sql->query(sprintf("select count(*) from m_store where code='%s' and deleted is null",$id),SQL_ALL,SQL_ASSOC);

        if( $this->sql->record[0]['count'] > 0 )
        {
            // �쥳���ɤ�����Τ�update
            return $this->updateEntry($formvars);
        }
        // ������Ͽ��
        return    $this->addEntry($formvars);
    }

    /**
     * add a new store entry
     *
     * @param array $formvars the form variables
     */
    function addEntry($formvars)
    {
        //
        // we need transaction here.
        //
        $this->sql->Autocommit(false);
        
        //
        if(strlen($formvars['close_date'])==0)
        {
            $close_date = 'null';
        }
        else 
        {
            $close_date = OlutApp::formatDate($formvars['close_date']);
        }
        
        if($strlen($formvars['open_date']==0))
        {
            $open_date = 'null';
        }
        else 
        {
            $open_date = OlutApp::formatDate($formvars['open_date']);
        }

        $_query = sprintf(
        "insert into m_store (code,ckd,scode,sckd,isdcd,name,name_k,tel,fax,zip,address,print_flag,open_date,close_date,mail_address,web_url,updated) values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',NOW())",
        pg_escape_string($formvars['code']),
        pg_escape_string($formvars['ckd']),
        pg_escape_string($formvars['scode']),
        pg_escape_string($formvars['sckd']),
        pg_escape_string($formvars['isdcd']),
        pg_escape_string($formvars['name']),
        pg_escape_string($formvars['name_k']),
        pg_escape_string($formvars['tel']),
        pg_escape_string($formvars['fax']),
        pg_escape_string($formvars['zip']),
        pg_escape_string($formvars['address']),
        pg_escape_string($formvars['print_flag']),
        pg_escape_string($open_date),
        pg_escape_string($close_date),
        pg_escape_string($formvars['mail_address']),
        pg_escape_string($formvars['web_url'])
        );

        if(!$this->sql->query($_query))
        {
            // ���顼�ʤΤ����ǡ�
            $this->sql->Rollback();
            // ���Υ����ȥ��ߥåȤ��᤹��
            $this->sql->AutoCommit(true);
            return false;
        }

        //
        // insert store section.
        //  grabb from forms
        //
        $code = $formvars['code'];
        for($i=0; $i<16; $i++)
        {
            //
            $id="store_section_$i";
            $store_section_code = $formvars[$id];
            if(isset($store_section_code) && $store_section_code > 0)
            {
                $_query = "insert into m_store_section (code,store_section_code,updated) ";
                $_query .= " values ('$code','$store_section_code',now())";

                if(!$this->sql->query($_query))
                {
                    // ���顼�ʤΤ����ǡ�
                    $this->sql->Rollback();
                    // ���Υ����ȥ��ߥåȤ��᤹��
                    $this->sql->AutoCommit(true);
                    return false;
                }
            }
        }

        $this->sql->Commit();

        // ���Υ����ȥ��ߥåȤ��᤹��
        $this->sql->AutoCommit(true);
        return true;
    }

    /*
    *
    */
    function getStoreSection($code)
    {
        // Ź������������롣
        $_query = "select store_section_code from m_store_section where code='$code'";

        if(!$this->sql->query($_query,SQL_ALL))
        {
            return false;
        }

        if($this->sql->record != null)
        {
            $i = 0;
            foreach($this->sql->record as $rec)
            {
                $this->store_section_codes[$i++] = $rec[0];
            }
        }
        return true;
    }


    /**
     * update entry
     *
     * @param array $formvars the form variables
     */
    function updateEntry($formvars) {
        //
        if(strlen($formvars['close_date'])==0)
        {
            $close_date = 'null';
        }
        else 
        {
            $close_date = OlutApp::formatDate($formvars['close_date']);
        }
        
        if($strlen($formvars['open_date']==0))
        {
            $open_date = 'null';
        }
        else 
        {
            $open_date = OlutApp::formatDate($formvars['open_date']);
        }
        
        $_query = sprintf(
        "update m_store set ckd='%s',scode='%s',sckd='%s',name='%s',name_k='%s',isdcd='%s',tel='%s',fax='%s',zip='%s',address='%s',print_flag='%s',open_date='%s',close_date='%s',mail_address='%s',web_url='%s',updated=NOW() where code='%s' and deleted is null",
        pg_escape_string($formvars['ckd']),
        pg_escape_string($formvars['scode']),
        pg_escape_string($formvars['sckd']),
        pg_escape_string($formvars['name']),
        pg_escape_string($formvars['name_k']),
        pg_escape_string($formvars['isdcd']),
        pg_escape_string($formvars['tel']),
        pg_escape_string($formvars['fax']),
        pg_escape_string($formvars['zip']),
        pg_escape_string($formvars['address']),
        pg_escape_string($formvars['print_flag']),
        pg_escape_string($open_date),
        pg_escape_string($close_date),
        pg_escape_string($formvars['mail_address']),
        pg_escape_string($formvars['web_url']),
        pg_escape_string($formvars['code'])
        );

        if(!$this->sql->query($_query))
        {
            // ���顼�ʤΤ����ǡ�
            $this->sql->Rollback();
            // ���Υ����ȥ��ߥåȤ��᤹��
            $this->sql->AutoCommit(true);
            return false;
        }

        // �ǽ�ˤ��Ǥ�¸�ߤ���Τ���
        $code = $formvars['code'];
        $_query = "delete from m_store_section where code='$code'";

        if(!$this->sql->query($_query))
        {
            // ���顼�ʤΤ����ǡ�
            $this->sql->Rollback();
            // ���Υ����ȥ��ߥåȤ��᤹��
            $this->sql->AutoCommit(true);
            return false;
        }

        //
        // insert store section.
        //  grabb from forms
        //
        for($i=0; $i<15; $i++)
        {
            //
            $id="store_section_$i";
            $store_section_code = $formvars[$id];
            if(isset($store_section_code) && $store_section_code != -1)
            {
                $_query = "insert into m_store_section (code,store_section_code,updated) ";
                $_query .= " values ('$code','$store_section_code',now())";

                if(!$this->sql->query($_query))
                {
                    // ���顼�ʤΤ����ǡ�
                    $this->sql->Rollback();
                    // ���Υ����ȥ��ߥåȤ��᤹��
                    $this->sql->AutoCommit(true);
                    return false;
                }
            }
        }

        $this->sql->Commit();

        // ���Υ����ȥ��ߥåȤ��᤹��
        $this->sql->AutoCommit(true);


        // print $_query;
        return true;

    }

    /*
    * delete vendor entry.
    */

    function deleteEntry($formvars)
    {
        $code = pg_escape_string($formvars['code']);
        $_query = sprintf("update m_store set deleted=now() where code='%s'",$code);
        // print $_query;
        return $this->sql->query($_query);
    }

    /**
     * get the vendor entries
     */
    function getEntries()
    {

        $this->sql->query(
        "select code,ckd,scode,sckd,name,name_k,isdcd,tel,fax,zip,address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date,mail_address,web_url from m_store where deleted is null order by code DESC",
        SQL_ALL,
        SQL_ASSOC
        );

        return $this->sql->record;
    }

    function getPrevEntry($formvars)
    {
        $id = pg_escape_string($formvars['code']);

        if(!isset($id) || $id=='')
        {
            $_query = "select code,ckd,scode,sckd,name,name_k,isdcd,tel,fax,zip,address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date,mail_address,web_url from m_store where deleted is null order by code desc";
        }else{
            $_query =  sprintf("select code,ckd,scode,sckd,name,name_k,isdcd,tel,fax,zip,address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date ,mail_address,web_url from m_store where code < '%s' and deleted is null order by code desc",$id);
        }

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            return null;
        }
        //
        $record = $this->sql->record;
        $code   = $this->sql->record['code'];
        $this->getStoreSection($code);
        return $record;
    }

    function getNextEntry($formvars) {

        $id = pg_escape_string($formvars['code']);

        if(!isset($id) || $id=="" )
        {
            $_query =  "select code,ckd,scode,sckd,name,name_k,isdcd,tel,fax,zip,address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date,mail_address,web_url from m_store where deleted is null order by code  asc";
        }
        else
        {
            $_query =  sprintf("select code,ckd,scode,sckd,name,name_k,tel,fax,zip,address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date,mail_address,web_url from m_store where code > '%s'  and deleted is null order by code asc",$id);
        }

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            return null;
        }
        //
        $record = $this->sql->record;
        $code   = $this->sql->record['code'];
        $this->getStoreSection($code);
        return $record;
    }

    //
    //  ����ɽ�����
    //
    function findEntryExact($formvars)
    {
        $id = pg_escape_string($formvars['code']);

        $_query =  sprintf("select code,ckd,scode,sckd,name,name_k,isdcd,tel,fax,zip,address,mail_address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date ,web_url from m_store where code = '%s' and deleted is null order by code asc",$id);
        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            return null;
        }
        //
        $record = $this->sql->record;
        $code   = $this->sql->record['code'];
        $this->getStoreSection($code);
        return $record;
    }

    //
    // ��������
    //

    function findEntry($formvars)
    {
        $id = pg_escape_string($formvars['search_code']);

        $_query =  sprintf("select code,ckd,scode,sckd,name,name_k,isdcd,tel,fax,zip,address,print_flag,to_char(open_date,'YYYYMMDD') as open_date,to_char(close_date,'YYYYMMDD') as close_date,mail_address,web_url from m_store where code like '%s%%' and deleted is null",$id);
        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            return null;
        }
        //
        $record = $this->sql->record;
        $code   = $this->sql->record['code'];
        $this->getStoreSection($code);
        return $record;
    }

    /**
     * display the vendor
     *
     * @param array $data the vendor data
     */
    function display($data = array()) {

        $this->tpl->assign('post', $data);
        $this->tpl->display('StoreMasterForm.tpl');

    }

    function getStoreSectionList()
    {
        $_query = "select code,name from m_store_division order by code";
        if(!$this->sql->query($_query,SQL_ALL))
        {
            return null;
        }
        if($this->sql->record != null)
        {
            for($i=0; $i<15; $i++)
            {
                $result[$i] = '<option value="-1">-----------</option>';
                foreach($this->sql->record as $rec)
                {
                    if($rec[0] == $this->store_section_codes[$i])
                    {
                        $result[$i] .= "<option value='$rec[0]' selected>$rec[1]</option>";
                    }
                    else
                    {
                        $result[$i] .= "<option value='$rec[0]'>$rec[1]</option>";
                    }
                }
            }
        }
        return $result;
    }

    function clear()
    {
        unset($this->store_section_codes);
    }
}

?>
