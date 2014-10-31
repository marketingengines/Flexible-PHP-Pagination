<?php
  // Initial Search Script

  $search_term = '';
  $search_output = '';

  // Search is Posted 
  if (isset($_GET['search_term']) && !empty($_GET["search_term"])) {
    try{
      // Prevent SQL Injection by Sanitizing Search Term
      $search_term = strtolower(trim(strip_tags(stripslashes(preg_replace('#[^a-z \- 0-9?!]#i', '', $_GET['search_term'])))));


      // Start Wrap Search Results
      echo '
        <section class="search-results">
          <div class="container">
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="search-results-container">
      ';

        // If Search Term is Empty after Sanitizing
        if ($search_term == ""){
          $search_output .= "<div class='search-result-error'><p class='bg-danger'>Enter a valid search term</p></div>";
          echo $search_output;
        }
        // If Search Term is Less Than 4 Chars (FULLTEXT Requires Min 3 Chars)
        elseif ( strlen(utf8_decode($search_term)) < 4 ){
          $search_output .= "<div class='search-result-error'><p class='bg-warning'><strong>$search_term</strong><span> is too short.  Enter at least 4 characters</span></p></div>";
          echo $search_output;
        }
        // Proceed Only if Passed Validation
        else {

          // 1. Connect to Database
          require_once(INCLUDE_DIR . 'connect.mysql.php');
          #require_once(INCLUDE_DIR . 'connect.mysql.local.php');

          // 2. Track searched term by inserting it into keywords table
          $sql = "INSERT INTO `arborfinancialgroup`.`keywords` (`id`, `keyword`, `category`, `status`, `when`) VALUES (NULL, '$search_term', 'UNCATEGORIZED', 'NOT REVIEWED YET', CURRENT_TIMESTAMP);";
          $s = $dbh->prepare($sql);
          $s->execute();

          // 3. Query pagetags table
          $quoted_search_term = $dbh->quote($search_term);
          $query = "SELECT * FROM pagetags WHERE MATCH (page_category, page_title, page_description, page_keywords) AGAINST ($quoted_search_term IN BOOLEAN MODE) LIMIT 0 , 30";
          $q = $dbh->prepare($query);

          // 4. Bind Params
          $q->bindParam(':search_term', $search_term, PDO::PARAM_STR);
          $q->bindParam(':page_id', $page_id, PDO::PARAM_INT);
          $q->bindParam(':page_image', $page_image, PDO::PARAM_STR);
          $q->bindParam(':page_category', $page_category, PDO::PARAM_STR);
          $q->bindParam(':page_title', $page_title, PDO::PARAM_STR);
          $q->bindParam(':page_description', $page_description, PDO::PARAM_STR);
          $q->bindParam(':page_url', $page_url, PDO::PARAM_STR);
          $q->bindParam(':page_keywords', $page_keywords, PDO::PARAM_STR);
          $q->execute();

          // 5. Fetch Rows
          $check = $q->fetchAll(PDO::FETCH_ASSOC);
          $row_count = count($check); #return # of rows

          // 6. Display Results
          #echo 'Results found: ' . $row_count . '<hr>';
          if($row_count > 0){

            // a) Display RESULT OR RESULT(S)
            if($row_count == 1){
              $row_count = $row_count . ' result';
            }else{
              $row_count = $row_count . ' results';
            }

            // b) Display Result Count
            $search_output .= "<div class='search-result-count'><small><span>$row_count for </span><nobr><strong>$search_term</strong></nobr></small></div>";
            echo $search_output;

            // c) Display Search Results
            for ($i=0; $i < $row_count; ++$i ){
              $row_id          = $check[$i]['page_id'];
              $row_image       = $check[$i]['page_image'];
              $row_category    = $check[$i]['page_category'];
              $row_title       = $check[$i]['page_title'];
              $row_description = $check[$i]['page_description'];
              $row_url         = $check[$i]['page_url'];
              $row_keywords    = $check[$i]['page_keywords']; 
              //output
              echo $output = '
                <div class="search-result-item">
                  <div class="image-container hidden-xs hidden-mobile">
                    <a href="'.$row_url.'" rel="follow" target="_self">
                      <img class="img-thumbnail" src="'.$siteURL.$row_image.'" alt="'.$row_category.'" title="In '.$row_category.' Center" data-toggle="tooltip" tooltip/>
                    </a>
                  </div>
                  <div class="info-container">
                    <h4>
                      <a href="'.$row_url.'" rel="follow" target="_self">
                        <span class="page-title">'.$row_title.'</span>
                      </a>
                    </h4>
                    <p class="page-description">'.$row_description.'</p>
                    <p class="page-url"><i class="icon-link-5"></i><a href="'.$row_url.'" rel="follow">'.$row_url.'</a></p>
                    <p class="page-keywords"><i class="icon-tags"></i><strong>'.$row_category.' center: </strong><span>'.$row_keywords.'</span></p>
                  </div>
                  <div class="clearfix" style="clear:both;"></div>
                </div>';
              //output
            }

            // d. Show Current MySQL Command
            // debug and development only
            #$search_output .= "<hr />$query";

          } else {
            // No Resutls Found
            $search_output .= "<div class='search-result-zero'><p><span>Sorry, no results for </span><nobr><strong>$search_term</strong></nobr>! Try something different and with multiple words.</p></div>";
            echo $search_output;

            // Show Current MySQL Command
            // debug and development only
            #$search_output .= "<hr />$query";
          } //end if $row_count > 0

        }

      // End Wrap Search Results
      echo '
                  </div>
                </div>
              </div>
            </div>
          </section>
      ';

    }catch(PDOException $e){
      //Error reporting if something went wrong...
      echo 'Error: ' . $e->getMessage();
      var_dump($dbh->errorInfo());
      exit();
    }


  } // end if posted
  else{

      // Display Errors if found
      #error_reporting(E_ALL);
      #ini_set('display_errors', '1');

      // Connect to Database
      require_once(INCLUDE_DIR . 'connect.mysql.php');
      #require_once(INCLUDE_DIR . 'connect.mysql.local.php');

      // Add Pagination Class
      include(INCLUDE_DIR . 'pagination.php');
      $max = 10;    # maximum results per page
      $maxNum = 5; # maximum digits in pagination nav

      // Query the Database and Get $total Rows Count
      $select = "SELECT * FROM pagetags";
      $query = $dbh->prepare($select);
      $query->execute();
      $count = $query->fetchAll(PDO::FETCH_ASSOC);
      $total = count($count);

      // Create Pagination Object
      $nav = new Pagination($max, $total, $maxNum);

      // Prepare Query & Limit to Max
      #$q = $dbh->prepare("SELECT * FROM pagetags");
      $s = "SELECT * FROM pagetags LIMIT " . $nav->start() . "," . $max;
      $q = $dbh->prepare($s);

      // Bind Params
      $q->bindParam(':page_id', $page_id, PDO::PARAM_INT);
      $q->bindParam(':page_image', $page_image, PDO::PARAM_STR);
      $q->bindParam(':page_category', $page_category, PDO::PARAM_STR);
      $q->bindParam(':page_title', $page_title, PDO::PARAM_STR);
      $q->bindParam(':page_description', $page_description, PDO::PARAM_STR);
      $q->bindParam(':page_url', $page_url, PDO::PARAM_STR);
      $q->bindParam(':page_keywords', $page_keywords, PDO::PARAM_STR);
      $q->execute();

      // Fetch Rows and Return Row Count
      $check = $q->fetchAll(PDO::FETCH_ASSOC);
      $row_count = count($check);

      // Display All Results
      if ($row_count == 0){
        echo '<div class="text-center mt110 mb110">
                <p><strong>'.$row_count.'</strong> results.</p>
                <p><small>Database is empty!</small></p>
                <hr>
              </div>';
      }
      /*
      # Uncomment this to display row count
      # if not handled by Pagination Class
      else{
        echo '<div class="text-center mt110 mb110">
                <p>Displaying around <strong>'.$row_count.'</strong> results.</p>
                <p><small>We encourage you to narrow your search term for better results!</small></p>
                <hr>
              </div>';
      }
      */
        // Start Wrap Search Results
        echo '
          <section class="search-results">
            <div class="container">
              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="search-results-container">
        ';

      #echo "<div class='search-result-count'><small><span>$row_count results found! </span></small></div>";
      if ($row_count > 0){

        for ($i=0; $i < $row_count; ++$i ){
          $row_id          = $check[$i]['page_id'];
          $row_image       = $check[$i]['page_image'];
          $row_category    = $check[$i]['page_category'];
          $row_title       = $check[$i]['page_title'];
          $row_description = $check[$i]['page_description'];
          $row_url         = $check[$i]['page_url'];
          $row_keywords    = $check[$i]['page_keywords'];

          echo $output = '
            <div class="search-result-item">
              <div class="image-container hidden-xs hidden-mobile">
                <a href="'.$row_url.'" rel="follow" target="_self">
                  <img class="img-thumbnail" src="'.$siteURL.$row_image.'" alt="'.$row_category.'" title="In '.$row_category.' Center" data-toggle="tooltip" tooltip/>
                </a>
              </div>
              <div class="info-container">
                <h4>
                  <a href="'.$row_url.'" rel="follow" target="_self">
                    <span class="page-title">'.$row_title.'</span>
                  </a>
                </h4>
                <p class="page-description">'.$row_description.'</p>
                <p class="page-url"><i class="icon-link-5"></i><a href="'.$row_url.'" rel="follow">'.$row_url.'</a></p>
                <p class="page-keywords"><i class="icon-tags"></i><strong>'.$row_category.' center: </strong><span>'.$row_keywords.'</span></p>
              </div>
              <div class="clearfix" style="clear:both;"></div>
            </div>';
        }

        # Display Navigation Only if
        # Total Results are Greater than Maximum 
        if ($max < $total) {
          // Pagination Navigation
          $link = 'search.php?p=';
          echo '<div class="search-result-pagination"><div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 text-left">';
          echo $nav->info('Result {start} to {end} of {total} ');
          echo '</div>';
          echo '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-center">';
          echo $nav->first(   ' <a href="'.$link.'{nr}">First   </a> | ', ' First | ');
          echo $nav->previous(' <a href="'.$link.'{nr}">Previous</a> | ', ' Previous | ');
          echo $nav->numbers( ' <a href="'.$link.'{nr}">{nr}    </a> | ', '<span class="active">{nr}</span> | ');
          echo $nav->next(    ' <a href="'.$link.'{nr}">Next    </a> | ', ' Next | ');
          echo $nav->last(    ' <a href="'.$link.'{nr}">Last    </a>   ', ' Last ');
          echo '</div>';
          echo '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 text-right">';
          echo $nav->info('Page {page} of {pages} ');
          echo '</div></div>';
          // End Pagination Navigation
        }

      }
      else{
        echo "<div class='search-result-error'><p class='bg-warning text-center'><span>No Results!</span></p></div>";
      }

      // Close Connection
      $dbh = null;

      // End Wrap Search Results
      echo '
                  </div>
                </div>
              </div>
            </div>
          </section>
      ';
    // Display All Resutls
  }

  // 7. Close Connection
  $dbh = null;
?>
