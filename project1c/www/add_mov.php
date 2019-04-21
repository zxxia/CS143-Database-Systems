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


          <?php
          $incomplete = 0;
          if($_SERVER["REQUEST_METHOD"] == "GET"){
            if(empty($_GET["title"])){
              $titleErr = "A title is required.";
              $incomplete = 1;
            }
            if(empty($_GET["company"])){
              $companyErr = "A comapny name is required.";
              $incomplete = 1;
            }

            if(empty($_GET["year"])){
              $yearErr = "A release year is required.";
              $incomplete = 1;
            }


            if(empty($_GET["rating"])){
              $ratingErr = "A rating is required.";
              $incomplete = 1;
            }

            if(empty($_GET["genre"])){
              $genreErr = "Genre is required.";
              $incomplete = 1;
            }
            else{
              foreach ($_GET["genre"] as $option)
                $genre .= $option.", ";
              $genre=chop($genre, ", ");
            }

            ?>

            


            <!-- BASIC FORM ELELEMNTS -->
            <div class="row mt">
              <div class="col-lg-12">
                <div class="form-panel">
                  <h4 class="mb"><i class="fa fa-angle-right"></i> Basic Information</h4>
                  <form class="form-horizontal style-form" method="get" action="<?php echo ($_SERVER["PHP_SELF"]);?>">

                    <div class="form-group">
                      <label class="col-sm-2 control-label">Title</label>
                      <div class="col-sm-7">
                        <input type="text" class="form-control" placeholder="Please type a title..." name="title">
                        <span class="help-block">EX. Spider Man</span>
                      </div>
                      <span class="error">* <?php echo $titleErr;?></span>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-2 control-label">Company</label>

                      <div class="col-sm-7">
                        <input type="text" class="form-control" placeholder="Please type a company name..." name="company">
                        <span class="help-block">EX. Disney</span>
                      </div>
                      <span class="error">* <?php echo $companyErr;?></span>
                    </div>


                    <div class="form-group">
                      <label class="col-sm-2 control-label">Year</label>

                      <div class="col-sm-7">
                        <input type="text" class="form-control" placeholder="Please type a release year..." name="year">
                        <span class="help-block">EX. 1995</span>
                      </div>
                      <span class="error">* <?php echo $yearErr;?></span>
                    </div>


                    <div class="form-group">
                      <label class="col-sm-2 control-label">MPAA Rating</label>
                      <div class="col-sm-7">
                        <select   class="form-control" name="rating">
                          <option value="G">G</option>
                          <option value="NC-17">NC-17</option>
                          <option value="PG">PG</option>
                          <option value="PG-13">PG-13</option>
                          <option value="R">R</option>
                          <option value="surrendere">surrendere</option>
                        </select>
                      </div>
                      <span class="error">* <?php echo $ratingErr;?></span>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-2 control-label">Genre</label>
                      <div class="col-sm-7">
                        <select multiple class="form-control" name="genre[]">
                          <option value="Action">Action</option>
                          <option value="Adult">Adult</option>
                          <option value="Adventure">Adventure</option>
                          <option value="Animation">Animation</option>
                          <option value="Comedy">Comedy</option>
                          <option value="Crime">Crime</option>
                          <option value="Documentary">Documentary</option>
                          <option value="Drama">Drama</option>
                          <option value="Family">Family</option>
                          <option value="Fantasy">Fantasy</option>
                          <option value="Horror">Horror</option>
                          <option value="Musical">Musical</option>
                          <option value="Mystery">Mystery</option>
                          <option value="Romance">Romance</option>
                          <option value="Sci-Fi">Sci-Fi</option>
                          <option value="Short">Short</option>
                          <option value="Thriller">Thriller</option>
                          <option value="War">War</option>
                          <option value="Western">Western</option>
                        </select>
                        <span class="help-block">Hold ctl/cmd to select multiple options.</span>
                      </div>
                      <span class="error">* <?php echo $genreErr;?></span>

                    </div>

                    <div class="form-group">
                      <div class="col-sm-7 col-sm-offset-5">
                        <button type="submit" class="btn btn-default">Add!</button>
                      </div>
                    </div>



                    <?php
                    
                    if(!$incomplete) {
                      printf('<h3><i class="fa fa-angle-right"></i> Output:</h3>');
                      printf('<div class="alert alert-success">');
                      $db = new mysqli('localhost', 'cs143', '', 'CS143');
                      if($db->connect_errno > 0)
                        die('Unable to connect to database [' . $db->connect_error . ']');
                      $query = "SELECT id FROM MaxMovieID";
                      if(!$result = $db->query($query))
                        echo "Invalid query.";
                      else{
                        // Extract max id from MaxPersonID table 
                        $row=$result->fetch_row();
                        $result->free();

                        // Create a new ID
                        $newID = $row[0]+1;

                        // Insert new tuple to corresponding table
                        $query = "INSERT INTO Movie"." VALUES (".(string)$newID.",'".$_GET["title"]."',".$_GET["year"].",'".$_GET["rating"]."','".$_GET["company"]."');";

                        //echo "$query<br>";

                        if(!$result = $db->query($query))
                          echo "Invalid query.";
                        else {
                          $query = "INSERT INTO MovieGenre"." VALUES (".(string)$newID.",'".$genre."');";
                          //echo "$query<br>";
                          if(!$result = $db->query($query))
                            echo "Invalid query.";

                          $query = "UPDATE MaxMovieID SET id = ".(string)$newID;
                          //echo "$query<br>";
                          if(!$result = $db->query($query))
                            echo "Invalid query.";
                          else
                            echo "Success!<br>";
                        }
                        $result->free();
                        $db->close();
                      }
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
