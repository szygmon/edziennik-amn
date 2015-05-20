<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 */
class Teacher extends \Model\User {

	//use \AutoProperty;
        /** 
         * @Column(type="integer", nullable=true) 
         */
        protected $stopien; // przykład pola jakiegoś, potem się coś doda

        /** @OneToMany(targetEntity="\Model\Plan", mappedBy="teacher") */
        protected $plans;
        
        ///** @OneToMany(targetEntity="\Model\Educator", mappedBy="teacher") */
        //protected $educators;
        
        /** @OneToMany(targetEntity="\Model\Lesson", mappedBy="teacher") */
        protected $lessons;
	
}
