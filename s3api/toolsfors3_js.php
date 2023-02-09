<?php
/**
 * @ Author: Bill Minozzi
 * @ Copyright: 2022 www.BillMinozzi.com
 * Created: 2022 - Sept 20
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<script>
    Dropzone.autoDiscover = false;
    console.log('linha 111994');
    //Dropzone class
    var myDropzone = new Dropzone(".dropzone", {
        url: ajaxurl + "?action=toolsfors3&prefix=<?php echo esc_attr($prefix); ?>&bucket=<?php echo esc_attr($bucket_name); ?>",
        paramName: "file",
        maxFiles: 10,
        maxFilesize: 100,
        parallelUploads: 10,
        autoProcessQueue: false,
        init: function() {
            console.log('init');
        },
        error: function(response, errorMessage, xhrObj) {
            const strippedString = errorMessage.replace(/(<([^>]+)>)/gi, "");
            alert(strippedString);
        }
    });
    myDropzone.on("queuecomplete", function(file) {
        console.log('queue complete!!');
        location.reload();

    });
    jQuery('#startUpload').click(function() {
        console.log('começou');
        myDropzone.processQueue();
    });
    jQuery('#cancelUpload').click(function() {
        console.log('cancelou');
        myDropzone.removeAllFiles();
    });
</script>