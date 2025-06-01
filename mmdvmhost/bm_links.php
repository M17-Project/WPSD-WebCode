<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('wpsdsession');
    session_start();
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    checkSessionValidity();
}
    
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

if (isset($_SESSION['CSSConfigs']['Background']['TableRowBgEvenColor'])) {
    $tableRowEvenBg = $_SESSION['CSSConfigs']['Background']['TableRowBgEvenColor'];
    $tableRowOddBg = $_SESSION['CSSConfigs']['Background']['TableRowBgOddColor'];
} else {
    $tableRowEvenBg = "inherit";
    $tableRowOddBg = "inherit";
}

// honor time format settings
if (constant("TIME_FORMAT") == "24") {
    $local_time = 'H:i:s';
} else {
    $local_time = 'h:i:s A';
}

// Check if DMR is Enabled
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);

// function to count users in TGs
function getCallsignCount($tg, $isDynamic = false) { // pass "true" if TG is dynamic, else, it's static
    $cacheFilePrefix = '/tmp/bm_static_count_';
    if ($isDynamic) {
        $cacheFilePrefix = '/tmp/bm_dynamic_count_';
    }

    // let's be kind to BM's api...cache!
    $cacheFile = $cacheFilePrefix . $tg . '.json';
    $cacheAgeLimit = 300; // update caache every 5 min

    // Check if the cache file exists and is not older than the age limit
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheAgeLimit) {
        // If the cache is fresh, read and return the count from the cache
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if (isset($cacheData['count'])) {
            return $cacheData['count'];
        }
    }

    // If the cache is not available or outdated, make the API request!
    $context = stream_context_create(array('http'=>array('timeout' => 10, 'header' => 'User-Agent: WPSD Software for '.$dmrID) )); // Add Timout and User Agent to include DMRID
    $response = file_get_contents("https://api.brandmeister.network/v2/talkgroup/$tg/devices", true, $context);

    if ($response === false) {
        echo 'Error making HTTP request';
        return false;
    }

    $data = json_decode($response, true);

    if ($data === null) {
        echo 'Error decoding JSON';
        return false;
    } else {
        $callsigns = array_column($data, 'callsign');
        $count = count($callsigns);

        // Update the cache with the new count
        $cacheData = ['count' => $count];
        file_put_contents($cacheFile, json_encode($cacheData));

        return $count;
    }
}

if ( $testMMDVModeDMR == 1 ) {
    $bmEnabled = true;

    //setup BM API Key
    $bmAPIkeyFile = '/etc/bmapi.key';
    if (file_exists($bmAPIkeyFile) && fopen($bmAPIkeyFile,'r')) {
        $configBMapi = parse_ini_file($bmAPIkeyFile, true);
        $bmAPIkey = $configBMapi['key']['apikey'];
    }

    // Get the current DMR Master from the config
    $dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
    if ( $dmrMasterHost == '127.0.0.1' ) {
        $dmrMasterHost = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'];
        $bmEnabled = ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] != "0" ? true : false);
        if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Id'])) { $dmrID = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Id']; }
    }
    else if (getConfigItem("DMR", "Id", $_SESSION['MMDVMHostConfigs'])) {
        $dmrID = getConfigItem("DMR", "Id", $_SESSION['MMDVMHostConfigs']);
    }
    else {
        $dmrID = getConfigItem("General", "Id", $_SESSION['MMDVMHostConfigs']);
    }
    
    // Make sure the master is a BrandMeister Master
    if (($dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r")) != FALSE) {
        while (!feof($dmrMasterFile)) {
            $dmrMasterLine = fgets($dmrMasterFile);
            $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
            if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
                if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
            }
        }
        fclose($dmrMasterFile);
    }

    if ((substr($dmrMasterHost, 0, 3) == "BM ") && ($bmEnabled == true) && isset($_SESSION['BMAPIKey'])) { 
        $bmAPIkey = $_SESSION['BMAPIKey'];
        // Use BM API to get information about current TGs
        $jsonContext = stream_context_create(array('http'=>array('timeout' => 10, 'header' => 'User-Agent: WPSD Software for '.$dmrID) )); // Add Timout and User Agent to include DMRID
        $json = json_decode(@file_get_contents("https://api.brandmeister.network/v2/device/$dmrID/profile", true, $jsonContext));
        // Set some vars
        $bmStaticTGList = "";
        $bmDynamicTGList = "";
        $bmDynamicTGname = "";
        $bmDynamicTGexpire = "";
        // Pull the information from JSON
        if (isset($json->staticSubscriptions)) { $bmStaticTGListJson = $json->staticSubscriptions;
            foreach($bmStaticTGListJson as $staticTG) {
                $linkedAtSlot = false;
                if ((getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) && $staticTG->slot == "1") ||
                    (getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $staticTG->slot == "2")) {
                        $linkedAtSlot = $staticTG->slot;
                } else if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "0" && getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $staticTG->slot == "0") {
                    $linkedAtSlot = "2";
                }

                if ($linkedAtSlot !== false) {
                    $switchId = "sw-unlink-tg-" . $staticTG->talkgroup;
                    $switchJsFunc = "toggleBMUnlinkTG({$staticTG->talkgroup}, {$staticTG->slot})";
                    $switchHtml = "<span style=\"float: right; padding-right: 8px;\"><a href='#' onclick=\"$switchJsFunc\">Unlink</a></span>";
                    $bmStaticTGname = exec("grep -w \"$staticTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
                    $bmStaticTGList .= "<tr>" .
                        "<td align='left' style='padding-left: 8px;'>TG " . $staticTG->talkgroup . " $switchHtml</td>" .
                        "<td align='left' style='padding-left: 8px;'>$bmStaticTGname</td>" .
                        "<td align='left' style='padding-left: 8px;'>TS$linkedAtSlot</td>" .
                        "</tr>\n";
                }
            }
            if (preg_match('/TG/', $bmStaticTGList) == false) { $bmStaticTGList = "<tr><td colspan='4'>No Talkgroups Linked</td></tr>"; }
        }
        else { $bmStaticTGList = "<tr><td colspan='4'>No Talkgroups Linked</td></tr>"; }
        if (isset($json->dynamicSubscriptions)) { $bmDynamicTGListJson = $json->dynamicSubscriptions;
            foreach($bmDynamicTGListJson as $dynamicTG) {
                $linkedAtSlot = false;
                if ((getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) && $dynamicTG->slot == "1") ||
                    (getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $dynamicTG->slot == "2")) {
                        $linkedAtSlot = $dynamicTG->slot;
                }
                else if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "0" && getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $dynamicTG->slot == "0") {
                    $linkedAtSlot = "2";
                }

                if ($linkedAtSlot !== false) {
                    $now = new DateTime();
                    $then = new DateTime("@" . $dynamicTG->timeout);
                    $diff = $then->diff($now);
                    $bmDynamicTGexpire = $diff->format('%i:%S mins');
                    $bmDynamicTGname = exec("grep -w \"$dynamicTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
                    $bmDynamicTGList .= "<tr>" .
                        "<td align='left' style='padding-left: 8px;'>TG ".$dynamicTG->talkgroup."</td>" .
                        "<td align='left' style='padding-left: 8px;'>$bmDynamicTGname</td>" .
                        "<td align='left' style='padding-left: 8px;'>TS$linkedAtSlot</td>" .
                        "<td align='left' style='padding-left: 8px;'>".date("$local_time", substr($dynamicTG->timeout, 0, 10))." ".date('T')." ($bmDynamicTGexpire remaining)</td>" .
                        "</tr>";
                }
            }
            $bmDynamicTGList = wordwrap($bmDynamicTGList, 135, "\n");
            if (preg_match('/TG/', $bmDynamicTGList) == false) { $bmDynamicTGList = "<tr><td colspan='5'>No Talkgroups Linked</td></tr>"; }
        } else { $bmDynamicTGList = "<tr><td colspan='5'>No Talkgroups Linked</td></tr>"; }
            echo '<div style="text-align:left;font-weight:bold;" class="larger">Linked Talkgroups</div>
  <table id="bmLinks">
    <tr style="font-size:1.1em;">
      <th><a class=tooltip href="#">Static Talkgroups<span><b>Statically linked talkgroups</b></span></a></th>
      <th><a class=tooltip href="#">Dynamic Talkgroups<span><b>Dynamically linked talkgroups</b></span></a></th>
    </tr>'."\n";
        echo '    <tr>'."\n";
        echo '     <td align="left" style="background:'.$tableRowOddBg.';vertical-align:top;padding:0;margin:0;border:none;">';
        echo "     <table style='padding:0;margin:0;border:none;'>";
        echo "     <tr style='padding:0;margin:0;border:none;font-size:0.85em;'>";
        echo "       <th align='left' style='padding-left: 8px;'>Talkgroup #</th>";
        echo "       <th align='left' style='padding-left: 8px;'>Name</th>";
        echo "       <th align='left' style='padding-left: 8px;'>Timeslot</th>";
        echo "     </tr>";
        echo "     $bmStaticTGList";
        echo "     </table>";
        echo '     </td>';
        echo '     <td align="left" style="background:'.$tableRowOddBg.';vertical-align:top;padding:0;margin:0;border:none;">';
        echo "     <table style='padding:0;margin:0;border:none;'>";
        echo "     <tr style='padding:0;margin:0;border:none;font-size:0.85em;'>";
        echo "       <th align='left' style='padding-left: 8px;'>Talkgroup #</th>";
        echo "       <th align='left' style='padding-left: 8px;'>Name</th>";
        echo "       <th align='left' style='padding-left: 8px;'>Timeslot</th>";
        echo "       <th align='left' style='padding-left: 8px;'>Idle Timeout</th>";
        echo "     </tr>";
        echo "     $bmDynamicTGList";
        echo "     </table>";
        echo '     </td>';
        echo '    </tr>'."\n";
        echo '    <tr>'."\n";
        echo '      <td colspan="3" style="white-space:normal;padding: 3px;background:'.$tableRowEvenBg.'">Your Hotspot/Repeater ID: <a href="https://brandmeister.network/?page=hotspot&amp;id='.$dmrID.'" target="_new" title="Click to view your hotspot info on BrandMeister">'.$dmrID.'</a> &bull; Connected To: '.$dmrMasterHost.' &bull; <a href="https://w0chp.radio/brandmeister-talkgroups/" target="_blank">List of All BrandMeister Talkgroups</a></td>'."\n";
        echo '    </tr>'."\n";
        echo '  </table>'."\n";
    }
}