<?php

if(isset($_GET['backup']) && $_GET['backup']=='true')
    backup_tables(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $wpdb->prefix . "micronews-table");

/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$name,$table)
{
    $link = mysqli_connect($host,$user,$pass);
    mysqli_select_db($link, $name);
    
    $result = mysqli_query($link, 'SELECT * FROM '.$table);
    $num_fields = mysqli_num_fields($result);
    
    $return = "";
    
    $return.= 'DROP TABLE IF EXISTS '.$table.';';
    $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
    $return.= "\n\n".$row2[1].";\n\n";
                
    while($row = mysqli_fetch_row($result))
    {
        $return.= 'INSERT INTO '.$table.' VALUES(';
        for($j=0; $j<$num_fields; $j++) 
        {
            $row[$j] = addslashes($row[$j]);
            $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
            if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
            if ($j<($num_fields-1)) { $return.= ','; }
        }
        $return.= ");\n";
    }
    $return.="\n\n\n";
    $return.="\n -- Backup of MicroNews Widget";

    
    //Downloading file     
    $filename = 'db-micronews-table-backup-'.time().'.sql';
    Header("Content-type: application/octet-stream");
    Header("Content-Type: text/x-sql");
    Header("Content-Disposition: attachment; filename=$filename");
    echo $return;
    
}

?>
