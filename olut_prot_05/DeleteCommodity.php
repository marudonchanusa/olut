<?php
include('sys_define.inc');


require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

function deleteCommodity()
{
    $fn = 'c:\ntc\olut\doc\commodity_delete_list.txt';
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

        $ar = split("\t",$line);

        $code = $ar[0];

        if(isset($ar[2]) && strlen(trim($ar[2])))  // ¹õ´Ý¡£
        {
            $_query = "delete from m_commodity where code='$code'";


            if($db->query($_query)==false)
            {
                break;
            }
        }
    }

    $db->disconnect();

    fclose($fh);

}

// main.

deleteCommodity();


?>