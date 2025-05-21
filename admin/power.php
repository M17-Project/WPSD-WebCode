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

// Load the language support
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/power.php") {
    // Sanity Check Passed.
    header('Cache-Control: no-cache');

    if(isset($_SESSION['WPSDrelease']['WPSD']['ProcNum']) && ($_SESSION['WPSDrelease']['WPSD']['ProcNum'] >= 4)) {
	exec('/usr/local/sbin/.wpsd-platform-detect | grep "Pi 5 Model" | wc -l', $output);
	$count = intval($output[0]);
	$is_pi_5 = ($count >= 1);
	if ($is_pi_5) { // redir in 30 secs. for Pi5's
	    $rbTime = 45;
	} else {
	    $rbTime = 90; // typical 4-core archs.
	}
    } else {
	$rbTime = 120; // single core archs
    }

    // Calculate minutes and remaining seconds
    $rbMinutes = floor($rbTime / 60);
    $rbSeconds = $rbTime % 60;

    // Set time unit based on the value of $rbTime
    if ($rbMinutes > 0 && $rbSeconds > 0) {
	$timeUnit = "minutes and seconds";
    } elseif ($rbMinutes > 0) {
	$timeUnit = ($rbMinutes > 1) ? "minutes" : "minute";
    } else {
	$timeUnit = ($rbSeconds > 1) ? "seconds" : "second";
    }
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html lang="en">
	<head>
	    <meta name="language" content="English" />
	    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	    <meta http-equiv="pragma" content="no-cache" />
	    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
	    <meta http-equiv="Expires" content="0" />
	    <title>WPSD <?php echo __( 'Dashboard' )." - ".__( 'Power' );?></title>
	    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
        <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript" src="/js/functions.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript">
          $.ajaxSetup({ cache: false });
          window.time_format = '<?php echo constant("TIME_FORMAT"); ?>';
        </script>
	</head>
	<body>
	    <div class="container">
		<div class="header">
		    <div class="SmallHeader shLeft noMob">Hostname: <?php echo exec('cat /etc/hostname'); ?></div>
		    <?php if ($_SESSION['CURRENT_PROFILE']) { ?><div class="SmallHeader shLeft noMob"> | <?php echo __( 'Current Profile' ).": ";?> <?php echo $_SESSION['CURRENT_PROFILE']; ?></div><?php } ?>
		    <div class="SmallHeader shRight noMob">
                      <div id="CheckUpdate">
                      <?php
                          include $_SERVER['DOCUMENT_ROOT'].'/includes/checkupdates.php';
                      ?>
                      </div><br />
                    </div>
		    <h1>WPSD <?php echo __(( 'Dashboard' )) . " - ".__( 'Power' );?></h1>
			<div class="navbar">
              <script type= "text/javascript">
              function reloadDateTime(){
                $( '#timer' ).html( _getDatetime( window.time_format ) );
                setTimeout(reloadDateTime,1000);
                }
                reloadDateTime();
              </script>
              <div class="headerClock">
                <span id="timer"></span>
            </div>
			    <a class="menuconfig" href="/admin/configure.php"><?php echo __( 'Configuration' );?></a>
			    <a class="menubackup noMob" href="/admin/config_backup.php"><?php echo __( 'Backup/Restore' );?></a>
			    <a class="menuupdate noMob" href="/admin/update.php"><?php echo __( 'WPSD Update' );?></a>
			    <a class="menuadmin noMob" href="/admin/"><?php echo __( 'Admin' );?></a>
			    <a class="menudashboard" href="/"><?php echo __( 'Dashboard' );?></a>
			</div>
		</div>
		<div class="contentwide">
		    <?php if (!empty($_POST)) { ?>
			<table width="100%">
			    <tr><th colspan="2"><?php echo __( 'Power' );?></th></tr>
			    <?php
			    if ( escapeshellcmd($_POST["action"]) === "reboot" ) {
				echo '<tr><td colspan="2" style="background: #000000; color: #4DEEEA;"><br /><br />System is rebooting...
				    <br /><br />You will be redirected back to the dashboard automatically.<br /><br /><div class="status"></div><br />
				    <script>

                var offline = false;
                
                function check_internet_connection(){
                  $(".status").html("<svg style=\"fill:#4DEEEA;\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" xmlns=\"http://www.w3.org/2000/svg\"><style>.spinner_Wezc{transform-origin:center;animation:spinner_Oiah .75s step-end infinite}@keyframes spinner_Oiah{8.3%{transform:rotate(30deg)}16.6%{transform:rotate(60deg)}25%{transform:rotate(90deg)}33.3%{transform:rotate(120deg)}41.6%{transform:rotate(150deg)}50%{transform:rotate(180deg)}58.3%{transform:rotate(210deg)}66.6%{transform:rotate(240deg)}75%{transform:rotate(270deg)}83.3%{transform:rotate(300deg)}91.6%{transform:rotate(330deg)}100%{transform:rotate(360deg)}}</style><g class=\"spinner_Wezc\"><circle cx=\"12\" cy=\"2.5\" r=\"1.5\" opacity=\".14\"/><circle cx=\"16.75\" cy=\"3.77\" r=\"1.5\" opacity=\".29\"/><circle cx=\"20.23\" cy=\"7.25\" r=\"1.5\" opacity=\".43\"/><circle cx=\"21.50\" cy=\"12.00\" r=\"1.5\" opacity=\".57\"/><circle cx=\"20.23\" cy=\"16.75\" r=\"1.5\" opacity=\".71\"/><circle cx=\"16.75\" cy=\"20.23\" r=\"1.5\" opacity=\".86\"/><circle cx=\"12\" cy=\"21.5\" r=\"1.5\"/></g></svg>");
            $.ajax({
                type: "GET",
                url: "/api/", 
                success: function(data, status, xhr) {
                    if ( offline ) {
                      // we were offline so reboot must have happened?
                      $(".status").html("System is back, redirecting to Dashboard");
                      setTimeout( () => {
                        location.href = "/";
                      }, "1500");
                    }
                },
                error: function(output) {
                    // ok were offline
                    offline = true;
                    console.log("Offline now....")
                    }
            });
        };
                setInterval(check_internet_connection, 1500);
                
				    </script>
				    </td></tr>';
				   exec("sudo sync && sleep 2 && sudo reboot > /dev/null 2>&1 &");
			    }
			    else if ( escapeshellcmd($_POST["action"]) == "shutdown" ) {
				echo '<tr><td colspan="2" style="background: #000000; color: #4DEEEA;"><br /><br />Shutdown command has been sent to the system.
				   <br />Please wait at least 30 seconds for it to fully shutdown<br />before removing the power.<br /><br /><br /></td></tr>';
				   exec("sudo sync && sleep 3 && sudo shutdown -h now > /dev/null 2>&1 &");
			    }

			    unset($_POST);
			    ?>
			</table>
		    <?php }
		    else { ?>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			    <table width="100%">
				<tr>
				    <th colspan="2"><?php echo __( 'Power' );?></th>
				</tr>
				<tr>
				    <td align="center">
					<h3>Reboot</h3><br />
					<button style="border: none; background: none; margin: 15px 0px;" name="action" value="reboot"><img src="/images/reboot.png" border="0" alt="Reboot" /></button>
				    </td>
				    <td align="center">
					<h3>Shutdown</h3><br />
					<button style="border: none; background: none; margin: 15px 0px;" id="shutdown" name="action" value="shutdown"><img src="/images/shutdown.png" border="0" alt="Shutdown" /></button>					
				    </td>
				</tr>
			    </table>
			</form>
		    <?php } ?>
		</div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
	    </div>
	</body>
    </html>
<?php
}
?>
