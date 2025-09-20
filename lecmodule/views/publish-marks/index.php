<?php
/**
 * @date: 8/6/2025
 * @time: 11:37 AM
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'MARKS APPROVAL',
    'url' => Yii::$app->request->referrer ?: ['/allocated-courses'],
];
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="publish-marks">
        <?php

        $gridColumns = [
            [
                'class' => 'kartik\grid\SerialColumn',
                'width' => '5%'
            ],
            [
                'attribute' => 'REGISTRATION_NUMBER',
                'label' => 'REG NUMBER',
                'width' => '8%',
            ],
            [
                'attribute' => 'SURNAME',
                'label' => 'SURNAME',
                'width' => '10%',
                'value' => function ($model) {
                    return ucwords($model['SURNAME']);
                }
            ],
            [
                'attribute' => 'OTHER_NAMES',
                'label' => 'OTHER NAMES',
                'width' => '15%',
                'value' => function ($model) {
                    return ucwords($model['OTHER_NAMES']);
                }
            ],
            [
                'label' => 'EXAM TYPE',
                'attribute' => 'EXAM_TYPE',
                'width' => '8%',
            ],
            [
                'label' => 'CW',
                'attribute' => 'COURSE_MARKS',
                'width' => '5%',
                'value' => function ($model) {
                    return $model['COURSE_MARKS'] ?? '--';
                }
            ],
            [
                'label' => 'EXAM',
                'attribute' => 'EXAM_MARKS',
                'width' => '5%',
                'value' => function ($model) {
                    return $model['EXAM_MARKS'] ?? '--';
                }
            ],
            [
                'label' => 'FINAL',
                'attribute' => 'FINAL_MARKS',
                'width' => '5%',
                'value' => function ($model) {
                    return $model['FINAL_MARKS'] ?? '--';
                }
            ],
            [
                'label' => 'GRADE',
                'attribute' => 'GRADE',
                'width' => '5%',
                'value' => function ($model) {
                    return $model['GRADE'] ?? '--';
                }
            ],
            [
                'label' => 'MARKS COMPLETE',
                'attribute' => 'MARKS_COMPLETE',
                'format' => 'raw',
                'width' => '10%',
                'value' => function ($model) {
                    if ($model['MARKS_COMPLETE'] == 1) {
                        return '<span class="text-success"><i class="glyphicon glyphicon-ok-circle"></i></span>';
                    } else {
                        return '<span class="text-danger"><i class="glyphicon glyphicon-remove-circle"></i></span>';
                    }
                }
            ],
            [
                'label' => 'LATEST BALANCE',
                'attribute' => 'BALANCE',
                'format' => 'raw',
                'width' => '10%',
                'value' => function ($model) {

                    $balanceRow = (new \yii\db\Query())
                        ->select(['BALANCE'])
                        ->from('MUTHONI.UON_STUDENTS_BALANCE_ALL_UFB')
                        ->where(['REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER']])
                        ->one();

                    if ($balanceRow && isset($balanceRow['BALANCE'])) {
                        $balance = (float)$balanceRow['BALANCE'];
                        $formatted = Yii::$app->formatter->asDecimal($balance, 2);

                        if ($balance > 0) {
                            return '<span class="text-danger">' . $formatted . '</span>';
                        } elseif ($balance < 0) {
                            return '<span class="text-success">' . $formatted . '</span>';
                        } else {
                            return '<span class="text-muted">0.00</span>';
                        }
                    }

                    return '<span class="text-muted">N/A...</span>';
                },
            ],
            // [
                // 'label' => 'LATEST BALANCE',
                // 'attribute' => 'BALANCE',
                // 'format' => 'raw',
                // 'width' => '10%',
                // 'value' => function ($model) {
                //     if (isset($model['BALANCE'])) {
                //         $balance = $model['BALANCE'];
                //         $formatted = Yii::$app->formatter->asDecimal($balance, 2);

                //         if ($balance > 0) {
                //             return '<span class="text-danger">' . $formatted . '</span>';
                //         } elseif ($balance < 0) {
                //             return '<span class="text-success">' . $formatted . '</span>';
                //         } else {
                //             return '<span class="text-muted">0.00</span>';
                //         }
                //     }

                //     return '<span class="text-muted">N/A</span>';

//                if (isset($balanceMap[$regNo])) {
//                    $balance = $balanceMap[$regNo]->BALANCE;
//                    $formatted = Yii::$app->formatter->asDecimal($balance, 2);
//
//                    if ($balance > 0) {
//                        return '<span class="text-danger">' . $formatted . '</span>';
//                    } elseif ($balance < 0) {
//                        return '<span class="text-success">' . $formatted . '</span>';
//                    } else {
//                        return '<span class="text-muted">0.00</span>';
//                    }
//                }
                // },
            // ],
            [
                'label' => 'PUBLISHABILITY',
                'format' => 'raw',
                'width' => '15%',
                'value' => function ($model) {
                    if ($model['PUBLISH_STATUS'] == 1) {
                        return '<span class="text-success"><i class="glyphicon glyphicon-ok-circle"></i></span>';
                    }

                    $status = '<span class="text-danger"><i class="glyphicon glyphicon-remove-circle"></i></span>';

                    // First check: if marks are incomplete
                    if (isset($model['MARKS_COMPLETE']) && $model['MARKS_COMPLETE'] == 0) {
                        $status .= ' | <span class="text-danger">Not Publishable</span>';
                        return $status;
                    }

                    // Next: check balance if marks are complete
                    if (isset($model['BALANCE'])) {
                        $balance = $model['BALANCE'];
                        if ($balance > 0) {
                            $status .= ' | <span class="text-danger">Not Publishable</span>';
                        } else {
                            $status .= ' | <span class="text-success">Publishable</span>';
                        }
                    } else {
                        $status .= ' | <span class="text-muted">No Balance Data</span>';
                    }

                    return $status;
                },
            ]
        ];

        echo GridView::widget([
            'id' => 'marksGridView',
            'dataProvider' => $provider,
            'filterModel' => $filterModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fas fa-check"></i> Publish', [
                            'id' => 'publish-marks-btn',
                            'class' => 'btn',
                            'title' => 'Publish marks',
                            'data-marksheetId' => $marksheetId,
                            'data-pjax' => '0',
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
            ],
            'export' => [
                'fontAwesome' => false,
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading' => '<h3 class="panel-title">' . $panelHeading . '</h3>',
            ],
            'persistResize' => false,
            'itemLabelSingle' => 'student',
            'itemLabelPlural' => 'students',
        ]);
        ?>
    </div>

<?php

$publishMarksUrl = Url::to(['/publish-marks/publish']);

$publishMarksJs = <<< JS

const publishMarksUrl = '$publishMarksUrl';

$('#marksGridView-pjax').on('click', '#publish-marks-btn', function(e){
    var btn = $(this);  // Use var instead of const for a broader scope
    var marksheetId = btn.attr('data-marksheetId');
    
    if (!confirm('Publish marks for this marksheet?')) {
        return;
    }
    
    // Disable button and show loading state
    btn.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin"></i> Publishing...')
        .addClass('btn-secondary')
        .removeClass('btn-primary');
    
    // Show a loading message
    var loadingAlert = $('<div class="alert alert-info alert-dismissible fade in" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '<i class="fas fa-spinner fa-spin"></i> Publishing marks, please wait...' +
        '</div>');
    
    $('.publish-marks').prepend(loadingAlert);
    
    $.post(publishMarksUrl, {
        marksheetId: marksheetId
    })
    .done(function(response) {
        // Remove loading alert
        loadingAlert.remove();
        
        // Check if the response indicates success
        if (response && response.success === false) {
            // Handle server-side errors returned as JSON
            const errorMsg = response.message || 'An error occurred while publishing marks';
            showAlert('danger', 'Error', errorMsg);
        } else {
            // Success - could be redirect response or success JSON
            showAlert('success', 'Success', 'Marks published successfully');
            
            // Reload the grid to show updated data
            setTimeout(() => {
                $.pjax.reload({container: '#marksGridView-pjax'});
            }, 1500);
        }
    })
    .fail(function(xhr, status, error) {
        // Remove loading alert
        loadingAlert.remove();
        
        let errorMessage = 'Failed to publish marks';
        
        // Try to parse error response
        try {
            var response = JSON.parse(xhr.responseText);
            if (response && response.message) {
                errorMessage = response.message;
            }
        } catch (e) {
            // If JSON parsing fails, use default error message
            if (xhr.status === 0) {
                errorMessage = 'Network error. Please check your connection.';
            } else if (xhr.status === 404) {
                errorMessage = 'Service not found. Please contact support.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to perform this action.';
            } else {
                errorMessage = 'Error ' + xhr.status + ': ' + (error || 'Unknown error occurred');
            }
        }
        
        showAlert('danger', 'Error', errorMessage);
    })
    .always(function() {
        // Always restore the button state
        btn.prop('disabled', false)
            .html('<i class="fas fa-check"></i> Publish')
            .removeClass('btn-secondary')
            .addClass('btn-primary');
    });
});

// Helper function to show alerts
function showAlert(type, title, message) {
    var alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    
    var alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade in" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '<strong>' + title + ':</strong> ' + message +
        '</div>');
    
    $('.publish-marks').prepend(alert);
    
    // Auto-dismiss success alerts after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            alert.fadeOut();
        }, 5000);
    }
}
JS;
$this->registerJs($publishMarksJs, \yii\web\View::POS_READY);

