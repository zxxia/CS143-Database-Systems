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
                <span>Add a actor to a movie</span>
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


          <?php
          $incomplete = 0;
          if($_SERVER["REQUEST_METHOD"] == "GET"){
            if(empty($_GET["name"])){
              $nameErr = "Name is required.";
              $incomplete = 1;
            }


            if(empty($_GET["title"])){
              $titleErr = "A title is required.";
              $incomplete = 1;
            }

            if(empty($_GET["rating"])){
              $ratingErr = "Rating is required.";
              $incomplete = 1;
            }

            ?>


            <!-- BASIC FORM ELELEMNTS -->
            <div class="row mt">
              <div class="col-lg-12">
                <div class="form-panel">
                  <h4 class="mb"><i class="fa fa-angle-right"></i> Basic Information</h4>
                  <form class="form-horizontal style-form" method="get" action="<?php echo ($_SERVER["PHP_SELF"]);?>">

                    <div class="form-group">
                      <label class="col-sm-2 control-label">Name</label>
                      <div class="col-sm-7">
                        <input type="text" class="form-control" placeholder="Please type a name..." name="name">
                        <span class="help-block">EX. Tom</span>
                      </div>
                      <span class="error">* <?php echo $nameErr;?></span>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-2 control-label">Movie Title</label>
                      <div class="col-sm-7">
                        <select class="form-control" name="title">
                          <?php
                          $db = new mysqli('localhost', 'cs143', '', 'CS143');
                          if($db->connect_errno > 0)
                            die('Unable to connect to database [' . $db->connect_error . ']');
                          $query = "SELECT id, title FROM Movie ORDER BY title;";
                      //echo $query;
                          if(!$result = $db->query($query))
                            echo "Invalid query.<br>";
                          else{
                            while($row=$result->fetch_row())
                              echo '<option value="'.$row[0].'">'.$row[1].'</option>';
                          }
                          $result->free();
                          $db->close();
                          ?>
                        </select>
                      </div>
                      <span class="error">* <?php echo $titleErr;?></span>
                    </div>



                    <div class="form-group">
                      <label class="col-sm-2 control-label">Rating</label>
                      <div class="col-sm-7">
                        <select   class="form-control" name="rating">
                          <option value="1">1</option>
                          <option value="2">2</option>
                          <option value="3">3</option>
                          <option value="4">4</option>
                          <option value="5">5</option>
                        </select>
                      </div>
                      <span class="error">* <?php echo $ratingErr;?></span>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-2 col-sm-2 control-label">Comment</label>
                      <div class="col-sm-7">
                        <input type="text" class="form-control" placeholder="Please leave a comment..." name="comment">
                      </div>
                    </div>

                    <div class="form-group">
                      <div class="col-sm-7 col-sm-offset-5">
                        <button type="submit" class="btn btn-default">Add!</button>
                      </div>
                    </div>


                    
                      <?php
                      if(!$incomplete) {
                        printf('<h4><i class="fa fa-angle-right"></i> Output:</h4>');
                    printf('<div class="alert alert-success">');
                        $db = new mysqli('localhost', 'cs143', '', 'CS143');
                        if($db->connect_errno > 0)
                          die('Unable to connect to database [' . $db->connect_error . ']');
                        $mid = $_GET["title"];
                        $query = "INSERT INTO Review VALUES ('".$_GET["name"]."', now(), ".$mid.", ".$_GET["rating"].", '".$_GET["comment"]."')";
                      //echo $query;
                        if(!$result = $db->query($query))
                          echo "Invalid query.";
                        else
                          echo "Sucess!<br>";
                        $result->free();
                        $db->close();
                        printf('</div>');
                      }
                    }
                    ?>
                            


                </form>
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
