<html>
<head>
    <title>Web interface for Neato XV-25 - O� suis-je ?</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script language="javascript" type="text/javascript" src="cookies.js"></script>
    <script language="javascript" type="text/javascript">
     
     window.onload = function() {
         var canvas = document.getElementById("xv");
         var context = canvas.getContext("2d");

         context.strokeStyle = '#0099ff';

         context.beginPath();
         for (x = 50; x < 500; x += 50) {
             context.moveTo(x, 0);
             context.lineTo(x, 500);
         }
         for (y = 50; y < 500; y += 50) {
             context.moveTo(0, y);
             context.lineTo(500, y);
         }
         context.stroke();

         var imageObj = new Image();
         imageObj.onload = function() {
             context.drawImage(imageObj, 225, 225);
         };
         imageObj.src = 'xv25-top.png';

         context.strokeStyle = '#0000ff';
         for (x = 110; x < 390; x += 10) {
             deltaX = Math.floor(Math.random()*11) - 5;
             deltaY = Math.floor(Math.random()*11) - 5;
             context.fillRect(x+deltaX,110+deltaY,2,2);
             context.fillRect(110+deltaX,x+deltaY,2,2);
             context.fillRect(390-deltaX,x+deltaY,2,2);
             context.fillRect(x+deltaX,390-deltaY,2,2);
         }
     }
    </script>
</head>
<body>
<h1>Web interface for Neato XV-25</h1>

<div id="topnav">
<ul>
    <li><a href="webApiForm.php">Commandes</a></li>
    <li><a href="ou-suis-je.php">O� suis-je ?</a></li>
    <li><a href="configuration.php">Configuration</a></li>
</ul>
</div>

<div class="form">
    <h2>O� suis-je ?</h2>
    <div class="centered">
        <canvas class="xv" id="xv" width="500" height="500">
            This text is displayed if your browser does not support HTML5 Canvas.
        </canvas>
    </div>
<?php
    $history = "";
if (isset($_POST['history'])) {
    $history = htmlspecialchars($_POST['history']);
}
if (isset($_POST['cmd']))
    $history = htmlspecialchars($_POST['cmd']) . "," . $history;
echo "        <input type=\"hidden\" id=\"history\" name=\"history\" value=\"" . $history . "\" />\n";
?>
    </form>
</div>

<?php
error_reporting(E_ALL);

if (isset($_POST['cmd'])) {
    echo "<div class=\"form\" id=\"connectionLog\"style=\"cursor: pointer;\" onclick=\"hideConnectionLog()\" >\n";
    echo "    <h2>Connection Log</h2>\n";
    echo "    <div class=\"square\">\n";

    $service_port = $port;
    $address = gethostbyname($ip);
    $error = 0;

    if (!($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
        $error = 1;
    echo "        <p class=\"". ((0 == $error) ? "ok" : "ko") . "\">\n";
    echo "        <b>Cr�ation du socket</b><br/>\n";
    if (1 == $error)
        echo "            --> socket_create() a �chou� (erreur:" . socket_strerror(socket_last_error()) . ")\n";
    else
        echo "            --> OK.\n";
    echo "        </p>\n";

    if (0 == $error) {
        if (!(socket_connect($socket, $address, $service_port)))
            $error = 1;
        echo "        <p class=\"". ((0 == $error) ? "ok" : "ko") . "\">\n";
        echo "        <b>Essai de connexion � '" . $address . "' sur le port '" . $service_port . "'</b><br/>\n";
        if (1 == $error)
            echo "            --> socket_connect() a �chou� (erreur:" . socket_strerror(socket_last_error($socket)) . ")\n";
        else
            echo "            --> OK.\n";
        echo "        </p>\n";
    }

    if (0 == $error) {
        $in = htmlspecialchars($_POST['cmd']) . "\n";
        if (socket_write($socket ,$in ,strlen($in)) === false)
            $error = 1;
        echo "        <p class=\"". ((0 == $error) ? "ok" : "ko") . "\">\n";
        echo "        <b>Envoi de la requ�te '" . htmlspecialchars($_POST['cmd']) . "\\n'</b><br/>\n";
        if (1 == $error)
            echo "            --> socket_write() a �chou� (erreur:" . socket_strerror(socket_last_error($socket)) . ")\n";
        else
            echo "            --> OK.\n";
        echo "        </p>\n";
    }

    if (0 == $error) {
        $out = "";
        $response = "";
        $receivedDone = 0;
        while (0 == $receivedDone && 0 == $error) {
            if (false === socket_recv($socket, $out, 1024, MSG_WAITALL)) {
                $error = 1;
            } else {
                $response .= $out;
                if (false !== ($endOfResponse = strpos($response, ",EndOfResponse"))) {
                    $receivedDone = 1;
                    $response = substr($response, 0, $endOfResponse);
                    $response = str_replace("~", "<br/>", $response);
                }
            }
        }
        echo "        <p class=\"". ((0 == $error) ? "ok" : "ko") . "\">\n";
        echo "        <b>Lecture de la r�ponse</b><br/>\n";
        if (1 == $error)
                echo "            --> socket_read() a �chou� (erreur:" . socket_strerror(socket_last_error($socket)) . ")\n";
        else
            echo "--> " . $response;
        echo "        </p>\n";
    }

    echo "        <p class=\"ok\">\n";
    echo "        <b>Fermeture du socket</b>\n";
    socket_close($socket);
    echo "        </p>\n";
    echo "    </div>\n";
    echo "</div>\n\n";
}
?>

</body>
</html>