<?
require_once getenv("PHPLIB") . "keystore.php";
mysql_connect(keystore("mysql", "db"), keystore("mysql", "user"), keystore("mysql", "pass"));
mysql_select_db("terrance_labs");
session_start();
if (array_key_exists("error", $_GET) && isset($_GET["error"])) {
    template_header("Error: VoTE");
    switch ($_GET["error"]) {
        case "ajax":
            template_post("AJAX error...", "Your browser doesn't appear to support XMLHTTP requests.  In order to use VoTE, you need a better browser (such as Google Chrome, Mozilla Firefox or Apple Safari).  When you've got one, go <a href=\"./\">here</a> to try it again.");
            break;
        case "js":
            template_post("JavaScript error...", "You have JavaScript disabled in your browser.  In order to use VoTE, you need to enable it.  You can usually do this by going to your browser's options screen.  When you've done so, click <a href=\"./\">here</a> to try it again.");
            break;
        default:
            template_post("Unknown error...", "Some random error has occured...  what have you done?  Are you trying to access this error page directly?  Go back <a href=\"./\">here</a> now.");
            break;
    }
    template_about("VoTE...  what?", "The Vote-orientated Tally Engine (VoTE) is a system that asks a variety of random and, quite often, pointless questions.  But you'll love it.  No, really.");
    template_footer();
    return;
}
// check post data (used by AJAX functions to dynamically get data)
if (array_key_exists("action", $_POST)) {
    switch ($_POST["action"]) {
        case "get_question":
            $data = mysql_query("SELECT * FROM `vote__questions`;");
            $last = (int)$_POST["last"];
            $i = $last;
            while ($i < mysql_num_rows($data)) {
                $ok = true;
                $id = mysql_result($data, $i, "id");
                if (array_key_exists("login", $_SESSION) && $_SESSION["login"]["id"] > 0) {
                    $data2 = mysql_query("SELECT * FROM `vote__votes` WHERE `id` = ".$id." AND `user` = ".$_SESSION["login"]["id"].";");
                    if (mysql_num_rows($data2) > 0) {
                        $ok = false;
                    }
                }
                $data2 = mysql_query("SELECT * FROM `vote__votes` WHERE `id` = ".$id." AND `ip` = \"".$_SERVER["REMOTE_ADDR"]."\";");
                if (mysql_num_rows($data2) > 0) {
                    $ok = false;
                }
                if ($ok) {
                    $question = mysql_result($data, $i, "question");
                    $answers = mysql_result($data, $i, "answers");
                    print($id."\n".$question."\n".$answers);
                    return;
                } else {
                    $i++;
                }
            }
            $i = 0;
            while ($i < $last) {
                $ok = true;
                $id = mysql_result($data, $i, "id");
                $data = mysql_query("SELECT * FROM `vote__votes` WHERE `id` = ".$id." AND `ip` = \"".$_SERVER["REMOTE_ADDR"]."\";");
                if (mysql_num_rows($data) > 0) {
                    $ok = false;
                }
                if ($ok) {
                    $question = mysql_result($data, $i, "question");
                    $answers = mysql_result($data, $i, "answers");
                    print($id."\n".$question."\n".$answers);
                    return;
                } else {
                    $i++;
                }
            }
            return;
        case "make_vote":
            $data = mysql_query("SELECT * FROM `vote__votes` WHERE `id` = ".$_POST["id"]." AND `ip` = \"".$_SERVER["REMOTE_ADDR"]."\";");
            if (mysql_num_rows($data) > 0) {
                print("You've already voted on this question...  so you shouldn't have really been asked it.  What did you do?  Have you got this page open more than once?  In which case, please don't.  :/");
                return;
            }
            mysql_query("INSERT INTO `vote__votes` VALUES(".$_POST["id"].", 0, \"".$_SERVER["REMOTE_ADDR"]."\", \"".$_POST["answer"]."\");");
            return;
        case "get_result":
            $data = mysql_query("SELECT * FROM `vote__votes` WHERE `id` = ".$_POST["id"].";");
            $i = 0;
            $count = array("a" => 0, "b" => 0, "c" => 0);
            while ($i < mysql_num_rows($data)) {
                $count[mysql_result($data, $i, "answer")] += 1;
                $i++;
            }
            print implode("|", $count);
            return;
        case "suggest":
            mysql_query("INSERT INTO `vote__suggestions` VALUES(\"".$_SERVER["REMOTE_ADDR"]."\", \"".$_POST["question"]."\", \"".$_POST["answers"]."\");");
            print("Your suggestion, \"".$_POST["question"]."\", has been noted!  Keep voting - you might see your question soon...");
            return;
    }
}
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
        #loading {
            border: 1px dashed white;
            display: none;
            font-size: 1.4em;
            font-style: italic;
            margin-top: 25px;
            padding: 18px 10px;
            width: 100%;
        }
        #question {
            border: 3px double white;
            display: none;
            font-size: 2em;
            font-weight: bold;
            margin-top: 25px;
            padding: 10px;
            width: 100%;
        }
        #answers {
            display: none;
            padding-top: 25px;
        }
        .answer {
            border: 1px dashed white;
            font-size: 1.6em;
            margin-top: 5px;
            padding: 10px;
            text-align: left;
            width: 100%;
        }
        .answer:hover {
            border: 1px solid white;
            cursor: pointer;
            font-weight: bold;
        }
        #results {
            display: none;
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
        #next {
            border: 1px dashed white;
            display: none;
            margin-top: 25px;
            padding: 10px;
            width: 200px;
        }
        #next:hover {
            border: 1px solid white;
            cursor: pointer;
            font-weight: bold;
        }
        #footer {
            border-top: 3px double white;
            bottom: 0;
            margin: 0;
            padding: 10px 0;
            position: fixed;
            text-align: center;
            width: 100%;
        }
        </style>
        <noscript>
            <meta http-equiv="refresh" content="0; URL=?error=js" />
        </noscript>
        <script type="text/javascript">
        function $(id) { // shorthand to get an element
            return document.getElementById(id);
        }
        var xmlhttp = null;
        var complete = false;
        try {
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (error) {
            try {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (error) {
                try {
                    xmlhttp = new XMLHttpRequest();
                }
                catch (error) {
                    window.location = "?error=ajax";
                }
            }
        }
        if (document.images) {
            var colours = ["green", "blue", "purple", "red"];
            for (var i in colours) {
                var img = new Image(8, 8);
                img.src = "resources/bg_" + colours[i] + ".png";
            }
        }
        function ajax_connect(url, method, vars, callback) {
            try {
                complete = false;
                method = method.toUpperCase();
                if (method === "GET") {
                    xmlhttp.open(method, url + "?" + vars, true);
                } else {
                    xmlhttp.open(method, url, true);
                    xmlhttp.setRequestHeader("Method", "POST " + url + " HTTP/1.1");
                    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                }
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState === 4 && !complete) {
                        complete = true;
                        if (callback) {
                            callback(xmlhttp.responseText);
                        }
                    }
                };
                xmlhttp.send(vars);
                return true;
            } catch (error) {
                return false;
            }
        }
        function suggest(response) {
            if (response) {
                alert(response);
            } else if (confirm("Have an idea for an interesting new question?  Why not suggest it to us!  Go on, you know you want to.  Just go ahead and make up something completely unique and interesting.  If you're lucky, you'll see it on here after a while.  :P\n\nYou want to suggest something?")) {
                var question = prompt("Good!  Type it below and let us know.", "");
                if (question !== "" && question !== null && question !== undefined) {
                    var answers = prompt("Any suggestions for the type of answers you expect.  Tell us that as well.", "");
                    if (answers === null || answers === undefined) {
                        answers = "";
                    }
                    if (confirm("Wait...  a quick note before you suggest.\n\nWe keep a record of who suggests what - both user ID's and IP addresses.  Do not suggest anything of questionable content - we will take action if you do.\n\nQuestion: " + question + (answers === "" ? "" : "\nAnswers: " + answers) + "\n\nSubmit this suggestion?")) {
                        ajax_connect("./", "POST", "action=suggest&question=" + escape(question) + "&answers=" + escape(answers), suggest);
                    }
                }
            }
        }
        function get_question() {
            bg_colour("black");
            toggle_loading(true, "Loading questions...");
            var last = $("id").innerHTML;
            if (last === "") {
                last = "0";
            }
            ajax_connect("./", "POST", "action=get_question&last=" + last, get_question_r);
        }
        function get_question_r(response) {
            bg_colour();
            if (response === "") {
                toggle_loading(true, "No more questions are currently available (click <u onclick=\"get_question();\" style=\"cursor: pointer;\">here</u> to refresh).  :(");
            } else {
                response = response.split("\n");
                var id = response[0];
                var question = response[1];
                var answers = response[2].split("|");
                toggle_loading(false, true);
                $("id").innerHTML = id;
                $("question").innerHTML = question;
                $("answer1").innerHTML = answers[0];
                $("answer2").innerHTML = answers[1];
                $("answer3").innerHTML = answers[2];
                $("bar1").innerHTML = answers[0];
                $("bar2").innerHTML = answers[1];
                $("bar3").innerHTML = answers[2];
            }
        }
        function make_vote(answer) {
            toggle_loading(true, "Submitting vote...");
            ajax_connect("./", "POST", "action=make_vote&id=" + $("id").innerHTML + "&answer=" + answer, make_vote_r);
        }
        function make_vote_r(response) {
            if (response === "") {
                toggle_loading(true, "Fetching results...");
                ajax_connect("./", "POST", "action=get_result&id=" + $("id").innerHTML, get_result_r);
            } else {
                alert(response);
                toggle_loading(true, "Loading questions...");
                ajax_connect("./", "POST", "action=get_question", get_question_r);
            }
        }
        function get_result_r(response) {
            bg_colour();
            response = response.split("|");
            a = parseInt(response[0]);
            b = parseInt(response[1]);
            c = parseInt(response[2]);
            total = a + b + c;
            a = Math.floor((a / total) * 100);
            b = Math.floor((b / total) * 100);
            c = Math.floor((c / total) * 100);
            $("bar1").innerHTML += " (" + a + "%)";
            $("bar1").setAttribute("class", "zero");
            if (a > 0) {
                $("bar1").setAttribute("class", "bar");
                $("bar1").style.width = a + "%";
            }
            $("bar2").innerHTML += " (" + b + "%)";
            $("bar2").setAttribute("class", "zero");
            if (b > 0) {
                $("bar2").setAttribute("class", "bar");
                $("bar2").style.width = b + "%";
            }
            $("bar3").innerHTML += " (" + c + "%)";
            $("bar3").setAttribute("class", "zero");
            if (c > 0) {
                $("bar3").setAttribute("class", "bar");
                $("bar3").style.width = c + "%";
            }
            toggle_loading(false, false);
        }
        function toggle_loading(hide, param) {
            if (hide) {
                $("loading").style.display = "block";
                $("question").style.display = "none";
                $("answers").style.display = "none";
                $("results").style.display = "none";
                $("next").style.display = "none";
                if (param) {
                    $("loading").innerHTML = param;
                }
            } else {
                $("loading").style.display = "none";
                $("question").style.display = "block";
                if (param) {
                    $("answers").style.display = "block";
                    $("results").style.display = "none";
                } else {
                    $("answers").style.display = "none";
                    $("results").style.display = "block";
                }
                $("next").style.display = "block";
            }
        }
        function bg_colour(colour) {
            if (!colour) {
                var colours = ["green", "blue", "purple", "red"];
                colour = colours[Math.floor(Math.random() * 4)];
            }
            document.body.style.backgroundImage = "url(resources/bg_" + colour + ".png)";
        }
        window.onresize = function(event) {
            $("content").style.width = window.innerWidth - 75;
        }
        window.onload = function(event) {
            window.onresize();
            get_question();
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
                <span onclick="suggest();" style="cursor: hand;" title="Give us ideas for questions...">Suggest</span>
                <a href="results.php" accesskey="r" title="See what other people voted for... (Alt+R)">Results</a>
            </div>
        </div>
        <div id="content" align="center">
            <div id="loading"></div>
            <div id="question"></div>
            <div id="id" style="display: none;"></div>
            <div id="answers">
                <div id="answer1" onclick="make_vote('a');" class="answer"></div>
                <div id="answer2" onclick="make_vote('b');" class="answer"></div>
                <div id="answer3" onclick="make_vote('c');" class="answer"></div>
            </div>
            <div id="results">
                <div id="result1" class="result">
                    <div id="bar1" class="bar"></div>
                </div>
                <div id="result2" class="result">
                    <div id="bar2" class="bar"></div>
                </div>
                <div id="result3" class="result">
                    <div id="bar3" class="bar"></div>
                </div>
            </div>
            <div id="next" onclick="get_question();">Next question, please...</div>
        </div>
        <div id="footer">Back to <a href="../">Terrance Laboratories</a>.</div>
    </body>
</html>
