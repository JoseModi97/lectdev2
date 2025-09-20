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
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            padding: 0;
            margin: 0;
        }

        .email-container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }

        .email-header {
            text-align: center;
            /* background: #2a68af; */
            padding: 10px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .email-header img {
            max-width: 200px;
            height: auto;
        }

        .email-body {
            padding: 20px;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }

        .email-footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #666;
            background: #f4f4f4;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .button {
            display: inline-block;
            background: #2a68af;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
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