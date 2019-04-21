<html>
<head><title>CS143 Project 1B Demo</title></head>
<body>
	<h2>Web Query Interface</h2>
	<p>Type an SQL query in the following box: </p>

	Example: <tt>SELECT * FROM Actor WHERE id=10;</tt>
	<p>
		<form action = "<?php echo ($_SERVER["PHP_SELF"]);?>" method="GET">
			<textarea name="query" cols="60" rows="8"></textarea>
			<br><input type="submit" value="Submit" />
		</form>

	</p>
	<p><small>Note: tables and fields are case sensitive. All tables in Project 1B are availale.</small>
	</p>

	<?php
	if ($_GET["query"]) {
		echo "<h3>Result from Mysql:</h3>";
		
		$query = $_GET["query"];
		
		// Connect to the target database
		$db = new mysqli('localhost', 'cs143', '', 'CS143');
		if($db->connect_errno > 0)
			die('Unable to connect to database [' . $db->connect_error . ']');
		
	    // Interprete the query
		if(!$result = $db->query($query)) {
			//die ('There was an error running the query [' . $db->error . ']');
			echo "Invalid query.";
		}
		else {
		?>
		<table border = '1' cellpadding = '2' cellspacing = '1'>
		<?php
			// Print names of all cols
			$field_cnt = $result->field_count;
			for ($i = 0; $i < $field_cnt; $i++) {
				$finfo = $result->fetch_field_direct($i);
				printf ("<th> %s </th>", $finfo->name);
			}
			print "<br \>";

			// Print each row in each table
			while ($row = $result->fetch_assoc()) {
				print "<tr align = center>";
				for($i = 0; $i < $field_cnt; $i++) {
					$finfo = $result->fetch_field_direct($i);
					$col_name = $finfo->name;
					$col_content = $row[$col_name] == null ? 'N/A' : $row[$col_name];
					print "<td> $col_content </td>";
				}
				print "</tr>";
			}
			$result->free();
		}
		$db->close();
	}
	?>


</body>
</html>