<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 07-09-2021 12:13:30 
 * @modify date 07-09-2021 12:13:30 
 * @desc 
 */

 /**
 * @var $this yii\web\View
 * @var $facCode string
 * @var $deptCode string
 */

use app\components\Menu;
?>

<!-- Faculty admin operations -->
<div class="col-sm-12 col-md-12 col-lg-12">
    <div class="row">
        <div class="col-sm-12">
            <div class="rq">
                <ul class="dc ayn">
                    <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Faculty administrator</strong></a>
                        <ul class="dc ayn">
                            <?= Menu::nodeGen('Record returned scripts',['/departments/in-faculty', 'level' => 'facAdmin', 'type' => 'operations'])?>

                            <!-- REPORTS -->
                            <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">REPORTS</strong></a>
                                <ul class="dc ayn">
                                    <?= Menu::nodeGen('Returned scripts',['/departments/in-faculty', 'level' => 'facAdmin', 'type' => 'reports'])?>
                                </ul> 
                            </li>
                            <!-- END REPORTS -->

                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- End Faculty admin operations -->


