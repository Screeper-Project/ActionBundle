<?php

namespace Screeper\ActionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\MappedSuperclass()
 */
class Parameter
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
     * Le nom du paramètre tel qu'il est définit dans la commande non formatée
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * Le type de donnée ('value', 'pseudo' ou 'uuid')
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * La valeur du paramètre, si c'est un joueur relié a un PlayerEntity, alors on laisse vide
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value = null;

    /**
     * Si le joueur est relié a un playerEntity
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Screeper\PlayerBundle\Entity\Player", cascade={"persist"})
     * @ORM\Column(nullable=true)
     */
    private $player = null;

    /**
     * L'action auquelle est attaché le paramètre
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Screeper\ActionBundle\Entity\Action", inversedBy="parameters")
     */
    private $action;

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
     * Set name
     *
     * @param string $name
     * @return Parameter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Parameter
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Parameter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set player
     *
     * @param \Screeper\PlayerBundle\Entity\Player $player
     * @return Parameter
     */
    public function setPlayer(\Screeper\PlayerBundle\Entity\Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \Screeper\PlayerBundle\Entity\Player 
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Set action
     *
     * @param \Screeper\ActionBundle\Entity\Action $action
     * @return Parameter
     */
    public function setAction(\Screeper\ActionBundle\Entity\Action $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \Screeper\ActionBundle\Entity\Action 
     */
    public function getAction()
    {
        return $this->action;
    }
}
