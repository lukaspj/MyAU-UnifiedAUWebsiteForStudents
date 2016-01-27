<?php

/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/25/16
 * Time: 12:48 PM
 */
class Grade
{
    public $courseName;
    public $ratedAt;
    public $grade;
    public $ects;

    function __construct($courseName, $ratedAt, $grade, $ects)
    {
        $this->courseName = $courseName;
        $this->ratedAt = $ratedAt;
        $this->grade = $grade;
        $this->ects = $ects;
    }

    function __toString()
    {
        return "[$this->courseName, $this->grade, $this->ects]";
    }

    /**
     * @return mixed
     */
    public function getCourseName()
    {
        return $this->courseName;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @return mixed
     */
    public function getEcts()
    {
        return $this->ects;
    }

    /**
     * @return mixed
     */
    public function getRatedAt()
    {
        return $this->ratedAt;
    }
}