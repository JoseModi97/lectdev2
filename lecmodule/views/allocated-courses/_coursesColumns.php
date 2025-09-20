<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 20-01-2021 09:35:23
 * @modify date 20-01-2021 09:35:23
 * @desc grid columns for
 */

$courseCodeColumn = [
    'attribute' => 'marksheetDef.course.COURSE_CODE',
    'label' => 'CODE'
];

$courseNameColumn = [
    'attribute' => 'marksheetDef.course.COURSE_NAME',
    'label' => 'NAME'
];

$lecutureRoomColumn = [
    'attribute' => 'marksheetDef.EXAM_ROOM',
    'label' => 'ROOM'
];

$lecturePeriodColumn = [
    'label' => 'PERIOD',
    'value' => function($model){
        $fromHr = $model->marksheetDef->FROM_HR;
        $fromMin = $model->marksheetDef->FROM_MIN;
        $toMin = $model->marksheetDef->TO_MIN;
        $toHr = $model->marksheetDef->TO_HR;
        if(strlen($fromHr) === 1) $fromHr = '0'.$fromHr;
        if(strlen($fromMin) === 1) $fromMin = '0'.$fromMin;
        if(strlen($toHr) === 1) $toHr = '0'.$toHr;
        if(strlen($toMin) === 1) $toMin = '0'.$toMin;
        return $fromHr.' : '.$fromMin. ' To '.$toHr.' : '.$toMin;
    }
];

$levelOfStudyColumn = [
    'label' => 'LEVEL',
    'value' => function($model){
        return $model->marksheetDef->semester->LEVEL_OF_STUDY;
    }
];

$sessionTypeColumn = [
    'label' => 'SESSION',
    'value' => function($model){
        $session =  $model->marksheetDef->semester->SESSION_TYPE;
        if(empty($session)){
            return 'NOT SET';
        } else{
            return $session;
        }
    }
];

$semesterCodeColumn = [
    'label' => 'SEMESTER',
    'width' => '20%',
    'value' => function($model){
        $description = $model->marksheetDef->semester->semesterDescription->SEMESTER_DESC;
        $code = $model->marksheetDef->semester->SEMESTER_CODE;
        return $code . ' (' . $description .')';
    }
];

$groupNameColumn = [
    'attribute' => 'marksheetDef.group.GROUP_NAME',
    'label' => 'GROUP'
];

$academicHoursColumn = [
    'attribute' => 'marksheetDef.course.ACADEMIC_HOURS',
    'label' => 'HOURS',
    'value' => function($model){
        if(is_null($model->marksheetDef->course->ACADEMIC_HOURS)){
            return '';
        }else{
            return $model->marksheetDef->course->ACADEMIC_HOURS;
        }
    }
];

$academicYearColumn = [
    'attribute' => 'marksheetDef.semester.ACADEMIC_YEAR',
    'label' => 'YEAR'
];

$degreeNameColumn = [
    'label' => 'DEGREE',
    'value' => function($model){
        $academicYear = $model->marksheetDef->semester->ACADEMIC_YEAR;
        $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;
        return $degreeName. ' ('.$academicYear.')';
    },
    'width' => '310px',
    'group' => true,
    'groupedRow' => true,
    'groupOddCssClass' => 'kv-grouped-row',
    'groupEvenCssClass' => 'kv-grouped-row',
];