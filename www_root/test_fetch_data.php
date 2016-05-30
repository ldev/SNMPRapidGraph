<?php
    require_once('../db_connect.php');
    $query = 'SELECT * FROM data';
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());

    // Printing results in HTML
    echo "<table>\n";
    while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        echo "\t<tr>\n";
        foreach ($line as $col_value) {
            echo "\t\t<td>$col_value</td>\n";
        }
        echo "\t</tr>\n";
    }
    echo "</table>\n";
