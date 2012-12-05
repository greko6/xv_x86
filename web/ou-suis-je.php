<html>
<head>
    <title>Web interface for Neato XV-25 - O� suis-je ?</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script language="javascript" type="text/javascript" src="cookies.js"></script>
    <script language="javascript" type="text/javascript">
     var canvas_size = 500;
     var scale_m_per_square = 0.5;

     function drawSquares(context) {
         context.beginPath();
         context.strokeStyle = '#0099ff';
         for (x = 50; x < canvas_size; x += 50) {
             context.moveTo(x, 0);
             context.lineTo(x, canvas_size);
         }
         for (y = 50; y < canvas_size; y += 50) {
             context.moveTo(0, y);
             context.lineTo(canvas_size, y);
         }
         context.stroke();

         context.beginPath();
         context.strokeStyle = '#ff1100';
         context.moveTo(canvas_size/2 - 25, canvas_size/2);
         context.lineTo(canvas_size/2 + 25, canvas_size/2);
         context.moveTo(canvas_size/2, canvas_size/2 - 25);
         context.lineTo(canvas_size/2, canvas_size/2 + 25);
         context.stroke();
     }

     var zeroOffset = canvas_size / 2;
     var xv_x = 0, xv_y = 0;
     var xv_t = 0;
     var xvImage;
     function drawXV(context) {
         context.save();
         context.translate(xv_x+zeroOffset, xv_y+zeroOffset);
         context.rotate(-xv_t);
         context.translate(-15, -15);
         context.drawImage(xvImage, 0, 0, 30, 30);
         context.restore();
     }

     function drawScan(context) {
         context.strokeStyle = '#0000ff';
         for (x = 110; x < (canvas_size-110); x += 10) {
             deltaX = Math.floor(Math.random()*11) - 5;
             deltaY = Math.floor(Math.random()*11) - 5;
             context.fillRect(x+deltaX,110+deltaY,2,2);
             context.fillRect(110+deltaX,x+deltaY,2,2);
             context.fillRect(canvas_size-110-deltaX,x+deltaY,2,2);
             context.fillRect(x+deltaX,canvas_size-110-deltaY,2,2);
         }
     }

     function drawScale(context) {
         context.font = 'italic 12pt Calibri';
         context.fillStyle = '#606060';
         context.fillText('�chelle', 30, 30); 
         text = '' + scale_m_per_square + ' m / graduation'
         context.fillText(text, 365, 30);

         context.beginPath();
         context.rect(100, 20, 50/scale_m_per_square, 10);
         context.strokeStyle = '#CC0000';
         context.fillStyle = '#FF8080';
         context.lineWidth = 1;
         context.fill();
         context.stroke();   

         context.beginPath();
         context.fillStyle = '#CC0000';
         context.fillText('1m', 100+25/scale_m_per_square-10, 15); 
         context.stroke();

         context.beginPath();
         context.strokeStyle = '#0099ff';        
         context.moveTo(100, 25);
         context.lineTo(350, 25);
         for (x = 100; x < canvas_size-100; x += 50) {
             context.moveTo(x, 10);
             context.lineTo(x, 40);
         }
         context.stroke();       
     }

     function refreshDrawing() {
         var canvas = document.getElementById("xv");
         var context = canvas.getContext("2d");

         document.getElementById("xPos").innerHTML = Math.round(xv_x);
         document.getElementById("yPos").innerHTML = Math.round(-xv_y);
         document.getElementById("thetaPos").innerHTML = ((Math.round(xv_t*100))/100);

         context.clearRect(0, 0, canvas.width, canvas.height);
         drawSquares(context);
         drawXV(context);
         drawScan(context);
         
         xv_t += Math.PI/30.0;
         if (xv_t > 2*Math.PI)
             xv_t -= 2*Math.PI;
         xv_x += 6 * Math.cos(xv_t);
         xv_y -= 6 * Math.sin(xv_t);

         canvas = document.getElementById("xv-scale");
         context = canvas.getContext("2d");
         context.clearRect(0, 0, 500, 50);
         drawScale(context);
     }

     var periodicFunction;
     function updatePeriod() {
         clearInterval(periodicFunction);
         periodicFunction = setInterval(refreshDrawing, (document.getElementById("periode").value*1000));
     }
     
     window.onload = function() {
         xvImage = new Image();
         xvImage.src = 'xv25-top.png';

         refreshDrawing();
         periodicFunction = setInterval(refreshDrawing, (document.getElementById("periode").value*1000));
     }
    </script>
</head>
<body>
<h1>Web interface for Neato XV-25</h1>

<div id="topnav">
<ul>
    <li><a href="commandes.php">Commandes</a></li>
    <li><a href="ou-suis-je.php">O� suis-je ?</a></li>
    <li><a href="configuration.php">Configuration</a></li>
</ul>
</div>

<div class="form">
    <h2>O� suis-je ?</h2>
    <div class="centered">

        <div class="square" id="position">
         <b>Position XV-25</b> : <div id="xPos"></div> / <div id="yPos"></div> / <div id="thetaPos"></div>
        </div>
        <canvas class="xv" id="xv" width="500" height="500">
            HTML 5 is not supported by your browser. You need it to run this page !
        </canvas> 
        <br/>
        <canvas class="xv" id="xv-scale" width="500" height="50">
        </canvas>
        <br/>
        P�riode <input type="text" id="periode" name="periode" maxlength="5" size="5" value="1" onchange="updatePeriod()">
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