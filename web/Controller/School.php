<?php

namespace Controller;

use Model\Object;
use Core\Response;

// organizacja szkoły
// klasy, przedmioty, sale lekcyjne, godziny zajęć, plan lekcji
class School {

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

    // info do przekazania 
    public function info($inf) {
        if ($inf == 'err') {
            $this->info = array('inf' => 'Taka pozycja już istnieje!', 'col' => 'red');
        } else if ($inf == 'err2') {
            $this->info = array('inf' => 'Nie można usunąć tego semestru!', 'col' => 'red');
        } else if ($inf == 'err3') {
            $this->info = array('inf' => 'Nie można usunąć grupy ponieważ zawiera podgrupy!', 'col' => 'red');
        } else if ($inf == 'added') {
            $this->info = array('inf' => 'Pozycja została dodana', 'col' => 'green');
        } else if ($inf == 'deleted') {
            $this->info = array('inf' => 'Pozycja została usunięta', 'col' => 'green');
        } else if ($inf == 'updt') {
            $this->info = array('inf' => 'Pozycja została zaktualizowana', 'col' => 'green');
        } else {
            $this->info = 'brak';
        }
        return $this->info;
    }

    /**
     * index
     * @Route(/admin/school)
     */
    public function index() {
        return array('info' => $this->info);
    }

    /**
     * Klasy
     * @Route(/admin/school/classes/{action}/{id})
     * @param \Core\Router $Router
     */
    public function classes($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
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
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/classPrepare', array('info' => 'added'));
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
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array(), array('year' => 'ASC', 'name' => 'ASC'));

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classes' => $classes, 'info' => $info);
    }

    /**
     * @Route(/admin/school/classes/prepare/{info})
     */
    public function classPrepare($info = 'brak') {
        $inf = $this->info($info);

        $years = $this->em->getRepository('Model\\Year')->findAll();
        return array('years' => $years, 'info' => $inf);
    }

// grupy //
    /**
     * @Route(/admin/school/groups/{action}/{id})
     * @param \Core\Router $Router
     */
    public function groups($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $group = $this->em->getRepository('\Model\\Group')->find($id);
                $group->setName($_POST['name']);
                if ($_POST['mainGroup'] != 0) {
                    $mainGroup = $this->em->getRepository('\Model\\Group')->find($_POST['mainGroup']);
                    $group->setMainGroup($mainGroup);
                } else
                    $group->setMainGroup(NULL);
                $this->em->flush();
                $this->info('updt');
                // nowa
            } else {
                // sprawdzenie czy już takiej nie ma ?? potrzebne wogóle?? to sprawdzanie??
                $group = new \Model\Group();
                $group->setName($_POST['name']);
                if ($_POST['mainGroup'] != 0) {
                    $mainGroup = $this->em->getRepository('\Model\\Group')->find($_POST['mainGroup']);
                    $group->setMainGroup($mainGroup);
                }
                $this->em->persist($group);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/groupPrepare', array('info' => 'added'));
                $this->info('added');
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

        $groups = null;
        $this->groupList($groups);
        return array('groups' => $groups, 'info' => $info);
    }

    /**
     * @Route(/admin/school/groups/prepare/{info})
     */
    public function groupPrepare($info = 'brak') {
        $inf = $this->info($info);
        $groups = null;
        $this->groupList($groups);
        return array('groups' => $groups, 'info' => $inf);
    }

    /**
     * @Route(/admin/school/groups/edit/{id})
     */
    public function groupEdit($id = '') {
        if (is_numeric($id)) {
            $data = $this->em->getRepository('\Model\\Group')->find($id);
        }
        $groups = null;
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
     * @param \Core\Router $Router
     */
    public function classrooms($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // sprawdzenie czy już takiej nie ma
            $qb = $this->em->getRepository('\Model\\Classroom')->findOneBy(array('name' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $classroom = new \model\Classroom();
                $classroom->setName($_POST['name']);
                $this->em->persist($classroom);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/classroomPrepare', array('info' => 'added'));
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
     * @Route(/admin/school/classrooms/prepare/{info})
     */
    public function classroomPrepare($info = 'brak') {
        $inf = $this->info($info);
        return array('info' => $inf);
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

            $t = $this->em->getRepository('\Model\\Teacher')->find($_POST['teacher']);
            $plan->setTeacher($t);
            $c = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
            $plan->setClass($c);

            $this->em->persist($plan);
            $this->em->flush();
            $this->info('added');
        }

        // lista
        $sem = $this->em->createQueryBuilder()
                ->select('s')
                ->from('\Model\\Semester', 's')
                ->where('s.fromDate <= ?1 AND s.toDate >= ?1')
                ->setParameters(array('1' => new \DateTime()))
                ->getQuery()
                ->getResult();
        foreach ($sem as $s) {
            $year = $s->getYear();
        }
        $classes = $this->em->getRepository('Model\\Clas')->findBy(array('year' => $year), array('name' => 'ASC'));
        $subjects = $this->em->getRepository('Model\\Subject')->findAll();
        $classrooms = $this->em->getRepository('Model\\Classroom')->findAll();
        $groups = null;
        $this->groupList($groups);
        $teachers = $this->em->getRepository('\Model\\Teacher')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('classes' => $classes, 'subjects' => $subjects, 'classrooms' => $classrooms, 'groups' => $groups, 'teachers' => $teachers, 'plan' => $this->getPlan(), 'info' => $info);
    }

    public function getPlan() {
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
                $s[$p->getHour()][$p->getDay()][] = array(
                    'subject' => $p->getSubject(),
                    'classroom' => $p->getClassroom(),
                    'group' => $p->getGroup(),
                    'teacher' => $p->getTeacher()
                );
            }
            $return[$class->getName()] = array(1 => $s[1], 2 => $s[2], 3 => $s[3], 4 => $s[4], 5 => $s[5], 6 => $s[6], 7 => $s[7], 8 => $s[8]);
            unset($s);
        }
        return $return;
    }

// przedmioty //
    /**
     * @Route(/admin/school/subjects/{action}/{id})
     * @param \Core\Router $Router
     */
    public function subjects($Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // sprawdzenie czy już takiego przedmiotu nie ma
            $qb = $this->em->getRepository('\Model\\Subject')->findOneBy(array('subject' => $_POST['name']));
            if ($qb != NULL)
                $this->info('err');
            else {
                $sub = new \Model\Subject();
                $sub->setSubject($_POST['name']);
                $this->em->persist($sub);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/subPrepare', array('info' => 'added'));
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
     * @Route(/admin/school/subjects/prepare/{info})
     */
    public function subPrepare($info = 'brak') {
        $inf = $this->info($info);
        return array('info' => $inf);
    }

    /**
     * semestry
     * @Route(/admin/school/semesters/{action}/{id})
     */
    public function semesters($id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                //$sem = $this->em->getRepository('\Model\\Semester')->find($id);
                $sem = $this->em->getRepository('\Model\\Semester')->find($id);
                $semesters = $this->em->getRepository('\Model\\Semester')->findBy(array('year' => $sem->getYear()), array('semester' => 'ASC'));
                
                $date1 = explode(' - ', $_POST['sem1DateRange']);
                $semesters[0]->setFromDate(new \DateTime($date1[0]));
                $semesters[0]->setToDate(new \DateTime($date1[1]));
                
                $date2 = explode(' - ', $_POST['sem2DateRange']);
                $semesters[1]->setFromDate(new \DateTime($date2[0]));
                $semesters[1]->setToDate(new \DateTime($date2[1]));
                //var_dump('hhhh')       ;
                //die;
                $this->em->flush();

                //$this->info('updt');
                // nowy
            } else {
                //var_dump('szmata'); die;
                $year = new \Model\Year();
                $year->setFromYear((int) $_POST['year']);
                $year->setToYear($_POST['year'] + 1);

                $sem1 = new \Model\Semester();
                $sem1->setSemester(1);
                $date = explode(' - ', $_POST['sem1DateRange']);
                $sem1->setFromDate(new \DateTime($date[0]));
                $sem1->setToDate(new \DateTime($date[1]));
                $sem1->setYear($year);

                $sem2 = new \Model\Semester();
                $sem2->setSemester(2);
                $date = explode(' - ', $_POST['sem2DateRange']);
                $sem2->setFromDate(new \DateTime($date[0]));
                $sem2->setToDate(new \DateTime($date[1]));
                $sem2->setYear($year);

                $this->em->persist($year);
                $this->em->persist($sem1);
                $this->em->persist($sem2);
                $this->em->flush();
                //$this->info('added');
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
     * @Route(/admin/school/semesters/edit/{id})
     */
    public function semestersEdit($id = '') {
        if (is_numeric($id)) {
            \Notify::success('Zaktualizowano semestr!');
            $semester = $this->em->getRepository('\Model\\Semester')->find($id);
            $semesters = $this->em->getRepository('\Model\\Semester')->findBy(array('year' => $semester->getYear()), array('semester' => 'ASC'));
        } else {
            \Notify::success('Dodano semestr!');           
        }
        return array('years' => $this->getYearsList(), 'semesters' => $semesters);
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

// nauczyciele
    /**
     * @Route(/admin/school/teachers/{action}/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function teachers($Me, $Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $user = $this->em->getRepository('\Model\\Teacher')->find($id);
                $user->setEmail($_POST['email']);
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] != '')
                    $user->setPassword($_POST['password']);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/teacherPrepare', array('info' => 'updt'));
                $this->info('updt');
                // nowy
            } else {
                $user = new \Model\Teacher();
                $user->setEmail($_POST['email']);
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] == '')
                    $user->setPassword('qwerty');
                else
                    $user->setPassword($_POST['password']);
                $user->setSchool($Me->getModel()->getSchool());
                $this->em->persist($user);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/teacherPrepare', array('info' => 'added'));
                $this->info('added');
            }
        }

        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $t = $this->em->getRepository('\Model\\Teacher')->find($id); /// dodać jakieś waruki? żeby sie powiązania nie spieprzyły
            $this->em->remove($t);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $teachers = $this->em->getRepository('Model\\Teacher')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('teachers' => $teachers, 'info' => $info);
    }

    /**
     * @Route(/admin/school/teachers/prepare/{info})
     */
    public function teacherPrepare($info = 'brak') {
        if (!is_numeric($info)) {
            $inf = $this->info($info);
            $teacher = 'new';
        } else {
            $inf = 'brak';
            $teacher = $this->em->getRepository('Model\\Teacher')->find($info);
        }

        return array('info' => $inf, 'teacher' => $teacher);
    }

// uczniowie
    /**
     * @Route(/admin/school/students/{action}/{id})
     * @param \User\Me $Me
     * @param \Core\Router $Router
     */
    public function students($Me, $Router, $id = '', $action = '') {
        // dodawanie
        if (isset($_POST['save']) || isset($_POST['saveAndAdd'])) {
            // aktualizacja
            if ($action == 'updt' && is_numeric($id)) {
                $user = $this->em->getRepository('\Model\\Student')->find($id);
                $user->setEmail($_POST['email']);
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] != '')
                    $user->setPassword($_POST['password']);

                $user->setRegistrationNr($_POST['registrationNr']);
                $user->setPesel($_POST['pesel']);
                $user->setBirthdate(new \DateTime($_POST['birthdate']));
                $user->setBirthplace($_POST['birthplace']);

                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/teacherPrepare', array('info' => 'updt'));
                $this->info('updt');
                // nowy
            } else {
                $user = new \Model\Student();
                $user->setEmail($_POST['email']);
                $user->setUsername($_POST['username']);
                $user->setGivenName($_POST['givenName']);
                $user->setFamilyName($_POST['familyName']);
                if ($_POST['password'] == '')
                    $user->setPassword('qwerty');
                else
                    $user->setPassword($_POST['password']);
                $user->setSchool($Me->getModel()->getSchool());
                $user->setRegistrationNr($_POST['registrationNr']);
                $user->setPesel($_POST['pesel']);
                $user->setBirthdate(new \DateTime($_POST['birthdate']));
                $user->setBirthplace($_POST['birthplace']);

                if ($_POST['class'] != 0) {
                    $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
                    $user->addClass($class);
                }

                $this->em->persist($user);
                $this->em->flush();
                if (isset($_POST['saveAndAdd']))
                    $Router->redirect('School/teacherPrepare', array('info' => 'added'));
                $this->info('added');
            }
        }

        // usuwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $t = $this->em->getRepository('\Model\\Student')->find($id); /// dodać jakieś waruki? żeby sie powiązania nie spieprzyły
            $this->em->remove($t);
            $this->em->flush();
            $this->info('deleted');
        }

        // lista
        $students = $this->em->getRepository('Model\\Student')->findAll();

        // czyszczenie infa
        $info = $this->info;
        $this->info = 'brak';

        return array('students' => $students, 'info' => $info);
    }

    /**
     * @Route(/admin/school/students/prepare/{info})
     */
    public function studentPrepare($info = 'brak') {
        if (!is_numeric($info)) {
            $inf = $this->info($info);
            $student = 'new';
        } else {
            $inf = 'brak';
            $student = $this->em->getRepository('Model\\Student')->find($info);
        }

        $years = $this->em->createQueryBuilder()
                ->select('y')
                ->from('\Model\\Year', 'y')
                ->where('y.toYear >= ?1')
                ->setParameter(1, date('Y'))
                ->getQuery()
                ->getResult();
        foreach ($years as $year) {
            $y[] = $year->getId();
        }

        $class = $this->em->createQueryBuilder('Model\\Clas')
                ->select('c')
                ->from('\Model\\Clas', 'c')
                ->where('c.year IN (:arr)')
                ->setParameter('arr', $y)
                ->orderBy('c.name')
                ->getQuery()
                ->getResult();

        return array('info' => $inf, 'student' => $student, 'class' => $class);
    }

}
