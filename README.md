# IP Failover Watch

This is a simple script that performs a couple checks to determine when an IP failover should run.

The two available checks are:

* HTTP status code check
* And an `mtr` check to verify network is not too slow

### Config

1) Copy `ip-failover-watch.conf.dist.php` somewhere and edit.

```php
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
        'keyfile' => '/root/.ssh/ip_failover_checker'
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
```

2) Run the command:

```
$ ip-failover-watch --config /path/to/ip-failover-watch.conf.php
```

For example, add it to cron tab (with --quiet to silence output):

```
*/3 * * * * /usr/local/bin/ip-failover-watch --quiet --config /path/to/ip-failover-watch.conf.php
```

### Example

```
$ /usr/local/bin/ip-failover-watch --dry-run
[2017-01-13 11:04:25] NOTE: DRY RUN
[2017-01-13 11:04:25] [INFO] ServerA:     192.168.180.76
[2017-01-13 11:04:25] [INFO] ServerB:     192.168.179.69
[2017-01-13 11:04:25] [INFO] Failover IP: 192.168.181.87
[2017-01-13 11:04:25]
[2017-01-13 11:04:25] Connecting to ServerA
[2017-01-13 11:04:25] Connecting to 192.168.180.76 ...
[2017-01-13 11:04:25] -> OK
[2017-01-13 11:04:25] Connecting to ServerB
[2017-01-13 11:04:25] Connecting to 192.168.179.69 ...
[2017-01-13 11:04:25] -> OK
[2017-01-13 11:04:25] [ServerA]$ ifconfig | grep '192.168.181.87'
[2017-01-13 11:04:25] [ServerA]	>> 1
[2017-01-13 11:04:25] [ServerB]$ ifconfig | grep '192.168.181.87'
[2017-01-13 11:04:25] [ServerB]	>> inet addr:192.168.181.87  Bcast:192.168.255.255  Mask:255.255.128.0
[2017-01-13 11:04:25] [ServerB]	>>
[2017-01-13 11:04:25] [ServerB]	>> 0
[2017-01-13 11:04:25] ServerB has the ip
[2017-01-13 11:04:25] Checking if ServerA is available
[2017-01-13 11:04:25] [http] GET http://192.168.180.76:9200/
[2017-01-13 11:04:25] [http] -> status(200)
[2017-01-13 11:04:25] [http] -> All OK.
[2017-01-13 11:04:25] ServerA is available
[2017-01-13 11:04:25] Checking if ServerB is available
[2017-01-13 11:04:25] [http] GET http://192.168.179.69:9200/
[2017-01-13 11:04:25] [http] -> status(200)
[2017-01-13 11:04:25] [http] -> All OK.
[2017-01-13 11:04:25] ServerB is available
[2017-01-13 11:04:25]
[2017-01-13 11:04:25] Running MTR health checks to verify network connection between proxy and db.
[2017-01-13 11:04:25] [ServerA :: mtr]$ mtr --report 192.168.177.11
[2017-01-13 11:04:35] [ServerA :: mtr]	>> Start: Fri Jan 13 11:04:25 2017
[2017-01-13 11:04:35] [ServerA :: mtr]	>> HOST: proxy01                 Loss%   Snt   Last   Avg  Best  Wrst StDev
[2017-01-13 11:04:35] [ServerA :: mtr]	>>   1.|-- some-host               0.0%    10    0.3   0.4   0.3   0.5   0.0
[2017-01-13 11:04:35] [ServerA :: mtr]	>>
[2017-01-13 11:04:35] [ServerA :: mtr]	>> 0
[2017-01-13 11:04:35] [ServerA :: Health check] AvgTime: 0.4ms  Loss: 0.0%
[2017-01-13 11:04:35] [ServerA :: Health check] OK
[2017-01-13 11:04:35] [ServerB :: mtr]$ mtr --report 192.168.177.11
[2017-01-13 11:04:45] [ServerB :: mtr]	>> Start: Fri Jan 13 11:04:35 2017
[2017-01-13 11:04:45] [ServerB :: mtr]	>> HOST: proxy02                 Loss%   Snt   Last   Avg  Best  Wrst StDev
[2017-01-13 11:04:45] [ServerB :: mtr]	>>   1.|-- some-host               0.0%    10    0.3   0.3   0.2   0.4   0.0
[2017-01-13 11:04:45] [ServerB :: mtr]	>>
[2017-01-13 11:04:45] [ServerB :: mtr]	>> 0
[2017-01-13 11:04:45] [ServerB :: Health check] AvgTime: 0.3ms  Loss: 0.0%
[2017-01-13 11:04:45] [ServerB :: Health check] OK
[2017-01-13 11:04:45]
[2017-01-13 11:04:45]
[2017-01-13 11:04:45] Summary:
[2017-01-13 11:04:45] ServerA[192.168.180.76] -- OK -- no ip
[2017-01-13 11:04:45] ServerB[192.168.179.69] -- OK -- HAS IP
[2017-01-13 11:04:45] FailoverIP[192.168.181.87] -- OK -- Failover not needed
[2017-01-13 11:04:45]
```