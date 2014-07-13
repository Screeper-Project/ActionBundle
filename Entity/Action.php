<?php

namespace Screeper\ActionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table(name="screeper_actions")
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
     * Une description de la commande à éxécuté
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description = '';

    /**
     * Si l'action porte sur un joueur, on le note et on fait un lien avec le joueur en question
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Screeper\PlayerBundle\Entity\Player")
     */
    private $onPlayer;

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
    private $dateRealExecution;

    /**
     * Lors de l'éxécution, permet de savoir si l'action a été un succès ou un echec ("success" ou "error")
     * @var string
     *
     * @ORM\Column(name="execution_status", type="string", length=7, nullable=true)
     */
    private $executionStatus;

    /**
     * Si l'action a déja été reporté, le nombre de reports
     * @var integer
     *
     * @ORM\Column(name="reports_number", type="integer")
     */
    private $reportsNumber = 0;

    /**
     * Si l'action à déja été reporté, l'adresse de sa dernière occurence
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="Screeper\ActionBundle\Entity\Action")
     */
    private $lastReport;
}
