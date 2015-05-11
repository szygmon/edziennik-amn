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
     * @Route(/teacher/lesson/edit/{id})
     * @param \Core\Router $Router
     */
    public function editLesson($Router, $id = '') {
        if (isset($_POST['save'])) {
            if (is_numeric($id)) { // edycja
                $lesson = $this->em->getRepository('Model\\Lesson')->find($id);
            } else { // nowy
                $lesson = new \Model\Lesson();
                $lesson->setTopic($_POST['topic']);
                $teacher = $this->em->getRepository('\Model\\Teacher')->find($_POST['teacher']);
                $lesson->setTeacher($teacher);
                $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
                $lesson->setClass($class);
                $lesson->setDate(new \DateTime()); /////////////////////////////////////////////////////////////////////////////////
                $lesson->setHour($_POST['hour']);
                $subject = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
                $lesson->setSubject($subject);

                $this->em->persist($lesson);
                $this->em->flush();
                $Router->redirect('Lesson/editLesson', array('id' => $lesson->getId()));
            }
        }

        if (is_numeric($id)) { // edycja
            $lesson = $this->em->getRepository('Model\\Lesson')->find($id);
            
            $ratingDescs = $this->em->getRepository('\Model\\RatingDesc')->findBy(array('class' => $lesson->getClass(), 'subject' => $lesson->getSubject()), array('order' => 'ASC'));
            foreach ($ratingDescs as $rd) {
                $ratingd[$rd->getOrder()] = $rd;
                if ($rd->getRatings()) {
                    foreach ($rd->getRatings() as $r) {
                        $ratings[$rd->getOrder()][$r->getStudent()->getId()] = $r;
                    }
                }
            }
        } else {
            $lesson = null;
            $ratings = null;
            $ratingd = null;
        }
        



        return array('lesson' => $lesson, 'ratingd' => $ratingd, 'ratings' => $ratings);
    }

}
