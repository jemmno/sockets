<?php
  // Config
  $host = '127.0.0.1';
  $port = 4510; // Random port

  // Setup
  $socket  = socket_create(AF_INET, SOCK_DGRAM, 0);
  $clients = array();
  
  // Bind, listen and disable blocking
  if ($socket === FALSE
    || ! socket_bind($socket, $host, $port)
    || ! socket_listen($socket)
    || socket_set_nonblock($socket) !== TRUE)
    trigger_socket_error();
  
  // Get socket info
  debug("Listening on " . socket_human($socket));
    
  // Event loop
  while (true)
  {
    $reads   = array_merge(array($socket), $clients);
    $writes  = NULL;
    $excepts = NULL;
    
    // Status
    debug("Clients: " . count($reads));
    
    // Wait for data on ANY socket… FOREVER!
    if (($n = socket_select($reads, $writes, $excepts, NULL)) === FALSE)
      trigger_socket_error();
    
    // No data? WTF?
    if ($n === 0) continue;
    
    // Accept new clients
    if (($client = @socket_accept($socket)) !== FALSE)
    {
      --$n;
      $clients[] = $client;
      debug("New client accepted: " . socket_human($client));
    }
    
    // Accept and handle data
    $numclients = count($clients);
    for ($i = 0; $n > 0 && $i < $numclients; ++$i)
    {
      $client = $clients[$i];
      switch ($data = socket_read($client, 65535, PHP_NORMAL_READ))
      {
        // Empty data or FALSE (disconnect): close our socket and remove
        // from list of clients
        case FALSE:
        case "":
          debug("Lost client: " . socket_human($client));
          socket_close($client);
          unset($clients[$i]);
          $clients = array_merge($clients);
          break;
        
        // We have incoming data; handle it!
        default:
          $data = trim($data);
          debug(socket_human($client) . ' -> ' . $data);
          $response = handle($client, $data);
          // Can’t do this, we don’t know if socket is ready for writing
          // debug(socket_human($client) . ' <- ' . trim($response));
          // socket_write($client, $response);
          --$n;
          break;
      }
    }
  }
  
  /**
   * Handles incoming data on a socket.
   *
   * @param Socket client
   * @param String data
   * @return String response
   */
  function handle($client, $data)
  {
    debug("Got data: " . $data);
    return "Thanks for ${data}!\n";
  }
  
  /**
   * Get human representation of a socket.
   *
   * @param Socket
   * @param Boolean remote side
   * @return String
   */
  function socket_human($socket, $remote = FALSE)
  {
    $host = '';
    $port = 0;
    $remote ? socket_getpeername($socket, $host, $port)
            : socket_getsockname($socket, $host, $port);
    return "${host}:${port}";
  }
  
  /**
   * Print a debug statement.
   *
   * @param String
   */
  function debug($msg)
  {
    printf("[%s]: %s\n", date('Y-m-d H:i:s'), $msg);
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
  
/* End of file server.php */