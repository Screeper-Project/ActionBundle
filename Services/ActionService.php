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
use Doctrine\ORM\Internal\Hydration\HydrationException;
use Screeper\ActionBundle\Entity\Action as ActionEntity;
use Screeper\ActionBundle\Entity\Parameter;
use Screeper\PlayerBundle\Entity\Player;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

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
     * @param $command
     * @param array $parameters
     * @param array $options
     * @return bool
     * @throws \Doctrine\ORM\Internal\Hydration\HydrationException
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function addAction($command, $parameters = array(), $options = array())
    {
        $action = new ActionEntity();

        if(empty($command))
            throw new UnexpectedValueException("Screeper - ActionBundle - La commande à ajouté est vide");

        // Formattage des paramètres
        $parameters_return = $this->formatParameters($parameters, $action, true, false); // On convertis tous les paramètres en objets et on récupère leur nom

        $parameters = $parameters_return[0];
        $parameters_name = $parameters_return[1];

        $parameters_real_name = null;

        // On recherche des paramètres dont on a oublié de spécifié une valeur
        foreach($parameters_real_name as $name)
            if(!in_array($name, $parameters_name))
                throw new HydrationException("Screeper - ActionBundle - Vous avez utilisé le paramètre '".$name."' mais vous n'avez pas spécifié de valeur pour celui-ci");

        // On recherche si on n'a pas également spécifié des paramètre en trop, alors on les supprimes, sinon, on l'ajoute à l'action
        $parameters_fixed = array();
        foreach($parameters_name as $i => $name)
            if(in_array($name, $parameters_real_name));
                $action->addParameter($parameters[$i]);

        // On fini le remplissage de l'action si il n'y a pas eu d'erreur
        $action
            ->setCommand($command)
            ->setDateCreation(new \DateTime())
            ->setDateRealExecution(null)
            ->setExecuted(false)
            ->setExecutionStatus(null)
            ->setReportsNumber(0)
            ->setDescription('')
            ->setDateExecution(new \DateTime());

        if(isset($options['description']))
            $action->setDescription($options['description']);

        if(isset($options['date_execution']))
            if($options['date_execution'] instanceof \DateTime)
                $action->setDateExecution($options['date_execution']);
            else
                throw new InvalidTypeException("Screeper - ActionBundle - L'option 'date_execution' doit être un objet de type Datetime");

        // On persist enfin l'action
        $this->entityManager->persist($action);

        // On flush le tous
        $this->entityManager->flush();

        // On retourne la valeur true si tous c'est bien passé
        return true;
    }

    /**
     * Permet la récupération des paramètres au format objet
     * @param $parameters
     * @param $action
     * @param bool $get_name
     * @param bool $persist
     * @return array
     * @throws \ExpressionErrorException
     * @throws \HydrationException
     */
    public function formatParameters($parameters, $action, $get_name = false, $persist = false)
    {
        $parameters_object = array();

        if($get_name)
            $parameters_name = array();

        foreach($parameters as $name => $array_value)
            if(isset($array_value['value']))
            {
                $value = $array_value['value'];
                $new_parameter = new Parameter();

                $new_parameter
                    ->setName($name)
                    ->setAction($action);

                if($get_name)
                    $parameters_name[] = $name;

                if($value instanceof Player) // Si la valeur est un joueur
                    if(isset($array_value['type']))
                        if(in_array($array_value['type'], array('uuid', 'pseudo'))) // Si le type est de type 'pseudo' ou 'uuid'
                            $new_parameter
                                ->setPlayer($value)
                                ->setValue(null)
                                ->setType($array_value['type']);
                        else
                            throw new \ExpressionErrorException("Screeper - ActionBundle - Le type d'un paramètre contenant des joueurs doit être un pseudo ou un uuid");
                    else
                        throw new \HydrationException("Screeper - ActionBundle - Pour un paramètre faisant référence à un joueur, vous devez spécifié le type de ce paramètre");
                else
                    $new_parameter
                        ->setPlayer(null)
                        ->setValue(strval($value))
                        ->setType('value');

                $parameters_object[] = $new_parameter;

                if($persist) // Si on a demandé a ce que les nouveau paramètre soit persisté
                    $this->entityManager->persist($new_parameter);
            }
            else
                throw new \HydrationException("Screeper - ActionBundle - Les paramètres définis doivent tous avoir une valeur");

        return ($get_name) ? array($parameters_object, $parameters_name) : array($parameters_object);
    }
}