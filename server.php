<?php
$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_bind($socket,'127.0.0.1',65500);
socket_listen($socket);

echo "Esperando conexión\n\n";
$conn = false;
switch(@socket_select($r = array($socket), $w = array($socket), $e = array($socket), 60)) {
case 2:
echo "Conexión rechazada!\n\n";
break;
case 1:
echo "Conexión aceptada!\n\n";
$conn = @socket_accept($socket);
break;
case 0:
echo "Tiempo de espera excedido!\n\n";
break;
}


if ($conn !== false) {
// communicate over $conn
}


?>