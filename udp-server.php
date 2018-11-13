<?php
if (!extension_loaded('sockets')) {
    die('The sockets extension is not loaded.');
}
// create unix udp socket
$host = '127.0.0.1';
$port = 4568;
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket)
        die('Unable to create AF_UNIX socket');


        // Bind, listen and disable blocking
if ($socket === FALSE
  || ! socket_bind($socket, $host, $port)
  || ! socket_listen($socket)
  || socket_set_nonblock($socket) !== TRUE)
  trigger_socket_error();


while(1) // server never exits
{
// receive query
if (!socket_set_block($socket))
        die('Unable to set blocking mode for socket');
$buf = '';
$from = '';
echo "Ready to receive in port $port...\n";

// will block to wait client query
$bytes_received = socket_recvfrom($socket, $buf, 65536, 0, $from);
if ($bytes_received == -1)
        die('An error occured while receiving from the socket');
echo "Received $buf from $from\n";

$buf .= "->Response"; // process client query here

// send response
if (!socket_set_nonblock($socket))
        die('Unable to set nonblocking mode for socket');
// client side socket filename is known from client request: $from
$len = strlen($buf);
$bytes_sent = socket_sendto($socket, $buf, $len, 0, $from);
if ($bytes_sent == -1)
        die('An error occured while sending to the socket');
else if ($bytes_sent != $len)
        die($bytes_sent . ' bytes have been sent instead of the ' . $len . ' bytes expected');
echo "Request processed\n";
}

  /**
   * Trigger an exception with the last socket error.
   *
   * @param Socket
   */
  function trigger_socket_error($socket = null)
  {
    $code = $socket ? socket_last_error($socket) : socket_last_error();
    debug_print_backtrace();
    trigger_error("[${code}] " . socket_strerror($code), E_USER_ERROR);
  }
  
?>