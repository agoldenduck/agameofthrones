<?php

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form.

  include 'connect.php';

	if (!empty($_POST['yourhouse']) && is_numeric($_POST['yourhouse'])) {
    $problem = FALSE;
		$yourhouse = filter_input (INPUT_POST, 'yourhouse', FILTER_VALIDATE_INT);

		if ($yourhouse < 7) {
			$remindmeall = mysql_query ("SELECT * FROM houses WHERE housekey = $yourhouse", $dbc);
		}

		elseif ($yourhouse < 13) {
			$remindmeall = mysql_query ("SELECT * FROM houses2 WHERE housekey = $yourhouse", $dbc);
		}

    else {
      include 'back.php';
      print '<p style="color: red; text-align: center;">Please select a valid code.</p>';
      $problem = TRUE;
    }

    if (!$problem) {
  		$remindme = mysql_fetch_array ($remindmeall);
      include 'back.php';
  		echo '<div id="container">';
  		echo '<h1>Welcome back ' . $remindme[player] . ' of House ' . $remindme[house] . ', have you forgotten yourself?</h1>';
  		echo '<blockquote>' . $remindme[motto] . '</blockquote>';
  		echo '<p>' . $remindme[bio] . '</p>';
  		echo '<img src = ' . $remindme[profile] . ' />';
  		echo '</div>';
    }
	}

	else {
		// Validate the form data:
		$problem = FALSE;
		if (!empty($_POST['player']) && $_POST['yourevent'] != '0') {
			$player = mysql_real_escape_string (trim (strip_tags ($_POST['player'])), $dbc);
		}
		else {
      include 'back.php';
			print '<p style="color: red; text-align: center;">Please enter your name and select which event you will attend. Otherwise please enter your code to review your House.</p>';
			$problem = TRUE;
		}

		if (!$problem) {

			// Define the query:
			$event = $_POST['yourevent'];
			if ($event == "1") {
				$sql = "SELECT house FROM houses WHERE player IS NULL";
				$table = "houses";
			}
			else {
				$sql = "SELECT house FROM houses2 WHERE player IS NULL";
				$table = "houses2";
			}

			$houses = mysql_query ($sql, $dbc);
			$n = mysql_num_rows ($houses);
			$random = rand (0, ($n - 1));
			$column = array();
			while ($eachrow = mysql_fetch_array($houses)) {
				$column[] = $eachrow['house'];
				echo '<p id="hidden">' . $eachrow['house'] . '</p>';
			}
			$house = $column[$random];
			if (!$house) {
        include 'back.php';
				print '<div id="container">
					<p> We\'re sorry, this game is full, see if you can join the other game</p>
				</div>';
			}

			else {
				$query = "UPDATE $table SET player = '$player' where house = '$house';";

				// Execute the query:
				if (@mysql_query($query, $dbc)) {
					$showmeall = mysql_query ("SELECT * FROM $table WHERE house = '$house'", $dbc);
					$showme = mysql_fetch_array ($showmeall);
          include 'back.php';
					echo '<div id="container">';
					echo '<h1>Welcome ' . $player . ' of House ' . $house . ', you have accepted the challenge!</h1>';
					echo '<p id="relative">Remember your code to visit here again: <strong>' . $showme[housekey] . '</strong></p>';
					echo '<blockquote>' . $showme[motto] . '</blockquote>';
					echo '<p>' . $showme[bio] . '</p>';
					echo '<img src = ' . $showme[profile] . ' />';
					echo '</div>';
				}

				else {
          include 'back.php';
					print '<p style="color: red;">Could not add the entry because:<br />' . mysql_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
				}
			}
		} // No problem!
	}
	mysql_close($dbc); // Close the connection.

} // End of form submission IF.

elseif (strpos($_SERVER['REQUEST_URI'], 'reset=true') !== false) {

  include 'connect.php';

  $query1 = "UPDATE houses SET player = NULL;";
  $query2 = "UPDATE houses2 SET player = NULL;";

  if (@mysql_query($query1, $dbc) && @mysql_query($query2, $dbc)) {
    include 'back.php';
    print '<div id="container">
      <p>Successfully reset both games. Please go back and join a game.</p>
    </div>';
  }
  else {
    include 'back.php';
    print '<p style="color: red;">Could not add the entry because:<br />' . mysql_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
  }
  mysql_close($dbc); // Close the connection.
}

else {
    print '
<div id="container">
  <h1>DISCOVER YOUR HOUSE</h1>
  <form action="index.php" method="post">
    <label for="yourname">Enter your name</label><input type="text" name="player" id="yourname" /><br />
    <label for="yourevent">Choose your battle</label><select name="yourevent" id="yourevent">
  		<option value="0" selected disabled>Please select</option>
  		<option value="1">11am 18 January 2014</option>
  		<option value="2">11am 19 January 2014</option>
    </select><br />
    <input class="button" type="submit" value="Enter" />
  </form>
  <form action="index.php" method="get">
    <input class="hidden" type="text" name="reset" value="true" />
    <p>All full? Reset the tables.</p>
    <input class="button" type="submit" value="Reset" />
  </form>
	<h2>RETRIEVE YOUR HOUSE</h2>
	<form action="index.php" method="post">
		<label for="yourhouse">Enter your code</label><input type="text" name="yourhouse" id="yourhouse" maxlength="2" /><br />
		<input class="button" type="submit" value="Return" />
	</form>
</div>';
}
?>

</body>
</html>
