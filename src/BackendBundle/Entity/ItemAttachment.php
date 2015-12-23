<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * ItemAttachment
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="item_attachment")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ItemAttachment {

    use Consecutive;

    /**
     * @ORM\Id
     * @ORM\Column(name="atta_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Nombre del archivo adjunto
     * @ORM\Column(name="atta_name", type="string")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Descripcion opcional para el archivo adjunto
     * @ORM\Column(name="atta_description", type="text", nullable=true)
     */
    protected $description;

    /**
     * Ruta en el servidor en donde se encuentra el archivo
     * @ORM\Column(name="atta_file_path", type="string")
     */
    protected $filePath;

    /**
     * Extenson del archivo
     * @ORM\Column(name="atta_file_extension", type="string", nullable=true)
     */
    protected $fileExtension;

    /**
     * Usuario que realiza la subida del archivo
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="atta_user_owner_id", referencedColumnName="user_id")
     */
    protected $userOwner;

    /**
     * Item al cual pertenece el archivo adjunto
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="atta_item_id", referencedColumnName="item_id", nullable=true)
     */
    protected $item;

    /**
     * Fecha en la que se carga el archivo al sistema
     * @ORM\Column(name="atta_upload_date", type="datetime", nullable=true)
     */
    protected $uploadDate;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getFilePath() {
        return $this->filePath;
    }

    function getFileExtension() {
        return $this->fileExtension;
    }

    function getUserOwner() {
        return $this->userOwner;
    }

    function getItem() {
        return $this->item;
    }

    function getUploadDate() {
        return $this->uploadDate;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    function setFileExtension($fileExtension) {
        $this->fileExtension = $fileExtension;
    }

    function setUserOwner($userOwner) {
        $this->userOwner = $userOwner;
    }

    function setItem($item) {
        $this->item = $item;
    }

    function setUploadDate($uploadDate) {
        $this->uploadDate = $uploadDate;
    }

    public function __toString() {
        return $this->getName() . "";
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getUploadDate() === null) {
            $this->setUploadDate(Util::getCurrentDate());
        }
    }

}
