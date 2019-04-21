<!DOCTYPE html>
<html>
<body>

	<h1>Calculator</h1>
	<p>(Ver 1.0 09/30/2016 by Zhengxu Xia)
		<br>
		Type an expression in the following box (e.g., 10.5+20*3/25).
	</p>
	
	<form action="<?php echo ($_SERVER["PHP_SELF"]);?>" method="get">
		<input type="text" name="expression">
		<input type="submit" value="Calculate">
		<br>
	</form>


	<ul>
		<li>Only numbers and +,-,* and / operators are allowed in the expression.</li>
		<li>The evaluation follows the standard operator precedence.</li>
		<li>The calculator does not support parentheses.</li>
		<li>The calculator handles invalid input"gracefully". It does not output PHP error messages.</li>
	</ul>


	Here are some(but not limit to) reasonable test cases:
	<ol>
		<li> A basic arithmetic operation:  3+4*5=23 </li>
		<li> An expression with floating point or negative sign : -3.2+2*4-1/3 = 4.46666666667, 3*-2.1*2 = -12.6 </li>
		<li> Some typos inside operation (e.g. alphabetic letter): Invalid input expression 2d4+1 </li>
	</ol>

	<?php
	if ($_GET["expression"]) {
		echo "<h2>Result</h2>";
		$pattern = "/^(\s*\-?(?!0+\d)\d+(\.\d+)?\s*[\+\-\*\/]?)*$/";
		$div_zero = "/\/0(\.0+)?/";
		$replace = "- -";
		$search = "--";
		if (preg_match($pattern, $_GET["expression"], $matches)) {
			if(preg_match($div_zero, $_GET["expression"]))
				echo "Division by zero.";
			else{
				$expre = str_replace($search, $replace, $matches[0]);
				eval("\$ans = $expre;");
				echo $expre." = ".$ans;
			}
		}
		else
			echo "Invalid Expression!";
	}
	?>



</body>
</html>
