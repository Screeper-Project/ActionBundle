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
use Screeper\ServerBundle\Services\ServerService;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class ActionService
{
    protected $container;
    protected $entityManager;

    const ADD_TIME_WHEN_REBOOT = '+10 minutes';

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
     * Permet d'ajouté une action
     * @param $command
     * @param array $parameters
     * @param array $options
     * @param bool $return_action
     * @return bool|ActionEntity
     * @throws \Doctrine\ORM\Internal\Hydration\HydrationException
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function addAction($command, $parameters = array(), $options = array(), $return_action = false)
    {
        $action = new ActionEntity();

        if(empty($command))
            throw new UnexpectedValueException("Screeper - ActionBundle - La commande spécifié est vide");

        // Formatage des paramètres
        $parameters_name = array_key($parameters); // On récupère le nom des paramètres fournis
        $parameters = $this->unserializeParameters($parameters, $action, false); // On convertit tous les paramètres en objets

        // On récupère le nom des paramètres dans la commande
        $parameters_real_name = array();
        preg_match_all ( "#%(.*?)%#", $command, $parameters_real_name);
        $parameters_real_name = $parameters_real_name[1];

        // On recherche des paramètres dont on a oublié de spécifié une valeur
        foreach($parameters_real_name as $name)
            if(!in_array($name, $parameters_name))
                throw new HydrationException("Screeper - ActionBundle - Vous avez utilisé le paramètre '".$name."' mais vous n'avez pas spécifié de valeur pour celui-ci");

        // On recherche si on n'a pas également spécifié des paramètre en trop, alors on les supprimes, sinon, on l'ajoute à l'action
        foreach($parameters_name as $i => $name)
            if(in_array($name, $parameters_real_name));
                $action->addParameter($parameters[$i]);

        // On fini le remplissage de l'action si il n'y a pas eu d'erreur
        $action
            ->setCommand($command)
            ->setDateCreation(new \DateTime())
            ->setDateExecution(new \DateTime())
            ->setServerName(ServerService::DEFAULT_SERVER_NAME);

        // Ajout des options
        if(isset($options['description']))
            $action->setDescription($options['description']);

        if(isset($options['reboot']))
            if(is_bool($options['reboot']))
                $action->setDescription($options['reboot']);
            else
                throw new InvalidTypeException("Screeper - ActionBundle - L'option 'reboot' doit être un booléen");

        if(isset($options['date_execution']))
            if($options['date_execution'] instanceof \DateTime)
                $action->setDateExecution($options['date_execution']);
            else
                throw new InvalidTypeException("Screeper - ActionBundle - L'option 'date_execution' doit être un objet de type Datetime");

        if(isset($options['server'])) // Si l'action s'éxécute sur un serveur autre que "default"
            if(in_array($options['server'], $this->container->get('screeper.server.services.server')->getServersName())) // Si le serveur est enregistré
                $action->setServerName($options['server']);
            else
                throw new UnexpectedValueException("Screeper - ActionBundle - Le serveur spécifié dans la commande : '".$options['server']."', n'existe pas dans la configuration app/config/config.yml");

        // On persist l'action
        $this->entityManager->persist($action);

        // On flush
        $this->entityManager->flush();

        // On retourne l'action ou un booléen si tous c'est bien passé
        return ($return_action) ? $action : true;
    }

    /**
     * Permet la récupération des paramètres au format objet
     * @param $parameters
     * @param $action
     * @return array
     * @throws \ExpressionErrorException
     * @throws \HydrationException
     */
    protected function unserializeParameters($parameters, $action)
    {
        $parameters_object = array();

        foreach($parameters as $name => $array_value)
            if(isset($array_value['value']))
            {
                $value = $array_value['value'];
                $new_parameter = new Parameter();

                $new_parameter
                    ->setName($name)
                    ->setAction($action);

                if($value instanceof Player) // Si la valeur est un joueur
                    if(isset($array_value['type']))
                        if(in_array(strtolower($array_value['type']), array('uuid', 'pseudo'))) // Si le type est de type 'pseudo' ou 'uuid'
                            $new_parameter
                                ->setPlayer($value)
                                ->setValue(null)
                                ->setType(strtolower($array_value['type']));
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
            }
            else
                throw new \HydrationException("Screeper - ActionBundle - Les paramètres définis doivent tous avoir une valeur");

        return $parameters_object;
    }

    /**
     * @param ActionEntity $action
     */
    public function removeAction(ActionEntity $action)
    {
        $this->entityManager->remove($action);
        $this->entityManager->flush();
    }

    /**
     * @param ActionEntity $action
     * @return bool|null
     */
    public function executeAction(ActionEntity $action)
    {
        $json_api = $this->container->get('screeper.json_api.services.api')->getApi($action->getServerName()); // On récupère l'api du serveur

        $command = $action->getCommand();
        $parameters = $action->getParameters();

        // Formatage de la commande avec les paramètres
        $checkConnection = true;
        $playerService = $this->container->get('screeper.player.services.player');

        foreach($parameters as $parameter)
        {
            if($parameter->getPlayer() instanceof Player)
                $checkConnection = ($checkConnection && $playerService->isConnected($parameter->getPlayer())); // On vérifie si tous les joueurs nécéssaire à la commande sont présents, on stocke le résultat dans $checkConnexion

            $command = $this->replaceParameter($parameter, $command);
        }

        if($checkConnection) // Si tous est en ordre au niveau des connexions, on peu éxécuté la commande
        {
            $query = $json_api->call('runConsoleCommand', array($command), $server_name);

            if(isset($query[0]['result']))
                $action
                    ->setDateRealExecution(new \DateTime())
                    ->setExecuted(true)
                    ->setExecutionStatus($query[0]['result']);

            $this->entityManager->persist($action);
            $this->flush();

            // En cas d'erreur, on reporte la commande à la prochaine vérification si cela à été demandé
            if($action->getExecutionStatus() == 'error')
                if($action->getCanBeReboot())
                    $this->rebootAction($action);

            return true;
        }
        else // Si la connexion pose problème, on n'exécute rien et on repporte donc le tous a la prochaine éxécution
            return null;
    }

    /**
     * @param Parameter $parameter
     * @param $command
     * @return mixed
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     */
    protected function replaceParameter(Parameter $parameter, $command)
    {
        if(!($parameter->getPlayer() instanceof Player))
            $value = $parameter->getValue();
        else
            if($parameter->getType() == 'pseudo')
                $value = $parameter->getPlayer()->getLastUsername();
            elseif($parameter->getType() == 'uuid')
                $value = $parameter->getPlayer()->getUuid();
            else
                throw new InvalidTypeException("Screeper - ActionBundle -
                Erreur lors du formatage des paramètres,
                le type d'un paramètre contenant des joueurs doit être un pseudo ou un uuid");

        return str_replace($command, $value, '%'.$parameter->getName().'%');
    }

    /**
     * @param ActionEntity $action
     * @param array $option
     * @param bool $return_new_action
     * @return bool|ActionEntity
     */
    public function rebootAction(ActionEntity $action, $option = array(), $return_new_action = false)
    {
        // On crée une nouvelle action, copie de la première, et on la récupère
        $new_action = $this->addAction(
            $action->getCommand(),
            $action->getParametersAsArray(),
            array(
                'reboot' => $action->getCanBeReboot(),
                'description' => $action->getDescription(),
                'server' => $action->getServerName(),
                'date_execution' => $action->getDateExecution()->modify(ActionService::ADD_TIME_WHEN_REBOOT)
                ),
            true);

        // On met à jour le nombre de reboot de la nouvelle action
        $new_action
            ->setLastReboot($action)
            ->setNbReboot($action->getNbReboot() + 1);

        // On persist le tous
        $this->entityManager->persist($new_action);
        $this->entityManager->flush();

        return ($return_new_action) ? $new_action : true;
    }
}