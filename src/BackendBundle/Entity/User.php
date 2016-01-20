<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface, \Serializable {

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
     * Estado del usuario (0 = Inactivo, 1 = Activo, 2 = Bloqueado)
     * @ORM\Column(name="user_status", type="integer", nullable=true)
     */
    protected $status;

    /**
     * Atributo para almacenar temporalmente el archivo de imagen de perfil
     * @Assert\File(mimeTypes={ "image/jpg", "image/jpeg", "image/png" })
     */
    protected $profileImage;
    
    /**
     * Nombre del archivo de la imagen del perfil del usuario
     * @ORM\Column(name="user_profile_image", type="string", nullable=true)
     */
    protected $profileImagePath;

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

    /**
     * Boolean para saber si el usuario ya confirmo su cuenta de usuario o no
     * @ORM\Column(name="user_is_account_confirmed", type="boolean", nullable=true)
     */
    protected $isAccountConfirmed;
    
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

    function getStatus() {
        return $this->status;
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

    function setStatus($status) {
        $this->status = $status;
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

    function getProfileImage() {
        return $this->profileImage;
    }

    function setProfileImage($profileImage) {
        $this->profileImage = $profileImage;
    }
    
    function getProfileImagePath() {
        return $this->profileImagePath;
    }

    function setProfileImagePath($profileImagePath) {
        $this->profileImagePath = $profileImagePath;
    }

    function getIsAccountConfirmed() {
        return $this->isAccountConfirmed;
    }

    function setIsAccountConfirmed($isAccountConfirmed) {
        $this->isAccountConfirmed = $isAccountConfirmed;
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

    public function eraseCredentials() {
        
    }

    public function getRoles() {
        $role = array();
        switch ($this->getStatus()) {
            case self::STATUS_ACTIVE:
                $role = array('ROLE_USER_ACTIVE');
                break;
            case self::STATUS_INACTIVE:
                $role = array('ROLE_USER_INACTIVE');
                break;
            case self::STATUS_LOCKED:
                $role = array('ROLE_USER_LOCKED');
                break;
            default:
                break;
        }
        return $role;
    }

    public function getUsername() {
        return $this->getEmail();
    }

    public function serialize() {
        return serialize(array(
            $this->id,
        ));
    }

    public function unserialize($serialized) {
        list (
                $this->id,
                ) = unserialize($serialized);
    }

    public function getSalt() {
        return '';
    }

}
