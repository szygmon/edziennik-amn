<?php

namespace model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Classroom {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

        /**
	 * @Column(type="string", length=255, nullable=false)
	 */
	protected $name;
        
        /** @OneToMany(targetEntity="\Model\Plan", mappedBy="Classroom") */
        protected $plans;

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}
       
}
