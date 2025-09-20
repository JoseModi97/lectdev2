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

<!-- HOD operations -->
<div class="col-sm-12 col-md-12 col-lg-12">
    <div class="row">
        <div class="col-sm-12">
            <div class="rq">
                <ul class="dc ayn">
                    <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">HOD</strong></a>
                        <ul class="dc ayn">
                            <?= Menu::nodeGen('Allocate lecturers',['/allocation'])?>
                            <?= Menu::nodeGen('View uploaded results',['/courses/in-department','level' => 'hod', 'deptCode' => $deptCode])?>

                            <!-- REPORTS -->
                            <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">REPORTS</strong></a>
                                <ul class="dc ayn">
                                    <?= Menu::nodeGen('Submitted marks',['/courses/in-department','level' => 'hod', 'deptCode' => $deptCode, 'type' => 'reports'])?>
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
<!-- End HOD operations -->






