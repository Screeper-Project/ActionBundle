<?php

namespace Screeper\ActionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Action
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * La commande à éxécuté
     * @var string
     *
     * @ORM\Column(name="command", type="text")
     */
    private $command = '';

    /**
     * Les paramètres de la commande
     * @var \stdClass
     *
     * @ORM\OneToMany(targetEntity="Screeper\ActionBundle\Entity\Parameter", mappedBy="action", cascade={"all"})
     */
    private $parameters = '';

    /**
     * Une description de la commande à éxécuté
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description = '';

    /**
     * Savoir si l'action a tenté d'être éxécuté
     * @var boolean
     *
     * @ORM\Column(name="executed", type="boolean")
     */
    private $executed = false;

    /**
     * La date de création de l'action
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=true)
     */
    private $dateCreation;

    /**
     * La date d'éxécution de l'action
     * @var \DateTime
     *
     * @ORM\Column(name="date_execution", type="datetime")
     */
    private $dateExecution;

    /**
     * A cause du décalage des crons, on note la date réel ou l'action a tenté d'être éxécuté
     * @var \DateTime
     *
     * @ORM\Column(name="date_real_execution", type="datetime", nullable=true)
     */
    private $dateRealExecution = null;

    /**
     * Lors de l'éxécution, permet de savoir si l'action a été un succès ou un echec ("success" ou "error")
     * @var string
     *
     * @ORM\Column(name="execution_status", type="string", length=7, nullable=true)
     */
    private $executionStatus = null;

    /**
     * Attribut désignant si l'action peut être reporté
     * @var integer
     *
     * @ORM\Column(name="reboot", type="boolean")
     */
    private $canBeReboot = true;

    /**
     * Si l'action à déja été reporté, le nombre de reports
     * @var integer
     *
     * @ORM\Column(name="reboot_number", type="integer")
     */
    private $rebootNumber = 0;

    /**
     * Lien vers la dernière instance de l'objet si l'action à déjà été reporté
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="Screeper\ActionBundle\Entity\Action")
     * @ORM\Column(nullable=true)
     */
    private $lastReboot = null;

    /**
     * Le serveur sur lequel s'éxécute l'action
     * @var string
     *
     * @ORM\Column(name="server_name", type="string", length=255)
     */
    private $serverName = 'default';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get parameters as array
     *
     * @return array
     */
    public function getParametersAsArray()
    {
        $parameters = $this->getParameters();
        $array_parameters = array();

        foreach($parameters as $parameter)
            $array_parameters[$parameter->getName()] = array(
                'value' => ($parameter->getPlayer instanceof Player) ? $parameter->getPlayer() : $parameter->getValue(),
                'type' => $parameter->getType()
            );

        return $array_parameters;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set command
     *
     * @param string $command
     * @return Action
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command
     *
     * @return string 
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Action
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set executed
     *
     * @param boolean $executed
     * @return Action
     */
    public function setExecuted($executed)
    {
        $this->executed = $executed;

        return $this;
    }

    /**
     * Get executed
     *
     * @return boolean 
     */
    public function getExecuted()
    {
        return $this->executed;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Action
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateExecution
     *
     * @param \DateTime $dateExecution
     * @return Action
     */
    public function setDateExecution($dateExecution)
    {
        $this->dateExecution = $dateExecution;

        return $this;
    }

    /**
     * Get dateExecution
     *
     * @return \DateTime 
     */
    public function getDateExecution()
    {
        return $this->dateExecution;
    }

    /**
     * Set dateRealExecution
     *
     * @param \DateTime $dateRealExecution
     * @return Action
     */
    public function setDateRealExecution($dateRealExecution)
    {
        $this->dateRealExecution = $dateRealExecution;

        return $this;
    }

    /**
     * Get dateRealExecution
     *
     * @return \DateTime 
     */
    public function getDateRealExecution()
    {
        return $this->dateRealExecution;
    }

    /**
     * Set executionStatus
     *
     * @param string $executionStatus
     * @return Action
     */
    public function setExecutionStatus($executionStatus)
    {
        $this->executionStatus = $executionStatus;

        return $this;
    }

    /**
     * Get executionStatus
     *
     * @return string 
     */
    public function getExecutionStatus()
    {
        return $this->executionStatus;
    }

    /**
     * Set canBeReboot
     *
     * @param boolean $canBeReboot
     * @return Action
     */
    public function setCanBeReboot($canBeReboot)
    {
        $this->canBeReboot = $canBeReboot;

        return $this;
    }

    /**
     * Get canBeReboot
     *
     * @return boolean 
     */
    public function getCanBeReboot()
    {
        return $this->canBeReboot;
    }

    /**
     * Set rebootNumber
     *
     * @param integer $rebootNumber
     * @return Action
     */
    public function setRebootNumber($rebootNumber)
    {
        $this->rebootNumber = $rebootNumber;

        return $this;
    }

    /**
     * Get rebootNumber
     *
     * @return integer 
     */
    public function getRebootNumber()
    {
        return $this->rebootNumber;
    }

    /**
     * Set lastReboot
     *
     * @param string $lastReboot
     * @return Action
     */
    public function setLastReboot($lastReboot)
    {
        $this->lastReboot = $lastReboot;

        return $this;
    }

    /**
     * Get lastReboot
     *
     * @return string 
     */
    public function getLastReboot()
    {
        return $this->lastReboot;
    }

    /**
     * Set serverName
     *
     * @param string $serverName
     * @return Action
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;

        return $this;
    }

    /**
     * Get serverName
     *
     * @return string 
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Add parameters
     *
     * @param \Screeper\ActionBundle\Entity\Parameter $parameters
     * @return Action
     */
    public function addParameter(\Screeper\ActionBundle\Entity\Parameter $parameters)
    {
        $this->parameters[] = $parameters;

        return $this;
    }

    /**
     * Remove parameters
     *
     * @param \Screeper\ActionBundle\Entity\Parameter $parameters
     */
    public function removeParameter(\Screeper\ActionBundle\Entity\Parameter $parameters)
    {
        $this->parameters->removeElement($parameters);
    }

    /**
     * Get parameters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
