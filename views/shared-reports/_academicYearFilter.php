<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var string $reportType
 * @var string $url
 */

use yii\helpers\Url;
?>

<div class="academic-year-filter">
    <form action="<?= Url::to([$url])?>">
        <input type="hidden" name="report-type" value="<?= $reportType; ?>">
        <div class="input-group">
            <select name="academic-year" class="form-control academic-year">
                <option value="">Academic Year</option>
                <?php if(YII_ENV_DEV): ?>
                    <option value="2020/2021">2020/2021</option>
                <?php endif; ?>
                <option value="2021/2022">2021/2022</option>
                <option value="2022/2023">2022/2023</option>
            </select>
            <span class="input-group-btn" style="width:0;">
                <button class="btn" title="Filter by academic year">
                    <i class="fa fa-filter" aria-hidden="true"></i> Filter by academic year
                </button>
            </span>
        </div>
    </form>
</div>
