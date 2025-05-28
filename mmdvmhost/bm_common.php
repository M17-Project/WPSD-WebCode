<?php
/* Common part of the /admin/?func=bm_man page */
?>
<script type="text/javascript">
function reloadbmConnections() {
    $("#bmConnects").load("/mmdvmhost/bm_links.php",function(){ setTimeout(reloadbmConnections,15000) });
}
setTimeout(reloadbmConnections,15000);
</script>
