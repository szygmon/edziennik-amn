<?php

namespace controller;

use Core\BaseController;
use Model\Object;

// organizacja szkoły
// klasy, przedmioty, sale lekcyjne, godziny zajęć, plan lekcji
class School {

    protected $me;

    /** \Doctrine\ORM\EntityManager */
    protected $em;
    private $info = 'brak';

    /**
     * @param type $Me
     * @param \Doctrine\ORM\EntityManager $em
     */
    function __construct($Me, $em) {
        $this->me = $Me;
        $this->em = $em;
    }

     // info do przekazania 
    public function info($inf) {
        if ($inf == 'err') {
            $info = array('inf' => 'Taka pozycja już istnieje!', 'col' => 'red');
        } else if ($inf == 'added') {
            $info = array('inf' => 'Pozycja została dodana', 'col' => 'green');
        } else if ($inf == 'deleted') {
            $info = array('inf' => 'Pozycja została usunięta', 'col' => 'green');
        }
        $this->info = $info;
    }

    /**
     * @Route(/admin/school)
     */
    public function index() {
        return array('info' => $this->info);
    }
    
    /**
     * @Route(/admin/school/classes/{action}/{id})
     */
    public function classes($id = '', $action = '') {
        // zapis nowej
        if (isset($_POST['save'])) {
            // sprawdzenie czy już takiej nie ma
            $qb = $this->em->getRepository('\Model\\Clas')->findOneBy(array('name' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $class = new \model\Clas();
                $class->setName($_POST['name']);

                // Persist czyli podłaczenie do bazy danych obiekt otrzumuje id
                $this->em->persist($class);
                $this->em->flush();
                $this->info('added');
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $class = $this->em->getRepository('\Model\\Clas')->find($id);
            $this->em->remove($class);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $classes = $this->em->getRepository('Model\\Clas')->findAll();
        
        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';
        
        return array('classes' => $classes, 'info' => $info);
    }
       
    /**
     * @Route(/admin/school/classrooms/{action}/{id})
     */
        public function classrooms() {
        
        
        return array('subjects' => 'a');
    }
    
    /**
     * @Route(/admin/school/hours/{action}/{id})
     */
        public function hours() {
        return array('info' => $info);
    }
    
    
    /**
     * @Route(/admin/school/plans/{action}/{id})
     */
        public function plans() {
        return array('info' => $info);
    }
    // lista przedmiotów
    /**
     * @Route(/admin/school/subjects/{action}/{id})
     */
    public function subjects($id = '', $action = '') {
        // zapis nowego przedmiotu
        if (isset($_POST['save'])) {
            // sprawdzenie czy już takiego przedmiotu nie ma
            $qb = $this->em->getRepository('\Model\\Subject')->findOneBy(array('subject' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $sub = new \Model\Subject();
                $sub->setSubject($_POST['name']);

                // Persist czyli podłaczenie do bazy danych obiekt otrzumuje id
                $this->em->persist($sub);
                $this->em->flush();
                $this->info('added');
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $subject = $this->em->getRepository('\Model\\Subject')->find($id);
            $this->em->remove($subject);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $subjects = $this->em->getRepository('Model\\Subject')->findAll();
        
        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';
        
        return array('subjects' => $subjects, 'info' => $info);
    }

// formularz dodawania przedmiotu
    /**
     * @Route(/admin/school/subjects/prepare)
     */
    public function subject_prepare() {
        return array('zmienna' => "brak");
    }

    /**
     * @Route(/admin/school/classes/prepare)
     */
    public function class_prepare() {
        return array('zmienna' => "brak");
    }
    
    
    // generator roczników od aktualnego do +6 bez tych co są w bazie
    public function getYearsList() {
        for ($i = date('Y'); $i < date('Y') + 6; $i++) {
            $years[] = array('from' => $i, 'to' => ($i + 1), 'year' => $i . '/' . ($i + 1));
        }
        
        // wywalanie tych co już są
        $ys = $this->em->getRepository('Model\\Year')->findAll();
        foreach ($ys as $y) {
            $del_val = array('from' => $y->getFrom_year(), 'to' => $y->getTo_year(), 'year' => $y->getFrom_year().'/'.$y->getTo_year());
            if (($key = array_search($del_val, $years)) !== false) {
                unset($years[$key]);
            }
        }

        return $years;
    }
    
    
    /**
     * @Route(/admin/school/semesters/{action}/{id})
     */
    public function semesters($id = '', $action = '') {
        // zapis nowego przedmiotu
        if (isset($_POST['save'])) {
                $year = new \Model\Year();
                $year->setFrom_year((int)$_POST['year']);
                $year->setTo_year($_POST['year'] + 1);
                
                $sem_1 = new \Model\Semester();
                $sem_1->setSemester(1);
                $sem_1->setFrom_date(new \DateTime($_POST['sem_1_from']));
                $sem_1->setTo_date(new \DateTime($_POST['sem_1_to']));
                $sem_1->setYear($year);
                
                $sem_2 = new \Model\Semester();
                $sem_2->setSemester(2);
                $sem_2->setFrom_date(new \DateTime($_POST['sem_2_from']));
                $sem_2->setTo_date(new \DateTime($_POST['sem_2_to']));
                $sem_2->setYear($year);
                
                // Persist czyli podłaczenie do bazy danych obiekt otrzumuje id
                $this->em->persist($year);
                $this->em->persist($sem_1);
                $this->em->persist($sem_2);
                $this->em->flush();
                $this->info('added');
        }

        // ususwanie czy ma być możliwe i kieyd////////////////////////////////////////////////////////////////////////////
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $this->info('deleted');
        }

        // lista
        $semesters = $this->em->getRepository('Model\\Semester')->findAll();
                
        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';
        
        return array('semesters' => $semesters, 'info' => $info);
    }
    
    
    /**
     * @Route(/admin/school/semesters/prepare)
     */
    public function sem_prepare() {
        //usunąć z form dostępne roczniki
        return array('years' => $this->getYearsList());
    }
}
