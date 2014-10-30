<?php

error_reporting(0);

// Connect To Database
mysql_connect('localhost', 'root', 'root');
mysql_select_db('arborfinancialgroup');


// Include Pagination Class
include('../pagination.php');

// Display Links for Demo Purposes (not required)
include('links.html');
