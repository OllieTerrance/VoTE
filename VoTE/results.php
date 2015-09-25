<?
require_once getenv("PHPLIB") . "keystore.php";
mysql_connect(keystore("mysql", "db"), keystore("mysql", "user"), keystore("mysql", "pass"));
mysql_select_db("terrance_labs");
?><html>
    <head>
        <title>VoTE: Vote-oriented Tally Engine</title>
        <style type="text/css">
        body {
            background-image: url(resources/bg_black.png);
            color: white;
            font-family: "Segoe UI", "Calibri", "Trebuchet MS", Tahoma, Arial, Helvetica, sans-serif;
            margin: 0;
            width: 100%;
        }
        img {
            padding: 10px;
        }
        a {
            color: white;
        }
        #header {
            border-bottom: 5px double white;
            margin: 0;
            width: 100%;
        }
        #links {
            padding: 10px;
            position: absolute;
            right: 0;
            top: 0;
        }
        #links a, #links span {
            margin-left: 5px;
            text-decoration: none;
        }
        #links a:hover, #links span:hover {
            border-bottom: 1px solid white;
            border-top: 1px solid white;
        }
        #content {
            margin-left: 25px;
        }
        #question {
            border: 3px double white;
            font-size: 2em;
            font-weight: bold;
            margin-top: 25px;
            padding: 10px;
            width: 100%;
        }
        #results {
            padding-top: 25px;
        }
        .result {
            border: 1px solid white;
            margin-top: 5px;
            padding: 10px;
            text-align: left;
            width: 100%;
        }
        .result .bar {
            background-image: url(resources/bar.png);
            color: black;
            font-size: 1.6em;
            padding: 0;
            text-align: left;
            width: 0%;
        }
        .result .zero {
            font-size: 1.6em;
            padding: 0;
            text-align: left;
            width: 100%;
        }
        #footer {
            border-top: 3px double white;
            margin-top: 25px;
            padding: 10px 0;
            text-align: center;
            width: 100%;
        }
        </style>
        <script type="text/javascript">
        window.onresize = function(event) {
            document.getElementById("content").style.width = window.innerWidth - 90;
        }
        window.onload = function(event) {
            window.onresize();
        }
        </script>
    </head>
    <body>
        <div id="header">
            <a href="./" accesskey="h" title="Return to the home page... (Alt+H)">
                <img src="resources/main.png" />
            </a>
            <div id="links">
                <span onclick="alert('The Vote-oriented Tally Engine (shortened to \'VoTE\') is an interesting new (well, not really) concept.  It will ask you a variety of questions, designed to test what kind of person you are.  There are no right or wrong answers here (although some questions clearly have right and wrong answers) - just vote for what you like.  :)');" style="cursor: hand;" title="Find out more about VoTE...">About</span>
                <a href="" accesskey="r" title="See what other people voted for... (Alt+R)">Results</a>
            </div>
        </div>
        <div id="content" align="center">
<?
$data = mysql_query("SELECT * FROM `vote__questions`;");
$i = 0;
while ($i < mysql_num_rows($data))
{
    $id = mysql_result($data, $i, "id");
    $question = mysql_result($data, $i, "question");
    $answers = explode("|", mysql_result($data, $i, "answers"));
    $data2 = mysql_query("SELECT * FROM `vote__votes` WHERE `id` = ".$id.";");
    $count = array("a" => 0, "b" => 0, "c" => 0);
    $i2 = 0;
    while ($i2 < mysql_num_rows($data2)) {
        $count[mysql_result($data2, $i2, "answer")] += 1;
        $i2++;
    }
    $total = $count["a"] + $count["b"] + $count["c"];
    $a = floor(($count["a"] / $total) * 100);
    $b = floor(($count["b"] / $total) * 100);
    $c = floor(($count["c"] / $total) * 100);
?>
            <div id="question"><? print($question); ?></div>
            <div id="results">
                <div id="result1" class="result">
<?
    if ($a > 0) {
?>
                    <div class="bar" style="width: <? print $a; ?>%;"><? print($answers[0]); ?> (<? print $a; ?>%)</div>
<?
    } else {
?>
                    <div class="zero"><? print($answers[0]); ?> (0%)</div>
<?
    }
?>
                </div>
                <div id="result2" class="result">
<?
    if ($b > 0) {
?>
                    <div class="bar" style="width: <? print $b; ?>%;"><? print($answers[1]); ?> (<? print $b; ?>%)</div>
<?
    } else {
?>
                    <div class="zero"><? print($answers[1]); ?> (0%)</div>
<?
    }
?>
                </div>
                <div id="result3" class="result">
<?
    if ($c > 0) {
?>
                    <div class="bar" style="width: <? print $c; ?>%;"><? print($answers[2]); ?> (<? print $c; ?>%)</div>
<?
    } else {
?>
                    <div class="zero"><? print($answers[2]); ?> (0%)</div>
<?
    }
?>
                </div>
            </div>
<?
    $i++;
}
?>
        </div>
        <div id="footer">Back to <a href="../">Terrance Laboratories</a>.</div>
    </body>
</html>
