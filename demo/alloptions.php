<?php
  include('config.php');

  $max = 4;
  $maxNum = 4;
  $select = "SELECT * FROM keywords";
  $query1 = mysql_query($select) or die( mysql_error() ); 
  $total = mysql_num_rows($query1);
  echo 'Total results in database: ';
  print_r($total);
  echo '<br><hr>';

  #$nav = new Pagination($max, $total);
  #$query2 = mysql_query($select." LIMIT ".$nav->start().",".$max) or die(mysql_error()); 

  $nav = new Pagination($max, $total, $maxNum);
  $select2 = "SELECT * FROM keywords LIMIT " . $nav->start() . "," . $max;
  $query2 = mysql_query($select2) or die(mysql_error());
  $total2 = mysql_num_rows($query2);
  echo 'Total results per page: ';
  print_r($total2); #4
  echo '<br><hr>';

  while($item = mysql_fetch_object($query2)) 
  { 
      echo '<br>'. $item->id . ' - <b>' . $item->keyword . '</b><br />';
  }

  $link = 'alloptions.php?p=';

  echo '<br><hr><br>';
  echo $nav->first(   ' <a href="'.$link.'{nr}">First   </a> | ', ' First | ');
  echo $nav->previous(' <a href="'.$link.'{nr}">Previous</a> | ', ' Previous | ');
 #echo $nav->numbers( ' <a href="'.$link.'{nr}">{nr}    </a> | ', ' <b>{nr}</b> | ');
  echo $nav->numbers( ' <a href="'.$link.'{nr}">{nr}    </a> | ', '<span class="active">{nr}</span> | ');
  echo $nav->next(    ' <a href="'.$link.'{nr}">Next    </a> | ', ' Next | ');
  echo $nav->last(    ' <a href="'.$link.'{nr}">Last    </a>   ', ' Last ');
  echo '<hr>';
  echo $nav->info('Result {start} to {end} of {total} ');
  echo '<hr>';  
  echo $nav->info('Page {page} of {pages} ');
