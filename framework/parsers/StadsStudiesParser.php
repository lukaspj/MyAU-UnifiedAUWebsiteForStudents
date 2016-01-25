<?php

/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/25/16
 * Time: 2:24 PM
 */
class StadsStudiesParser
{
    public static function ParseHTML($studiesHTML)
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($studiesHTML);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $resultRows = $xpath->query("//form[@id='form1']/table/*");
        $studies = array();
        for($i = 1; $i < $resultRows->length; $i++) {
            $row = $resultRows->item($i);
            $rowTableRows = $xpath->query(".//td/table/*", $row);
            $studyName = "";
            $studyStatus = "";
            $linesOfStudy = array();
            for($j = 0; $j < $rowTableRows->length; $j++) {
                $rowTableRow = $rowTableRows->item($j);
                if($rowTableRow->firstChild->textContent === "Status") {
                    $studyStatus = $rowTableRow->childNodes->item(2)->textContent;
                } elseif($rowTableRow->firstChild->textContent === "Uddannelsens navn") {
                    $studyName = $rowTableRow->childNodes->item(2)->textContent;
                } elseif($rowTableRow->firstChild->textContent === "Studieretning") {
                    $linesOfStudy[] = $rowTableRow->childNodes->item(2)->textContent;
                }
                echo $rowTableRow->firstChild->textContent;
            }
            if(trim($studyName) !== "") {
                $studies[] = array($studyName, $studyStatus, $linesOfStudy);
            }
        }
        return $studies;
    }
}