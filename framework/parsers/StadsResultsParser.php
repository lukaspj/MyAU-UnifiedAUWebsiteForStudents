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
    public static function ParseHTML($resultHtml)
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($resultHtml);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $resultRows = $xpath->query("//table[@id='resultTable']/tbody/*");
        $grades = array();
        for ($i = 0; $i < $resultRows->length; $i++) {
            $row = $resultRows->item($i);
            $grades[] = new Grade(self::GetCourseName($row),
                self::GetCourseRatedAt($row),
                self::GetCourseGrades($row),
                self::GetCourseECTS($row));
        }
        $meritRows = $xpath->query("//table[@id='meritTable']/tbody/*");
        $merits = array();
        for ($i = 0; $i < $meritRows->length; $i++) {
            $row = $meritRows->item($i);
            $merits[] = new Grade(self::GetCourseNameForMerit($row),
                self::GetCourseRatedAtForMerit($row),
                self::GetCourseGradesForMerit($row),
                self::GetCourseECTSForMerit($row));
        }
        return array($grades, $merits);
    }

    private static function GetCourseName($row)
    {
        return trim($row->childNodes->item(0)->textContent);
    }

    private static function GetCourseRatedAt($row)
    {
        return trim($row->childNodes->item(2)->textContent);
    }

    private static function GetCourseGrades($row)
    {
        return trim($row->childNodes->item(4)->textContent);
    }

    private static function GetCourseECTS($row)
    {
        return trim($row->childNodes->item(8)->textContent);
    }

    private static function GetCourseNameForMerit($row)
    {
        return trim($row->childNodes->item(0)->textContent);
    }

    private static function GetCourseRatedAtForMerit($row)
    {
        return trim($row->childNodes->item(4)->textContent);
    }

    private static function GetCourseGradesForMerit($row)
    {
        return trim($row->childNodes->item(6)->textContent);
    }

    private static function GetCourseECTSForMerit($row)
    {
        return trim($row->childNodes->item(10)->textContent);
    }
}