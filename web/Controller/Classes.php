<?php

namespace Controller;

use Model\Object;
use Core\Response;

class Classes {

    /** @var \User\Me */
    protected $me;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

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
     * @Route(/teacher/class/{id})
     * @param \Core\Router $Router
     */
    public function classes($Router, $id = '') {
        if (is_numeric($id)) {
            $class = $this->em->getRepository('Model\\Clas')->find($id);
            //$students = $class->getStudents();
        }
        return array('class' => $class);
    }
}