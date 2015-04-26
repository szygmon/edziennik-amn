<?php

namespace controller;

use Core\BaseController;
use Model\Object;

class Classes {

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
            $info = array('inf' => 'Taka klasa już istnieje!', 'col' => 'red');
        } else if ($inf == 'added') {
            $info = array('inf' => 'Klasa została dodana', 'col' => 'green');
        } else if ($inf == 'deleted') {
            $info = array('inf' => 'Klasa została usunięta', 'col' => 'green');
        }

        $this->info = $info;
    }

    // generator roczników od aktualnego do +6
    public function getYearsList() {
        //$years = array();
        for ($i = date('Y'); $i < date('Y') + 6; $i++) {
            $years[] = $i . '/' . ($i + 1);
        }
        return $years;
    }

    /**
     * @Route(/admin/classes/{action}/{id})
     */
    public function index($id = '', $action = '') {
        /* $qb = $this->em->getRepository('\Model\Clas')->findAll();
          foreach ($qb as $product) {
          echo sprintf("-%s\n", $product->getName());
          } */

        // zapis nowej klasy
        if (isset($_POST['save'])) {
            $this->add();
        }

        // ususwanie
        if ($action == 'del' && is_numeric($id) && $id > 0) {
            $this->del($id);
        }

        // lista klas
        $qb = $this->em->createQueryBuilder()
                        ->select('c')
                        ->from('\Model\Clas', 'c')
                        ->orderBy('c.name')
                        ->getQuery()->getResult(); // dodać paginacje czy coś...

        $info = $this->info;
        $this->info = 'brak';

        return array('classes' => $qb, 'info' => $info);
    }

    // formularz dodawania klasy
    /**
     * @Route(/admin/classes/prepare)
     */
    public function prepare() {
        return array('years' => $this->getYearsList());
    }

    // usuwanie klasy
    public function del($id) {
        $this->em->createQueryBuilder()
                ->delete('\Model\Clas', 'c')
                ->where('c.class_id = ?1')
                ->setParameter(1, $id)
                ->getQuery()
                ->getResult();
        $this->info('deleted');
    }

    // dodanie klasy
    public function add() {
        // sprawdzenie czy już takiej klasy nie ma
        $qb = $this->em->createQueryBuilder()
                        ->select('c')
                        ->from('\Model\Clas', 'c')
                        ->where('c.name= ?1 AND c.year = ?2')
                        ->setParameters(array(1 => $_POST['name'], 2 => $_POST['year']))
                        ->getQuery()->getResult();

        if ($qb != NULL) {
            $this->info('err');
        } else {
            // nowa Encja Clas
            $clas = new \Model\Clas();

            // Persist czyli podłaczenie do bazy danych obiekt otrzumuje id
            $this->em->persist($clas);

            $clas->setName($_POST['name']);
            $clas->setYear($_POST['year']);

            $this->em->flush();

            $this->info('added');
        }
    }

}
