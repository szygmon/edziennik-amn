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
        } else if ($inf == 'err2') {
            $info = array('inf' => 'Nie można usunąć tego semestru!', 'col' => 'red');
        } else if ($inf == 'err3') {
            $info = array('inf' => 'Nie można usunąć grupy ponieważ zawiera podgrupy!', 'col' => 'red');
        } else if ($inf == 'added') {
            $info = array('inf' => 'Pozycja została dodana', 'col' => 'green');
        } else if ($inf == 'deleted') {
            $info = array('inf' => 'Pozycja została usunięta', 'col' => 'green');
        } else if ($inf == 'updt') {
            $info = array('inf' => 'Pozycja została zaktualizowana', 'col' => 'green');
        }
        $this->info = $info;
    }

// index //
    /**
     * @Route(/admin/school)
     */
    public function index() {

        return array('info' => $this->info);
    }

// klasy //
    /**
     * @Route(/admin/school/classes/{action}/{id})
     */
    public function classes($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // sprawdzenie czy już takiej nie ma
            $y = $this->em->getRepository('Model\\Year')->find($_POST['year']);
            $qb = $this->em->getRepository('\Model\\Clas')->findOneBy(array('name' => $_POST['name'], 'year' => $y));
            if ($qb != NULL)
                $this->info('err');
            else {
                $year = $this->em->getRepository('Model\\Year')->find($_POST['year']);

                $class = new \model\Clas();
                $class->setName($_POST['name']);
                $class->setYear($year);
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
     * @Route(/admin/school/classes/prepare)
     */
    public function classPrepare() {
        $years = $this->em->getRepository('Model\\Year')->findAll();
        return array('years' => $years);
    }

// grupy //
    /**
     * @Route(/admin/school/groups/{action}/{id})
     */
    public function groups($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $group = $this->em->getRepository('\Model\\Group')->find($id);
                $group->setName($_POST['name']);
                if ($_POST['mainGroup'] != 0) {
                    $mainGroup = $this->em->getRepository('\Model\\Group')->find($_POST['mainGroup']);
                    $group->setMainGroup($mainGroup);
                }
                $this->em->flush();
                $this->info('updt');
                // nowa
            } else {
                // sprawdzenie czy już takiej nie ma
                //$qb = $this->em->getRepository('\Model\\Group')->findOneBy(array('fromTime' => new \DateTime($_POST['fromTime']), 'toTime' => new \DateTime($_POST['toTime'])));
                //if ($qb != NULL)
                //    $this->info('err');
                //else {
                $group = new \Model\Group();
                $group->setName($_POST['name']);
                if ($_POST['mainGroup'] != 0) {
                    $mainGroup = $this->em->getRepository('\Model\\Group')->find($_POST['mainGroup']);
                    $group->setMainGroup($mainGroup);
                }
                $this->em->persist($group);
                $this->em->flush();
                $this->info('added');
                //}
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $group = $this->em->getRepository('\Model\\Group')->find($id);
            if ($this->em->getRepository('\Model\\Group')->findOneBy(array('mainGroup' => $group)) != NULL) {
                $this->info('err3');
            } else {
                $this->em->remove($group);
                $this->em->flush();
                $this->info('deleted');
            }
        }

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        $groups;
        $this->groupList($groups);
        return array('groups' => $groups, 'info' => $info);
    }

    /**
     * @Route(/admin/school/groups/prepare)
     */
    public function groupPrepare() {
        $groups;
        $this->groupList($groups);
        return array('groups' => $groups);
    }

    /**
     * @Route(/admin/school/groups/edit/{id})
     */
    public function groupEdit($id = '') {
        if (is_numeric($id)) {
            $data = $this->em->getRepository('\Model\\Group')->find($id);
        }
        $groups;
        $this->groupList($groups);
        return array('group' => $data, 'groups' => $groups);
    }

// lista grup //    
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

// sale lekcyjne //
    /**
     * @Route(/admin/school/classrooms/{action}/{id})
     */
    public function classrooms($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // sprawdzenie czy już takiej nie ma
            $qb = $this->em->getRepository('\Model\\Classroom')->findOneBy(array('name' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $classroom = new \model\Classroom();
                $classroom->setName($_POST['name']);
                $this->em->persist($classroom);
                $this->em->flush();
                $this->info('added');
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $classroom = $this->em->getRepository('\Model\\Classroom')->find($id);
            $this->em->remove($classroom);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $classrooms = $this->em->getRepository('Model\\Classroom')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classrooms' => $classrooms, 'info' => $info);
    }

    /**
     * @Route(/admin/school/classroomsM)
     */
    public function classroomsM() {
        $classrooms = $this->em->getRepository('Model\\Classroom')->findAll();
        return array('classrooms' => $classrooms, 'info' => 'brak');
    }

    /**
     * @Route(/admin/school/classrooms/prepare)
     */
    public function classroomPrepare() {
        return array('zmienna' => "brak");
    }

// godziny lekcyjne //
    /**
     * @Route(/admin/school/hours/{action}/{id})
     */
    public function hours($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $hour = $this->em->getRepository('\Model\\Hour')->find($id);
                $hour->setFromTime(new \DateTime($_POST['fromTime']));
                $hour->setToTime(new \DateTime($_POST['toTime']));
                $this->em->flush();

                $this->info('updt');
                // nowa
            } else {
                // sprawdzenie czy już takiej nie ma
                $qb = $this->em->getRepository('\Model\\Hour')->findOneBy(array('fromTime' => new \DateTime($_POST['fromTime']), 'toTime' => new \DateTime($_POST['toTime'])));
                if ($qb != NULL)
                    $this->info('err');
                else {
                    $hour = new \model\Hour();
                    $hour->setFromTime(new \DateTime($_POST['fromTime']));
                    $hour->setToTime(new \DateTime($_POST['toTime']));
                    $this->em->persist($hour);
                    $this->em->flush();
                    $this->info('added');
                }
            }
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $hour = $this->em->getRepository('\Model\\Hour')->find($id);
            $this->em->remove($hour);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $hours = $this->em->getRepository('Model\\Hour')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('hours' => $hours, 'info' => $info);
    }

    /**
     * @Route(/admin/school/hours/prepare)
     */
    public function hourPrepare() {
        return array('zmienna' => "brak");
    }

    /**
     * @Route(/admin/school/hours/edit/{id})
     */
    public function hourEdit($id = '') {
        if (is_numeric($id)) {
            $data = $this->em->getRepository('\Model\\Hour')->find($id);
        }

        return array('hour' => $data);
    }

// plan lekcji //
    /**
     * @Route(/admin/school/plans/{action}/{id})
     */
    public function plans($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {

            $plan = new \Model\Plan();
            $date = explode(' - ', $_POST['dateRange']);
            $plan->setFromDate(new \DateTime($date[0]));
            $plan->setToDate(new \DateTime($date[1]));

            $plan->setHour($_POST['hour']);
            $plan->setDay($_POST['day']);
            $s = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
            $plan->setSubject($s);
            if ($_POST['classroom'] != 0) {
                $c = $this->em->getRepository('\Model\\Classroom')->find($_POST['classroom']);
                $plan->setClassroom($c);
            }
            if ($_POST['group'] != 0) {
                $g = $this->em->getRepository('\Model\\Group')->find($_POST['group']);
                $plan->setGroup($g);
            }
            
            //$t = $this->em->getRepository('\Model\\Teacher')->find($_POST['teacher']);
            $plan->setTeacher($t2);
            $c = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
            $plan->setClass($c);


            //$this->em->persist($plan);
            //$this->em->flush();
            $this->info('added');
        }

        // lista
        $sem = $this->em->createQueryBuilder()
                ->select('s')
                ->from('\Model\\Semester', 's')
                ->where('s.fromDate < ?1 AND s.toDate > ?1')
                ->setParameters(array('1' => new \DateTime()))
                ->getQuery()
                ->getResult();
        foreach ($sem as $s) {
            $year = $s->getYear();
        }
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array('year' => $year), array('name' => 'ASC'));
        $subjects = $this->em->getRepository('Model\\Subject')->findAll();
        $classrooms = $this->em->getRepository('Model\\Classroom')->findAll();
        $groups;
        $this->groupList($groups);
        $teachers = $this->em->getRepository('\Model\\Teacher')->findAll();

        // tworzenie danych do wyświetlenia w planie
        $test = array('1a' => array(1 => array(1 => 1, 2 => 2, 3 => 3)), '1b' => array(2 => array('1' => 'stara')));

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classes' => $classes, 'subjects' => $subjects, 'classrooms' => $classrooms, 'groups' => $groups, 'teachers' => $teachers, 'test' => $this->getPlan(), 'info' => $info);
    }

    public function getPlan() {
        $test = array(
            '1a' => array(
                1 => array( //lekcja
                    1 => 'polski', //dzień
                    2 => 'matma',
                    3 => 'przyroda')
            ),
            '1b' => array(
                2 => array(
                    '1' => 'stara')));

        $sem = $this->em->createQueryBuilder()
                ->select('s')
                ->from('\Model\\Semester', 's')
                ->where('s.fromDate < ?1 AND s.toDate > ?1')
                ->setParameters(array('1' => new \DateTime()))
                ->getQuery()
                ->getResult();
        foreach ($sem as $sr) {
            $year = $sr->getYear();
        }
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array('year' => $year), array('name' => 'ASC'));
        foreach ($classes as $class) {
            $plan = $this->em->createQueryBuilder()
                    ->select('p')
                    ->from('\Model\\Plan', 'p')
                    ->where('p.fromDate <= ?1 AND p.toDate >= ?1 AND p.class = ?2')
                    ->orderBy('p.day')
                    ->orderBy('p.hour')
                    ->setParameters(array('1' => new \DateTime(), '2' => $class))
                    ->getQuery()
                    ->getResult();
            foreach ($plan as $p) {
                $s[$p->getHour()][$p->getDay()][] = array($p->getSubject()->getSubject().($p->getClassroom() != NULL ? ' s. '.$p->getClassroom()->getName() : ''), ($p->getGroup() != NULL ? ' ['.$p->getGroup()->getName().']' : ''));
            }
            $return[$class->getName()] = array(1 => $s[1], 2 => $s[2], 3 => $s[3], 4 => $s[4], 5 => $s[5], 6 => $s[6], 7 => $s[7], 8 => $s[8]);
            unset($s);
        }
        //print_r ($return);
        return $return;
    }

// przedmioty //
    /**
     * @Route(/admin/school/subjects/{action}/{id})
     */
    public function subjects($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // sprawdzenie czy już takiego przedmiotu nie ma
            $qb = $this->em->getRepository('\Model\\Subject')->findOneBy(array('subject' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $sub = new \Model\Subject();
                $sub->setSubject($_POST['name']);
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

    /**
     * @Route(/admin/school/subjects/prepare)
     */
    public function subPrepare() {
        return array('zmienna' => "brak");
    }

// semestry //
    /**
     * @Route(/admin/school/semesters/{action}/{id})
     */
    public function semesters($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $sem = $this->em->getRepository('\Model\\Semester')->find($id);
                $sem->setFromDate(new \DateTime($_POST['sem1From']));
                $sem->setToDate(new \DateTime($_POST['sem1To']));
                $this->em->flush();

                $this->info('updt');
                // nowy
            } else {
                $year = new \Model\Year();
                $year->setFromYear((int) $_POST['year']);
                $year->setToYear($_POST['year'] + 1);

                $sem1 = new \Model\Semester();
                $sem1->setSemester(1);
                $sem1->setFromDate(new \DateTime($_POST['sem1From']));
                $sem1->setToDate(new \DateTime($_POST['sem1To']));
                $sem1->setYear($year);

                $sem2 = new \Model\Semester();
                $sem2->setSemester(2);
                $sem2->setFromDate(new \DateTime($_POST['sem2From']));
                $sem2->setToDate(new \DateTime($_POST['sem2To']));
                $sem2->setYear($year);

                $this->em->persist($year);
                $this->em->persist($sem1);
                $this->em->persist($sem2);
                $this->em->flush();
                $this->info('added');
            }
        }

        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $sem = $this->em->getRepository('\Model\\Semester')->find($id);
            $date = new \DateTime('now');
            if ($date->format('Y') < $sem->getYear()->getFromYear()) { // sprawdzenie czy semestr nie jest aktualny albo się zaraz zacznie
                $rem = $this->em->getRepository('\Model\\Semester')->findBy(array('year' => $sem->getYear()));
                $this->em->remove($sem->getYear());
                foreach ($rem as $r) {
                    $this->em->remove($r);
                }
                $this->em->flush();
                $this->info('deleted');
            } else {
                $this->info('err2');
            }
        }

        // lista
        $semesters = $this->em->getRepository('Model\\Semester')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('semesters' => $semesters, 'info' => $info);
    }

    /**
     * @Route(/admin/school/semestersM)
     */
    public function semestersM() {
        $semesters = $this->em->getRepository('Model\\Semester')->findAll();
        return array('semesters' => $semesters, 'info' => 'brak');
    }

    /**
     * @Route(/admin/school/semesters/prepare)
     */
    public function semPrepare() {
        return array('years' => $this->getYearsList());
    }

    /**
     * @Route(/admin/school/semesters/edit/{id})
     */
    public function semEdit($id = '') {
        if (is_numeric($id)) {
            $data = $this->em->getRepository('\Model\\Semester')->find($id);
        }

        return array('year' => $data->getYear()->getFromYear() . '/' . $data->getYear()->getToYear(), 'sem' => $data);
    }

// generator roczników od aktualnego do +6 bez tych co są w bazie
    public function getYearsList() {
        for ($i = date('Y') - 1; $i < date('Y') + 6; $i++) {
            $years[] = array('from' => $i, 'to' => ($i + 1), 'year' => $i . '/' . ($i + 1));
        }

        // wywalanie tych co już są
        $ys = $this->em->getRepository('Model\\Year')->findAll();
        foreach ($ys as $y) {
            $del_val = array('from' => $y->getFromYear(), 'to' => $y->getToYear(), 'year' => $y->getFromYear() . '/' . $y->getToYear());
            if (($key = array_search($del_val, $years)) !== false) {
                unset($years[$key]);
            }
        }

        return $years;
    }

}
