<?php

namespace Model;

/**
 * @Entity
 */
class Student extends User {

    use \AutoProperty;

    /**
     * @Column(type="string", length=45, nullable=true)
     */
    protected $registrationNr;

    /**
     * @Column(type="string", length=11, nullable=true) 
     */
    protected $pesel;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $birthdate;

    /**
     * @Column(type="string", length=45, nullable=true) 
     */
    protected $birthplace;

    /** @OneToMany(targetEntity="\Model\Rating", mappedBy="student") */
    protected $ratings;

    /**
     * @ManyToMany(targetEntity="\Model\Clas", inversedBy="students")
     * @JoinTable(name="students_class")
     */
    protected $class;

    public function addClass(\Model\Clas $class = null) {
        $this->class->add($class);
    }

    public function removeClass(\Model\Clas $class) {
        $this->class->removeElement($class);
    }

    public function __construct() {
        $this->class = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
