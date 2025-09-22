<?php

namespace app\controllers;

use Exception;
use yii\web\ServerErrorHttpException;

interface BaseControllerInterface
{
    public function init();

    public function getPayrollNo(): ?string;

    public function getDeptCode(): ?string;

    public function getDeptName(): ?string;

    public function getFacCode(): ?string;

    public function getFacName(): ?string;

    public function getAcademicYear(): ?string;

    public function getCurrentAcademicYear(): string;
}
