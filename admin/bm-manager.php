<?php

define('MMDVM_FUNC_DEFS_ONLY', true);  // do not autoexecute logs reading code in mmdvmhost/functions.php

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

include_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.BMApi.php';


$processUrl = '/admin/bm-manager.php';
$returnUrl = '/admin/?func=bm_man';

$bmApi = BMApi::getInstance();

//==== process actions ====
if (isset($_POST['static-tg-add'])) {
    if ($bmApi->getStatus() == BMApi::STATUS_OK) {
        $slot = $_POST['TS'];

        // get list of groups (any separator allowed)
        preg_match_all("/(\d+)/s", $_POST['TG'], $tgmatches);
        $tgs = $tgmatches[1];

        foreach ($tgs as $tg)
            $bmApi->addFavTG($tg, $slot);

        $bmApi->getFavTGs(); //update from profile if added directly from BM
        $bmApi->saveConfig();
    }

    redirectByHeader($returnUrl);
}

if (isset($_GET['droptg'])) {
    if ($bmApi->getStatus() == BMApi::STATUS_OK) {
        $bmApi->delFavTG($_GET['droptg'], $_GET['slot']);
        $bmApi->saveConfig();
    }

    redirectByHeader($returnUrl);
}

if (isset($_GET['masstg'])) {
    switch ($_GET['masstg']) {
        case 'enable':
            $bmApi->linkAllStatic();
            break;

        case 'disable':
            $bmApi->dropAllStatic();
            break;

        case 'drop':
            $bmApi->dropAllStatic(/*forever=*/true);
            break;
    }

    redirectByHeader($returnUrl);
}
//==========================

if ($bmApi->getStatus() != BMApi::STATUS_OK) {
    $helpBMHtml = "<a href=\"https://news.brandmeister.network/introducing-user-api-keys/\" target=\"new\" alt=\"BM API Keys\">BM API Key Announcement and Migration Instructions</a>; and then <a href=\"/admin/advanced/fulledit_bmapikey.php\">Enter your API Key</a> to enable this page.";

    $status2error = [
        BMApi::STATUS_BADCONFIG  => "Notice! Bad BrandMeister network configuration.",
        BMApi::STATUS_BMDISABLED => "Notice! BrandMeister network disabled.",
        BMApi::STATUS_NOKEY      => "Notice! You do not have a BrandMeister API key defined! Read the announcement on how create one: $helpBMHtml",
        BMApi::STATUS_LEGACYKEY  => "Notice! You have a legacy Brandmeister API Key, which will not work any longer. Read the announcement on how to migrate: $helpBMHtml",
    ];

    $errorMessage = $status2error[$bmApi->getStatus()] ?? "BM Error";
?>
    <div class="info larger">
        <?=$errorMessage?>
    </div>
<?php
} else { // bm status ok
    $duplexMode = getConfigItem("General", "Duplex", $_SESSION['MMDVMHostConfigs']) == "1";

    $ts1Enabled = getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "1";
    $ts2Enabled = getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) == "1";

    $ts1Selected = $ts1Enabled;
    $ts2Selected = !$ts1Enabled && $ts2Enabled;

    $profile = $bmApi->getProfile();
?>
    <script type="text/javascript" src="/js/bm-manager.js"></script>

    <div class="info" align="center">
        Your Hotspot/Repeater ID: <a href="https://brandmeister.network/?page=hotspot&amp;id=<?=$bmApi->dmrID?>" target="_blank" title="Click to view your hotspot info on BrandMeister"><?=$bmApi->dmrID?></a> &bull; Connected To: <?=$bmApi->dmrNetName?> &bull; <a href="https://w0chp.radio/brandmeister-talkgroups/" target="_blank">List of All BrandMeister Talkgroups</a>
    </div>

    <h3 class="section-header">Static Talkgroups</h3>
    <form id="bmm-tg-static-form" action="<?=$processUrl?>" method="POST">
    <table class="admin-table">
        <tr>
            <th align="left">Talkgroup</th>
            <th align="left">Timeslot</th>
            <th>Enable</th>
            <th>Drop</th>
            <th align="left">Name</th>
        </tr>
<?php
    foreach ($bmApi->getFavTGs() as $tg => $favTGData) {
        $dropUrl = htmlspecialchars("{$processUrl}?droptg={$tg}&slot={$favTGData['slot']}");
        $switchId = "toggle-tg{$tg}";
        $displaySlot = $favTGData['slot'] == 0? 2: $favTGData['slot'];
?>
        <tr>
            <td align="left">TG <?=$tg?></td>
            <td align="left">TS<?=$displaySlot?></td>
            <td style="padding: 2px 5px;">
                <div class="inline-switch">
                    <input type="checkbox" id="<?=$switchId?>" name="<?=$switchId?>" value="ON"
                        <?php echo $favTGData['linked']? 'checked="checked"': ''?>
                        data-tg="<?=$tg?>" data-slot="<?=$favTGData['slot']?>"
                        class="toggle toggle-round-flat bmm-tg-switch"
                        aria-hidden="false"
                        tabindex="-1" />
                    <label
                        id="aria-<?=$switchId?>"
                        for="<?=$switchId?>"
                        role="checkbox" tabindex="0"
                        aria-label="Toggle TG-<?=$tg?>"
                        aria-checked="<?php echo $favTGData['linked']? 'true': 'false'?>">
                            <font style="font-size:0px">Toggle TG-<?=$tg?></font>
                    </label>
                </div>
            </td>
            <td>
                <a class="clickloader" href="<?=$dropUrl?>" title="Unlink permanently &amp; delete from this list">Drop</a>
            </td>
            <td align="left"><?=$bmApi->resolveGroupName($tg)?></td>
        </tr>
<?php
    }
?>
	<tr>
            <td align="left">
                <textarea id="add-bm-tg-list" rows="5" cols="12" name="TG" value="" placeholder="Enter One Talkgroup per Line"></textarea>
            </td>
            <td align="left">

<?php
    if ($duplexMode) {
?>
                <input type="radio" id="ts1" name="TS" <?php if (!$ts1Enabled) echo 'disabled="disabled"'?> <?php if ($ts1Selected) echo 'checked="checked"'?> value="1"><label for="ts1">TS1</label><br>
                <input type="radio" id="ts2" name="TS" <?php if (!$ts2Enabled) echo 'disabled="disabled"'?> <?php if ($ts2Selected) echo 'checked="checked"'?> value="2"><label for="ts2">TS2</label>
<?php
    } else {
        //simplex - use TS0 for BM api
?>
                <input type="hidden" name="TS" value="0">
                <input type="radio" id="ts1" name="" disabled="disabled" value="1"><label for="ts1">TS1</label><br>
                <input type="radio" id="ts2" name="" checked="checked" value="2"><label for="ts2">TS2</label>
<?php
    }
?>
            </td>
            <td colspan="3" align="left">
                <input type="submit" class="clickloader" name="static-tg-add" value="Add &amp; Link">
                <span style="padding-left: 30px;">Mass Management:
                    <a class="clickloader" href="<?=$processUrl?>?masstg=enable">Enable all</a>
                    &bull;
                    <a class="clickloader" href="<?=$processUrl?>?masstg=disable">Disable all</a>
                    &bull;
                    <a class="clickloader" href="<?=$processUrl?>?masstg=drop" onclick="return confirm('Do you really want to drop all talkgroups?')">Drop all</a></span>
            </td>
        </tr>
    </table>
    </form>

    <div class="full-width-hint">
        <i class="fa fa-info-circle"></i> <b>Hint:</b> You can add multiple talkgroups at once. Added talkgroups are linked to BrandMeister. Disabling a talkgroup unlinks it from BrandMeister, but it remains in your favorites list. Use the Drop function to remove a talkgroup from the list permanently.
    </div>

    <h3 class="section-header">Dynamic Talkgroups</h3>

    <div class="dynamic-tgs" data-update-url="<?=$processUrl?>" data-update-period="15000">
        <!--DYNTGTABLE-BEGIN-->
        <table class="admin-table">
            <tr>
                <th align="left">Talkgroup</th>
                <th align="left">Timeslot</th>
                <th align="left">Name</th>
                <th align="left">Idle Timeout</th>
            </tr>
<?php
    $localTimeFormat = constant("TIME_FORMAT") == "24"? 'H:i:s': 'h:i:s A';
    $now = new DateTime();

    $dynTGs = $bmApi->getDynamicTGs();
    if (count($dynTGs) == 0) {
?>
            <tr>
                <td colspan="4">
                    No Talkgroups Linked
                </td>
            </tr>
<?php
    } else {
        foreach ($dynTGs as $dynTGData) {
            $displaySlot = $dynTGData['slot'] == 0? 2: $dynTGData['slot'];

            $then = new DateTime("@" . $dynTGData['timeout']);
            $minRemaining = $then->diff($now)->format('%i:%S');

            $expirationInfo = date($localTimeFormat, substr($dynTGData['timeout'], 0, 10)) . " " . date('T') .
                " (<span class=\"auto-calculate-remaining\" data-exptime=\"{$dynTGData['timeout']}\">{$minRemaining}</span> mins remaining)";
?>
            <tr>
                <td align="left">TG <?=$dynTGData['talkgroup']?></td>
                <td align="left">TS<?=$displaySlot?></td>
                <td align="left"><?=$bmApi->resolveGroupName($dynTGData['talkgroup'])?></td>
                <td align="left"><?=$expirationInfo?></td>
            </tr>
<?php
        }
    }
?>
            <tr>
                <td colspan="4">
<?php
    if ($duplexMode) {
?>
                    TS1:
                    <input class="clickbtn" data-linkto="/admin/system_api.php?action=bm_manager&cmd=drop_qso&slot=1" type="button" value="Drop QSO" title="Drop current QSO">
                    <input class="clickbtn" data-linkto="/admin/system_api.php?action=bm_manager&cmd=drop_dynamic&slot=1" type="button" name="dropalldyn" value="Drop All Dynamic" title="Drop all dynamic talkgroups">
                    &bull;
                    TS2:
                    <input class="clickbtn" data-linkto="/admin/system_api.php?action=bm_manager&cmd=drop_qso&slot=2" type="button" value="Drop QSO" title="Drop current QSO">
                    <input class="clickbtn" data-linkto="/admin/system_api.php?action=bm_manager&cmd=drop_dynamic&slot=2" type="button" name="dropalldyn" value="Drop All Dynamic" title="Drop all dynamic talkgroups">
<?php
    } else {
        //simplex
?>
                    <input class="clickbtn" data-linkto="/admin/system_api.php?action=bm_manager&cmd=drop_qso&slot=0" type="button" value="Drop QSO" title="Drop current QSO">
                    <input class="clickbtn" data-linkto="/admin/system_api.php?action=bm_manager&cmd=drop_dynamic&slot=0" type="button" name="dropalldyn" value="Drop All Dynamic" title="Drop all dynamic talkgroups">
<?php
    }
?>
                </td>
            </tr>
        </table>
        <!--DYNTGTABLE-END-->
    </div>
<?php
}
