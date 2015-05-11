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

        // dodawanie opisu oceny
        if (isset($_POST['saveRatingDesc'])) {
            $class = $this->em->getRepository('\Model\\Clas')->find($_POST['class']);
            $subject = $this->em->getRepository('\Model\\Subject')->find($_POST['subject']);
            $orderDesc = $_POST['orderDesc'];

            // sprawdzenie czy jest takie
            $rd = $this->em->getRepository('\Model\\RatingDesc')->findBy(array(
                'class' => $class,
                'subject' => $subject,
                'orderDesc' => $orderDesc
            ));

            if ($rd) { // edycja
                $rd[0]->setDescription($_POST['desc']);
                $rd[0]->setShortDesc($_POST['shortDesc']);
                $rd[0]->setWeight($_POST['weight']);
                $rd[0]->setColor($_POST['color']);
                $this->em->flush();
                $Router->redirect('Lesson/editLesson', array('id' => $_POST['lesson']));
            } else { // dodanie nowego
                $rd = new \Model\RatingDesc();

                $rd->setDescription($_POST['desc']);
                $rd->setShortDesc($_POST['shortDesc']);
                $rd->setWeight($_POST['weight']);
                $rd->setClass($class);
                $rd->setSubject($subject);
                $rd->setOrderDesc($orderDesc);
                $rd->setColor($_POST['color']);

                $this->em->persist($rd);
                $this->em->flush();
                $Router->redirect('Lesson/editLesson', array('id' => $_POST['lesson']));
            }
        }

        if (isset($_POST['saveRatings'])) { // zapisywanie ocen
            if (!is_numeric($id))
                die('ERROR!!!');

            $lesson = $this->em->getRepository('\Model\\Lesson')->find($id);
            $students = $lesson->getClass()->getStudents();
            for ($i = 1; $i < 21; $i++) {
                $rdesc = $this->em->getRepository('\Model\\RatingDesc')->findBy(array(
                    'class' => $lesson->getClass(),
                    'subject' => $lesson->getSubject(),
                    'orderDesc' => $i
                ));
                if ($rdesc) {
                    foreach ($students as $student) {
                        $find = false;
                        foreach ($rdesc[0]->getRatings() as $rating) {
                            if ($rating->getStudent()->getId() == $student->getId()) { // update
                                $find = true;
                                if ($_POST['rat' . $student->getId() . '-' . $i]) {
                                    $rating->setValue($_POST['rat' . $student->getId() . '-' . $i]);
                                    $rating->setDate(new \DateTime());
                                    $this->em->flush(); //////////////////////////////////////////////////może raz na końcu?
                                } else { // usuwanie oceny która została usunięta z dziennika
                                    $this->em->remove($rating);
                                    $this->em->flush();///////////////////////////////////////////////////jw
                                }
                            }
                        }
                        if (!$find) {
                            if ($_POST['rat' . $student->getId() . '-' . $i]) {
                                $rating = new \Model\Rating();
                                $rating->setStudent($student);
                                $rating->setSubject($lesson->getSubject());
                                $rating->setRatingDesc($rdesc[0]);
                                $rating->setClass($lesson->getClass());
                                $rating->setValue($_POST['rat' . $student->getId() . '-' . $i]);
                                $rating->setDate(new \DateTime());

                                $this->em->persist($rating);
                                $this->em->flush(); //////////////////////////////////////////////////może raz na końcu?
                            }
                        }
                    }
                }
            }
            $Router->redirect('Lesson/editLesson', array('id' => $id));
        }

        if (is_numeric($id)) { // edycja lekcji
            $lesson = $this->em->getRepository('Model\\Lesson')->find($id);

            $ratingDescs = $this->em->getRepository('\Model\\RatingDesc')->findBy(array('class' => $lesson->getClass(), 'subject' => $lesson->getSubject()), array('orderDesc' => 'ASC'));
            foreach ($ratingDescs as $rd) {
                $ratingd[$rd->getOrderDesc()] = $rd;
                if ($rd->getRatings()) {
                    foreach ($rd->getRatings() as $r) {
                        $ratings[$r->getStudent()->getId()][$rd->getOrderDesc()] = $r;
                        if (isset($ratingsSum[$r->getStudent()->getId()])) {
                            $ratingsSum[$r->getStudent()->getId()] += $r->getValue() * $rd->getWeight();
                            $counter[$r->getStudent()->getId()] += $rd->getWeight();
                        } else {
                            $ratingsSum[$r->getStudent()->getId()] = $r->getValue() * $rd->getWeight();
                            $counter[$r->getStudent()->getId()] = $rd->getWeight();
                        }
                    }
                }
            }
            foreach ($ratingsSum as $key => $value) { // liczenie średniej
                $ratingsAv[$key] = round($value / $counter[$key], 2);
            }
        } else {
            $lesson = null;
            $ratings = null;
            $ratingd = null;
        }




        return array('lesson' => $lesson, 'ratingd' => $ratingd, 'ratings' => $ratings, 'ratingsAv' => $ratingsAv);
    }

}
