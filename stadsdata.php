<?php
/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/20/16
 * Time: 2:21 PM
 */

include_once "framework/services/StadsService.php";
include_once "framework/parsers/StadsResultsParser.php";
include_once "framework/parsers/StadsStudiesParser.php";

if(!isset($_POST["username"]))
    die("Please post a username");

if(!isset($_POST["password"]))
    die("Please post a password");

$stadsService = new StadsService($_POST["username"], $_POST["password"]);

list($grades, $merits) = StadsResultsParser::ParseHTML($stadsService->GetResultPage());

$studies = StadsStudiesParser::ParseHTML($stadsService->GetStudiesPage());

header('Content-Type: application/json');
echo json_encode(array("grades" => $grades, "merits" => $merits, "studies" => $studies));
