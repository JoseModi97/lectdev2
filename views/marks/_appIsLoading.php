<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 19-06-2021 19:15:31 
 * @modify date 19-06-2021 19:15:31 
 * 
 * @desc For some xhr/fetch calls we need a visual representation to show
 * that the application is processing/loading. We display this modal.
 */
?>

<div id="app-is-loading-modal" class="modal fade app-is-loading" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md app-is-loading" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #008cba; color: #fff;">
                <h5 id="app-is-loading-modal-title" class="modal-title"></h5>
            </div>
            <div class="modal-body">
                <div id="app-is-loading-message" style="padding: 5px;">
                    <h3 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h3>
                </div>
            </div>
        </div>
    </div>
</div>


