<?php

namespace model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Lesson {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;
        
        /**
	 * @ManyToOne(targetEntity="\Model\Teacher", inversedBy="lessons")
	 * @JoinColumn(nullable=false)
	 */
	protected $teacher;
        
        /**
	 * @ManyToOne(targetEntity="\Model\Clas", inversedBy="lessons")
	 * @JoinColumn(nullable=false)
	 */
	protected $class;
        
       /**
	 * @Column(type="date", nullable=false)
	 */
	protected $date;
        
        /** 
         * @Column(type="integer", nullable=false) 
         */
	protected $hour;

        /**
	 * @ManyToOne(targetEntity="\Model\Subject", inversedBy="subjects")
	 * @JoinColumn(nullable=false)
	 */
	protected $subject;
        
        /**
	 * @Column(type="string", length=255, nullable=false)
	 */
	protected $topic;

}
