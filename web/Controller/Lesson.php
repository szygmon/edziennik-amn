<?php

namespace Controller;

use Model\Object;
use Core\Response;

class Lesson {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;
    private $info = 'brak';

    /**
     * @param \User\Me $Me
     * @param \Doctrine\ORM\EntityManager $em
     */
    function __construct($Me, $em) {
        $this->me = $Me;
        $this->em = $em;
    }

    /**
     * index
     * @Route(/teacher/lesson)
     */
    public function index() {
       
        return array('classes' => '$classes');
    }
    
        /**
     * index
     * @Route(/teacher/lesson/edit)
     */
    public function editLesson() {
        
        return array('classes' => 'aaa');
    }
}
 
