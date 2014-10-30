<?php
include('config.php');

$max = 4;
$select = "SELECT * FROM keywords";
$query1 = mysql_query($select) or die( mysql_error() ); 
$total = mysql_num_rows($query1);

$nav = new Pagination($max, $total);

$query2 = mysql_query($select." LIMIT ".$nav->start().",".$max) or die(mysql_error()); 
while($item = mysql_fetch_object($query2)) 
{ 
    echo $item->id . ' - <b>' . $item->keyword . '</b><br />';
}

$link = 'normal.php?p=';

//echo $nav->first(' <a href="'.$link.'{nr}">First</a> | ');

echo $nav->previous(' <a href="'.$link.'{nr}">Previous</a> | ');

echo $nav->numbers(' <a href="'.$link.'{nr}">{nr}</a> | ', ' <b>{nr}</b> | ');

echo $nav->next(' <a href="'.$link.'{nr}">Next</a> | ');

//echo $nav->last(' <a href="'.$link.'{nr}">Last</a>');
