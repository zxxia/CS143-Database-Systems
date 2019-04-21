<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Dashboard">
  <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
  <style>
    .error {color: #FF0000;}
  </style>
  <title>CS143 Database Query System</title>

  <!-- Bootstrap core CSS -->
  <link href="assets/css/bootstrap.css" rel="stylesheet">
  <!--external css-->
  <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
  <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
  <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">    

  <!-- Custom styles for this template -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/style-responsive.css" rel="stylesheet">

  <script src="assets/js/chart-master/Chart.js"></script>

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
    </head>

    <body>

      <section id="container" >
      <!-- **********************************************************************************************************************************************************
      TOP BAR CONTENT & NOTIFICATIONS
      *********************************************************************************************************************************************************** -->
      <!--header start-->
      <header class="header black-bg">
        <div class="sidebar-toggle-box">
          <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
        </div>
        <!--logo start-->
        <a href="index.php" class="logo"><b>CS143 Database Query System</b></a>
        <!--logo end-->

        <div class="top-menu">
         <ul class="nav pull-right top-menu">
          <li><a class="active" href="search.php">Search</a></li>
        </ul>
      </div>
    </header>
    <!--header end-->

      <!-- **********************************************************************************************************************************************************
      MAIN SIDEBAR MENU
      *********************************************************************************************************************************************************** -->
      <!--sidebar start-->
      <aside>
        <div id="sidebar"  class="nav-collapse ">
          <!-- sidebar menu start-->
          <ul class="sidebar-menu" id="nav-accordion">
            <li class="mt">
              <a class="active" href="add_act_dir.php">
                <i class="fa fa-desktop"></i>
                <span>Add an Actor/a Director</span>
              </a>
            </li>

            <li class="mt">
              <a class="active" href="add_mov.php" >
                <i class="fa fa-desktop"></i>
                <span>Add a Movie</span>
              </a>
            </li>

            <li class="mt">
              <a class="active" href="act-mov.php" >
                <i class="fa fa-desktop"></i>
                <span>Add an actor to a moive</span>
              </a>
            </li>
            <li class="mt">
              <a class="active" href="dir-mov.php" >
                <i class="fa fa-desktop"></i>
                <span>Add a director to a movie</span>
              </a>
            </li>
            <li class="mt">
              <a class="active" href="add_review.php" >
                <i class="fa fa-desktop"></i>
                <span>Comment a Movie</span>
              </a>
            </li>
            <li class="mt">
              <a class="active" href="browse_act.php" >
                <i class="fa fa-desktop"></i>
                <span>Look for an actor/actress?</span>
              </a>
            </li>

            <li class="mt">
              <a class="active" href="browse_mov.php" >
                <i class="fa fa-desktop"></i>
                <span>Look for a movie?</span>
              </a>
            </li>
          </ul>
          <!-- sidebar menu end-->
        </div>
      </aside>
      <!--sidebar end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
      <!--main content start-->
      <section id="main-content">

        <section class="wrapper">





          <!-- BASIC FORM ELELEMNTS -->
          <div class="row mt">
            <div class="col-lg-12">
              <div class="form-panel">
                <h4 class="mb"><i class="fa fa-angle-right"></i> Basic Information</h4>
                <form class="form-horizontal style-form" method="get" action="<?php echo ($_SERVER["PHP_SELF"]);?>">

                  <div class="form-group">
                    <label class="col-sm-2 control-label">Keyword Search</label>
                    <div class="col-sm-7">
                      <input type="text" class="form-control" placeholder="Please type keyword(s)..." name="search">
                      <span class="help-block">EX. Tom Hanks</span>
                    </div>
                    <button type="submit" class="btn btn-default">Search!</button>
                  </div>
                </form>


                <?php
                if($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET["search"])){
                  printf('<h3><i class="fa fa-angle-right"></i> Output:</h3><br>');

                  $keyword_array = explode(" ", $_GET["search"]);
                  
                  $db = new mysqli('localhost', 'cs143', '', 'CS143');
                  if($db->connect_errno > 0)
                    die('Unable to connect to database [' . $db->connect_error . ']');


                  // Generate search results in movie table
                  printf('<h4><i class="fa fa-angle-down"></i> Movies</h4>');
                  printf('<table class="table">');

                  foreach($keyword_array as $key)
                    $str = $str."title LIKE '%".$key."%' AND ";
                  $str = rtrim($str, " AND ");
                  $query = "SELECT id, title, year, company FROM Movie WHERE (".$str.");";
                  //echo "$query<br>";
                  if(!$result = $db->query($query))
                    echo "Invalid query.<br>";
                  else
                  {
                    //echo "Success!";


                  // Print name of each col
                    printf("<thread>");
                    printf("<tr>");
                    $field_cnt = $result->field_count;
                    for ($i = 1; $i < $field_cnt; $i++) {
                      $finfo = $result->fetch_field_direct($i);
                      printf ("<th> %s </th>", $finfo->name);
                    }
                    printf("</tr>");
                    printf("</thread>");

                    printf("<tbody>");
                  // Print each row in each table
                    while ($row = $result->fetch_assoc()) {
                      print "<tr>";
                      for($i = 1; $i < $field_cnt; $i++) {
                        $finfo = $result->fetch_field_direct($i);
                        $col_name = $finfo->name;
                        $col_content = $row[$col_name] == null ? 'N/A' : $row[$col_name];
                        //print "<td> $col_content </td>";
                        echo "<td> <a href=browse_mov.php?title=",$row["id"],">$col_content</a></td>";
                      }
                      print "</tr>";
                    }
                    $result->free();
                    printf("</tbody>");
                    printf('</table>');
                  }



                  // Generate results in actor table
                  if(sizeof($keyword_array)<3){

                    if(sizeof($keyword_array) == 1)
                      $query = "SELECT id, first, last, dob FROM Actor WHERE (last LIKE '%".$keyword_array[0]."%' OR first LIKE '%".$keyword_array[0]."%');";
                      //echo "$query<br>";
                    else if(sizeof($keyword_array)==2)
                      $query = "SELECT id, first, last, dob, dod FROM Actor WHERE (last LIKE '%".$keyword_array[1]."%' AND first LIKE'".$keyword_array[0]."%');";
                    //echo "$query<br>";
                    if(!$result = $db->query($query))
                      echo "Invalid query.<br>";
                    else {
                      //echo "Success!";
                      printf('<h4><i class="fa fa-angle-down"></i> Actor/Actress</h4>');
                      printf('<table class="table">');
                      // Print name of each col
                      printf("<thread>");
                      printf("<tr>");
                      $field_cnt = $result->field_count;
                      for ($i = 1; $i < $field_cnt; $i++) {
                        $finfo = $result->fetch_field_direct($i);
                        printf ("<th> %s </th>", $finfo->name);
                      }
                      printf("</tr>");
                      printf("</thread>");

                      // Print each row in each table
                      printf("<tbody>");
                      while ($row = $result->fetch_assoc()) {
                        print "<tr>";
                        for($i = 1; $i < $field_cnt; $i++) {
                          $finfo = $result->fetch_field_direct($i);
                          $col_name = $finfo->name;
                          $col_content = $row[$col_name] == null ? 'N/A' : $row[$col_name];
                          echo "<td> <a href=browse_act.php?name=",$row["id"],">$col_content</a></td>";
                          //print "<td> $col_content </td>";
                        }
                      }
                      $result->free();
                      printf("</tbody>");
                      printf("</table>");
                    }
                  }
                  $db->close();
              }// end of get
              ?>

            </div>
          </div><!-- col-lg-12-->       
        </div><!-- /row -->
      </section>
    </section>

    <!--main content end-->
    <!--footer start-->
    <footer class="site-footer">
      <div class="text-center">
        2016 Fall - UCLA - Zhengxu Xia
        <a href="index.html#" class="go-top">
          <i class="fa fa-angle-up"></i>
        </a>
      </div>
    </footer>
    <!--footer end-->
  </section>

  <!-- js placed at the end of the document so the pages load faster -->
  <script src="assets/js/jquery.js"></script>
  <script src="assets/js/jquery-1.8.3.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
  <script src="assets/js/jquery.scrollTo.min.js"></script>
  <script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>
  <script src="assets/js/jquery.sparkline.js"></script>


  <!--common script for all pages-->
  <script src="assets/js/common-scripts.js"></script>

  <script type="text/javascript" src="assets/js/gritter/js/jquery.gritter.js"></script>
  <script type="text/javascript" src="assets/js/gritter-conf.js"></script>

  <!--script for this page-->
  <script src="assets/js/sparkline-chart.js"></script>    
  <script src="assets/js/zabuto_calendar.js"></script>  


  <script type="application/javascript">
    $(document).ready(function () {
      $("#date-popover").popover({html: true, trigger: "manual"});
      $("#date-popover").hide();
      $("#date-popover").click(function (e) {
        $(this).hide();
      });

      $("#my-calendar").zabuto_calendar({
        action: function () {
          return myDateFunction(this.id, false);
        },
        action_nav: function () {
          return myNavFunction(this.id);
        },
        ajax: {
          url: "show_data.php?action=1",
          modal: true
        },
        legend: [
        {type: "text", label: "Special event", badge: "00"},
        {type: "block", label: "Regular event", }
        ]
      });
    });


    function myNavFunction(id) {
      $("#date-popover").hide();
      var nav = $("#" + id).data("navigation");
      var to = $("#" + id).data("to");
      console.log('nav ' + nav + ' to: ' + to.month + '/' + to.year);
    }
  </script>


</body>
</html>
