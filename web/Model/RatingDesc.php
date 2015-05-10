<?php

namespace model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class RatingDesc {

    use \AutoProperty;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="\Model\Clas", inversedBy="ratingDescs")
     * @JoinColumn(nullable=false)
     */
    protected $class;

    /**
     * @ManyToOne(targetEntity="\Model\Subject", inversedBy="ratingDescs")
     * @JoinColumn(nullable=false)
     */
    protected $subject;
    
    /** @OneToMany(targetEntity="\Model\Rating", mappedBy="ratingDesc") */
    protected $ratings;

    /**
     * @Column(type="integer", nullable=false) 
     */
    protected $order;

    /**
     * @Column(type="integer", nullable=false) 
     */
    protected $weight = 1;

    /** @Column(type="string") */
    protected $desc;

    /** @Column(type="string", length=5) */
    protected $shortDesc;

    /** @Column(type="string") */
    protected $color;
    
    public function __construct() {
        $this->ratings = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
