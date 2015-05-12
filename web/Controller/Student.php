<?php

namespace Controller;

use Model\Object;
use Core\Response;

// organizacja szkoły
// klasy, przedmioty, sale lekcyjne, godziny zajęć, plan lekcji
class Student {

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
     * @Route(/student)
     */
    public function index() {


        return array('classes' => $classes);
    }

    /**
     * @Route(/student/ratings/{class})
     */
    public function ratings($class = '') {
        if ($class == '') { // aktualna klasa
            $year = $this->me->getActualYear();
            foreach ($this->me->getModel()->getClass() as $c) {
                if ($c->getYear() == $year)
                    $class = $c;
            }
        } // dodać wcześniejsze////////////////////////////////////////////////////

        foreach ($this->me->getModel()->getRatings() as $r) {
            if ($r->getClass() == $class) {
                if (!isset($subjects[$r->getSubject()->getId()])) {
                    $subjects[$r->getSubject()->getId()] = $r->getSubject();
                }
                $ratings[$r->getSubject()->getId()][$r->getRatingDesc()->getOrderDesc()] = $r;
                //$ratingDescs[$r->getSubject()->getId()][$r->getRatingDesc()->getOrderDesc()] = $r->getRatingDesc();
                if (isset($ratingsSum[$r->getSubject()->getId()])) {
                    $ratingsSum[$r->getSubject()->getId()] += $r->getValue() * $r->getRatingDesc()->getWeight();
                    $counter[$r->getSubject()->getId()] += $r->getRatingDesc()->getWeight();
                } else {
                    $ratingsSum[$r->getSubject()->getId()] = $r->getValue() * $r->getRatingDesc()->getWeight();
                    $counter[$r->getSubject()->getId()] = $r->getRatingDesc()->getWeight();
                }
            }
        }

        foreach ($ratingsSum as $key => $value) { // liczenie średniej
            $ratingsAv[$key] = round($value / $counter[$key], 2);
        }


        return array('ratings' => $ratings, 'subjects' => $subjects, 'ratingsAv' => $ratingsAv, 'class' => $class);
    }

    /**
     * plan lekcji
     * @Route(/student/plan)
     */
    public function plan() {
        $year = $this->me->getActualYear();
        foreach ($this->me->getModel()->getClass() as $c) {
            if ($c->getYear() == $year)
                $class = $c;
        }

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
        $return = array(1 => $s[1], 2 => $s[2], 3 => $s[3], 4 => $s[4], 5 => $s[5], 6 => $s[6], 7 => $s[7], 8 => $s[8]);



        return array('plan' => $return, 'class' => $class);
    }

}
