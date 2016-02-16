<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * ItemComment
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="item_comment")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ItemComment {

    use Consecutive;

    /**
     * @ORM\Id
     * @ORM\Column(name="icom_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;


    /**
     * Contenido del comentario
     * @ORM\Column(name="icom_comment", type="text", nullable=true)
     * @Assert\NotBlank()
     */
    protected $comment;

    /**
     * Usuario que realiza la subida del archivo
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="icom_user_owner_id", referencedColumnName="user_id")
     */
    protected $userOwner;

    /**
     * Item al cual pertenece el archivo adjunto
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="icom_item_id", referencedColumnName="item_id", nullable=true, onDelete="CASCADE")
     */
    protected $item;

    /**
     * Fecha en la que se realiza el comentario
     * @ORM\Column(name="icom_creation_date", type="datetime", nullable=true)
     */
    protected $creationDate;

    function getId() {
        return $this->id;
    }

    function getComment() {
        return $this->comment;
    }

    function getUserOwner() {
        return $this->userOwner;
    }

    function getItem() {
        return $this->item;
    }

    function getCreationDate() {
        return $this->creationDate;
    }

    function setComment($comment) {
        $this->comment = $comment;
    }

    function setUserOwner($userOwner) {
        $this->userOwner = $userOwner;
    }

    function setItem($item) {
        $this->item = $item;
    }

    function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    public function __toString() {
        return $this->getComment();
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getCreationDate() === null) {
            $this->setCreationDate(Util::getCurrentDate());
        }
    }

}
