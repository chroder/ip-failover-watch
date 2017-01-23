<?php return array(
    'failover_ip'   => '192.168.181.87',
    'alert_email'   => 'email@example.com',
    'check_timeout' => 10,

    'network_health_check' => array(
        'check_ip'              => '192.168.177.11',  // IP address to check (host a and b will run an mtr to this)
        'warn_at_time'          => 8,                 // Warn when a server has an avg time of this (ms)
        'warn_at_loss'          => 10,                // Warn when a server has a loss percentage of this
        'switch_with_timediff'  => 20,                // Automatically switch when opposite server has a better ping time by at least this many ms
        'switch_with_lossdiff'  => 15                 // Automatically switch when opposite server has a better loss% by at least this
    ),

    'ssh_opts' => array(
        'port' => 2288,
        'user' => 'root',
        'keyfile' => '/root/.ssh/id_sysops'
    ),

    'server_a' => array(
        'ip'           => '192.168.180.76',
        'failover_eth' => 'eth0:2',
        'check_url'    => 'http://192.168.180.76:9200/',
    ),

    'server_b' => array(
        'ip'           => '192.168.179.69',
        'failover_eth' => 'eth0:2',
        'check_url'    => 'http://192.168.179.69:9200/',
    )
);