<?
include "conf.php";

$contents=file_get_contents("/var/lib/dhcp/dhcpd.leases");
$contents=explode("\n", $contents);

$current=0;
foreach($contents as $line) {
  switch($current) {
    case 0:
      if(preg_match("/^\s*(|#.*)$/", $line, $m)) {
      }
      else if(preg_match("/^lease (.*) {/", $line, $m)) {
        $current=$m[1];
      }
      else if(preg_match("/^server-duid/", $line)) {
        // ignore
      }
      else {
        print "Failed parsing '$line'\n";
      }
      break;
    default:
      if(preg_match("/^\s*([a-z\-]+) (.*);$/", $line, $m)) {
        $data[$current][$m[1]]=$m[2];
      }
      elseif(preg_match("/}/", $line, $m)) {
        $current=0;
      }
      else {
        print "Failed parsing '$line'\n";
      }
  }
}

$count_active=array();
foreach($pools as $pool_id=>$p) {
  $count_active[$pool_id]=0;
}

foreach($data as $ip=>$d) {
  if($d['binding']=="state active") {
    foreach($pools as $pool_id=>$p) {
      if(preg_match("/$p/", $ip)) {
        $count_active[$pool_id]++;
      }
    }
  }
}

