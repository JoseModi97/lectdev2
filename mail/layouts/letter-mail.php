<?php

/** @var string $title */
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
  
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="https://www.uonbi.ac.ke/sites/default/files/UoN_Logo.png" alt="Company Logo">
        </div>

        <!-- Body -->
        <div class="email-body">
            <?= $content ?>
        </div>
        <div>
            <p>For any further details, kindly log in to the system
                (<a href="<?= Yii::$app->request->hostInfo . Yii::getAlias('@web') ?>" style="color: #2a68af; text-decoration: underline;">graduate-tracking</a>).
            </p>
            <p>Best regards,<br>
                <strong>Graduate Office</strong>
            </p>
        </div>


        <!-- Footer -->
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> University of Nairobi. All rights reserved.</p>
        </div>
    </div>
</body>

</html>