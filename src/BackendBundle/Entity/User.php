<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * User
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User {

    use Consecutive;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_LOCKED = 2;

    /**
     * @ORM\Id
     * @ORM\Column(name="user_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Nombre del usuario
     * @ORM\Column(name="user_name", type="string", length=50, nullable=false)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Apellido del usuario
     * @ORM\Column(name="user_lastname", type="string", length=50, nullable=false)
     * @Assert\NotBlank()
     */
    protected $lastname;

    /**
     * Numero del teléfono celular del usuario
     * @ORM\Column(name="user_cellphone", type="string", nullable=true, length=20)
     */
    protected $cellphone;

    /**
     * Correo electrónico del usuario
     * @ORM\Column(name="user_email", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * Contraseña del usuario
     * @ORM\Column(name="user_password", type="string", nullable=true)
     */
    protected $password;

    /**
     * Pimienta necesaria para configurar la contraseña del usuario
     * @ORM\Column(name="user_salt", type="string", nullable=true)
     */
    protected $salt;

    /**
     * Estado del usuario (0 = Inactivo, 1 = Activo, 2 = Bloqueado)
     * @ORM\Column(name="user_status", type="integer", nullable=true)
     */
    protected $status;

    /**
     * Ruta del archivo de la imagen del perfil del usuario
     * @ORM\Column(name="user_photo_path", type="string", nullable=true)
     */
    protected $photoPath;

    /**
     * Fecha de creacion del usuario en el sistema
     * @ORM\Column(name="user_creation_date", type="datetime", nullable=true)
     */
    protected $creationDate;

    /**
     * Biografia o descripcion del perfil del usuario
     * @ORM\Column(name="user_biography", type="text", nullable=true)
     */
    protected $biography;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getLastname() {
        return $this->lastname;
    }

    function getCellphone() {
        return $this->cellphone;
    }

    function getEmail() {
        return $this->email;
    }

    function getPassword() {
        return $this->password;
    }

    function getSalt() {
        return $this->salt;
    }

    function getStatus() {
        return $this->status;
    }

    function getPhotoPath() {
        return $this->photoPath;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    function setCellphone($cellphone) {
        $this->cellphone = $cellphone;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setSalt($salt) {
        $this->salt = $salt;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setPhotoPath($photoPath) {
        $this->photoPath = $photoPath;
    }

    function getCreationDate() {
        return $this->creationDate;
    }

    function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    function getBiography() {
        return $this->biography;
    }

    function setBiography($biography) {
        $this->biography = $biography;
    }

    public function __toString() {
        return $this->getName() . ' ' . $this->getLastname();
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
