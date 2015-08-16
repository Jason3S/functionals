<?php
/**
 * Created by PhpStorm.
 * User: jasondent
 * Date: 16/08/2015
 * Time: 13:04
 */

namespace functionals;

function fnStrToLower()   { return function($v) { return \strtolower($v); }; }
function fnStrToUpper()   { return function($v) { return \strtoupper($v); }; }
function fnMbStrToLower() { return function($v) { return \mb_strtolower($v); }; }
function fnMbStrToUpper() { return function($v) { return \mb_strtoupper($v); }; }
