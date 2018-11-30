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
 *    SQL.php  -- pear db wrapper.
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

// define the query types
define('SQL_NONE', 1);
define('SQL_ALL', 2);
define('SQL_INIT', 3);

// define the query formats
define('SQL_ASSOC', 1);
define('SQL_INDEX', 2);

class SQL {
    
    var $db = null;
    var $result = null;
    var $error = null;
    var $record = null;
    
    /**
     * class constructor
     */
    function SQL() { }
    
    /**
     * connect to the database
     *
     * @param string $dsn the data source name
     */
    function connect($dsn) {
        $this->db = DB::connect($dsn);

        if(DB::isError($this->db)) {
            $this->error = $this->db->getMessage();
            return false;
        }        
        return true;
    }
    
    /**
     * disconnect from the database
     */
    function disconnect() {
        $this->db->disconnect();   
    }
    
    /**
     * query the database
     *
     * @param string $query the SQL query
     * @param string $type the type of query
     * @param string $format the query format
     */
    function query($query, $type = SQL_NONE, $format = SQL_INDEX) {

        if ($this->db == null)
        {
            return false;
        }

        $this->error = null;

        $this->record = array();
        $_data = array();

        // determine fetch mode (index or associative)
        $_fetchmode = ($format == SQL_ASSOC) ? DB_FETCHMODE_ASSOC : null;

        //
        //print "$query<br>";
        //print_r($this->db);

        $this->result = $this->db->query($query);
        // $this->result = $db->query($query);
        if (DB::isError($this->result)) {
            $this->error = $this->result->getMessage();
            return false;
        }

        switch ($type) {
            case SQL_ALL:
            // get all the records
            while($_row = $this->result->fetchRow($_fetchmode)) {
                $_data[] = $_row;
            }
            $this->result->free();
            $this->record = $_data;
            break;
            case SQL_INIT:
            // get the first record
            $this->record = $this->result->fetchRow($_fetchmode);
            break;
            case SQL_NONE:
            default:
            // records will be looped over with next()
            break;
        }
        return true;
    }

    /**
     * connect to the database
     *
     * @param string $format the query format
     */
    function next($format = SQL_INDEX) {
        // fetch mode (index or associative)
        $_fetchmode = ($format == SQL_ASSOC) ? DB_FETCHMODE_ASSOC : null;
        if ($this->record = $this->result->fetchRow($_fetchmode)) {
            return true;
        } else {
            $this->result->free();
            return false;
        }
    }

    /*
    *   コミットモードの変更。
    */

    function AutoCommit($onoff)
    {
        return $this->db->AutoCommit($onoff); //
    }

    /*
    *  コミットする。
    */
    function Commit()
    {
        return $this->db->Commit();
    }

    /*
    *  ロールバックする。
    */
    function Rollback()
    {
        return $this->db->Rollback();
    }
}

?>
