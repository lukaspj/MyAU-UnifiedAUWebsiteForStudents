<?php
/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/25/16
 * Time: 12:06 PM
 */

require_once __DIR__ . "/../Grade.php";

class StadsResultsParser
{
    public static function ParseHTML($resultHtml) {
        $dom = new DOMDocument;
        $dom->loadHTML($resultHtml);
        $xpath = new DOMXPath($dom);
        $resultRows = $xpath->query("//table[@id='resultTable']/tbody/*");
        print_r($resultRows);
        $grades = array();
        for($i = 0; $i < $resultRows->length; $i++) {
            $row = $resultRows->item($i);
            $grades[] = new Grade(self::GetCourseName($row), self::GetCourseGrades($row), self::GetCourseECTS($row));
        }
        return $grades;
    }

    private static function GetCourseName($row) {
        return $row->childNodes->item(0)->textContent;
    }

    private static function GetCourseGrades($row) {
        return $row->childNodes->item(4)->textContent;
    }

    private static function GetCourseECTS($row) {
        return $row->childNodes->item(8)->textContent;
    }
}