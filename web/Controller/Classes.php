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

    // lista grup //    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////powtarza się w school zrobić to gdzie indziej
    public function groupList(&$array, $criteria = array('mainGroup' => NULL), $offset = 0, $lvl = 0) {
        while (($groups = $this->em->getRepository('Model\\Group')->findBy($criteria, array('name' => 'ASC'), 1, $offset)) != NULL) {
            if (is_array($groups)) {
                foreach ($groups as $group) {
                    $array[] = array('id' => $group->getId(), 'name' => $group->getName(), 'level' => $lvl);
                    if ($group->getSubGroups() != NULL) {
                        $this->groupList($array, array('mainGroup' => $group->getId()), 0, $lvl + 1);
                    }
                }
            }
            $offset++;
        }
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
        $groups = null;
        $this->groupList($groups);
        return array('class' => $class, 'groups' => $groups);
    }

    /**
     * @Route(/teacher/groups/{id})
     * @param \Core\Router $Router
     */
    public function studentsGroups($Router, $id = '') {
        $student = $this->em->getRepository('Model\\Student')->find($_POST['student']);
        $student->removeAllGroups();
        foreach ($_POST['groups'] as $group) {
            $g = $this->em->getRepository('\Model\\Group')->find($group);
            $student->addGroup($g);
            $this->em->flush(); //////////////////////////////////////////////////////////////////???????????????????????????????????????????
        }
        $Router->redirect('Classes/classes', array('id' => $id));

        return array('class' => '');
    }

}
