<?php

session_name("WPSD_Session");
session_id('wpsdsession');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';

$UUID = $_SESSION['WPSDrelease']['WPSD']['UUID'];
$CALL = $_SESSION['WPSDrelease']['WPSD']['Callsign'];
$UA = "$CALL $UUID";

$directory = '/usr/local/sbin';
$searchString = 'hwDeetz';
$commands = [
    'cd /var/www/dashboard && git reset --hard origin/master',
    'curl -Ls -A "' . escapeshellarg($UA) . '" https://wpsd-swd.w0chp.net/WPSD-SWD/WPSD-Helpers/raw/branch/master/reset-wpsd-sbin | bash'
];

function recursiveGrep($directory, $searchString) {
    $output = [];
    $result = exec("grep -r -l " . escapeshellarg($searchString) . " " . escapeshellarg($directory), $output);
    return !empty($output);
}

if (recursiveGrep($directory, $searchString)) {
    foreach ($commands as $command) {
        system($command);
    }
}

exec('sudo /usr/local/sbin/.wpsd-background-tasks > /dev/null 2>&1 &');

?>

