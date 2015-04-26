<?php

namespace controller;

use Core\BaseController;
use Model\Object;

class Subjects {

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
            $info = array('inf' => 'Taki przedmiot już istnieje!', 'col' => 'red');
        } else if ($inf == 'added') {
            $info = array('inf' => 'Przedmiot został dodany', 'col' => 'green');
        } else if ($inf == 'deleted') {
            $info = array('inf' => 'Przedmiot został usunięty', 'col' => 'green');
        }

        $this->info = $info;
    }

    // lista przedmiotów
    /**
     * @Route(/admin/subjects/{action}/{id})
     */
    public function index($id = '', $action = '') {
        /* $qb = $this->em->getRepository('\model\Clas')->findAll();
          foreach ($qb as $product) {
          echo sprintf("-%s\n", $product->getName());
          } */

        // zapis nowego przedmiotu
        if (isset($_POST['save'])) {
            $this->add();
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $this->del($id);
        }
        
        // lista
        $qb = $this->em->createQueryBuilder()
                        ->select('s')
                        ->from('\Model\Subjects', 's')
                        ->getQuery()->getResult(); // dodać paginacje czy coś...

        $info = $this->info;
        $this->info = 'brak';
        
        return array('subjects' => $qb, 'info' => $info);
    }

// formularz dodawania przedmiotu
    /**
     * @Route(/admin/subjects/prepare)
     */
    public function prepare() {
        return array('zmienna' => "brak");
    }

    // dodanie przedmiotu
    public function add() {
        // sprawdzenie czy już takiego przedmiotu nie ma
        $qb = $this->em->createQueryBuilder()
                        ->select('s')
                        ->from('\Model\Subjects', 's')
                        ->where('s.subject = ?1')
                        ->setParameter(1, $_POST['name'])
                        ->getQuery()->getResult();

        if ($qb != NULL) 
            $this->info ('err');
        else {
            // nowa Encja Clas
            $sub = new \Model\Subjects();

            // Persist czyli podłaczenie do bazy danych obiekt otrzumuje id
            $this->em->persist($sub);

            $sub->setSubject($_POST['name']);

            $this->em->flush();
            $this->info('added');
        }
    }

    // usuwanie przedmiotu
    public function del($id) {
        $this->em->createQueryBuilder()
                ->delete('\Model\Subjects', 's')
                ->where('s.subject_id = ?1')
                ->setParameter(1, $id)
                ->getQuery()
                ->getResult();
        $this->info('deleted');
    }

}
