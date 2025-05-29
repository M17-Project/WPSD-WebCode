<?php
/* Common part of the /admin/?func=bm_man page */
?>
<script type="text/javascript">
function reloadbmConnections() {
    $("#bmConnects").load("/mmdvmhost/bm_links.php",function(){ setTimeout(reloadbmConnections,15000) });
}
setTimeout(reloadbmConnections,15000);

toggleBMUnlinkTGProcessing = false;
function toggleBMUnlinkTG(tg, ts) {
    if (toggleBMUnlinkTGProcessing) {
        // wait for the page to be reloaded
        return;
    }

    toggleBMUnlinkTGProcessing = true;

    // send unlink form
    $('#bm_man #tgNr').val(tg);
    $('#bm_man input[name="TS"][value="' + ts + '"]').prop('checked', true);
    $('#bm_man #tgDel').prop('checked', true);
    $('#bm_man #tgSubmit').click();

    // console.log(`toggleBMUnlinkTG tg=${tg} slot=${ts}`);
}
</script>
