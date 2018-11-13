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
if ($socket === FALSE || ! socket_bind($socket, $host, $port)) trigger_socket_error();


$clients = []; 
while (true){ 
    socket_recvfrom($socket, $buffer, 32768, 0, $ip, $port) === true or onSocketFailure("Failed to receive packet", $socket); 
    $address = "$ip:$port"; 
    if (!isset($clients[$address])) $clients[$address] = new Client(); 
    $clients[$address]->handlePacket($buffer); 
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