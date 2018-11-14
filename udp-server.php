<?php
if (!extension_loaded('sockets')) {
    die('The sockets extension is not loaded.');
}

// conf socket
$host = '127.0.0.1';
$port = 7777;

// create unix udp socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    onSocketFailure("Failed to create socket", $socket);
}

// reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

// bind
if ($socket === false || !socket_bind($socket, $host, $port)) {
    socket_close($socket);
    onSocketFailure("Failed to bind socket", $socket);
}

$clients = [];
while (true) {
    socket_recvfrom($socket, $buffer, 65535, 0, $clientIP,$clientPort);
    $address = "$clientIP:$clientPort";
    //if (!isset($clients[$address])) {
    //    $clients[$address] = new Client();
    //}

    //$clients[$address]->handlePacket($buffer);
    echo "Received $buffer from remote address $clientIP and remote port $clientPort" . PHP_EOL;
}

/**
 * Trigger an exception with the last socket error.
 *
 * @param String
 * @param Socket
 */
function onSocketFailure(string $message, $socket = null)
{
    if (is_resource($socket)) {
        $message .= ": " . socket_strerror(socket_last_error($socket));
    }
    die($message);
}
