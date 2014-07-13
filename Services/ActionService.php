<?php

namespace Screeper\ActionBundle\Services;

/**
 * @author Graille
 * @version 1.0.0
 * @link http://github.com/Graille
 * @package ACTIONBUNDLE
 * @since 1.0.0
 */


use Doctrine\ORM\EntityManager;
use Screeper\ActionBundle\Entity\Action;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActionService
{
    protected $container;
    protected $entityManager;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $entityManager
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    /**
     * Fonctionnement :
     * $command correspond a la commande a éxécuté, formatter, les paramètres correspondent a des paramètre entre %...%
     * $parameters répertorie la valeur de chaque paramètres de la manière suivante : array('param1' => array("value" => value1), 'param2' => array("value" => value2)....)
     * Si le paramètre est un pseudo ou un UUID, il peut etre référencé a un joueur (si le module PlayerBundle a été activé et configuré) afin d'éviter les problème lié aux changements de pseudo, le paramètre spécifié sera alors de type Player
     * et alors, value sera de la forme array('value' => PlayerEntity, 'type' => 'pseudo' ou 'uuid')
     * $options définie les options de la commande : 'date' : date d'éxécution de la commande (par default : le plus tôt possible)
     *
     */

    public function addAction($command, $parameters = array(), $options = array())
    {
        $action = new Action();

        $action->
    }
}