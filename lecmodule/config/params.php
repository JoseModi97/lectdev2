<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc system parameters
 */   

return [
    'sitename' => 'Lecturer Module',
    'sitename_short' => 'Lecturer Module',
    'icon-framework' => 'fa',  
    'availableSchemas' => [
        'HRMIS', 
        'MUTHONI'
    ],
    'noReplyEmail' => 'examadmin@uonbi.ac.ke',
    'newFaculties' => ['B', 'D', 'E', 'F', 'G', 'H', 'I', 'L', 'M', 'N'],
    'facultiesWithMultipleExams' => [
        'G',
    ],
    'academicYears' => [
        '2024/2025' => '2024/2025',
        '2023/2024' => '2023/2024',
        '2022/2023' => '2022/2023',
        '2021/2022' => '2021/2022',
        '2020/2021' => '2020/2021',
        '2019/2020' => '2019/2020',
    ],

    'publishMarksApiUrl' => 'https://smis.uonbi.ac.ke/api_marksheet_dev.php',
]; 

