<?
echo date('Y-m-d H:i:s', strtotime('Tue, 13 Aug 2013 12:00:00 -0400'));

$date = date_create('Tue, 13 Aug 2013 12:00:00 -0400', timezone_open('America/New_York'));
echo date_format($date, 'Y-m-d H:i:s') . "\n";

?>