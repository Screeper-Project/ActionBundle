<?php
namespace Screeper\ActionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('screeper:execute_actions')
            ->setDescription('Éxécute les actions en attentes')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $checkConnection = $container->get('screeper.json_api.services.api')->getServerStatus($action->getServerName());

        if($checkConnection) // On vérifie que le serveur est opérationnel
        {
            $action_service = $container->get('screeper.action.services.action');
            $repository = $container
                ->get('doctrine')
                ->getRepository('ScreeperActionBundle:Action');

            // On recherche les commandes à éxécuté
            $results = $repository
                ->createQueryBuilder('a')
                ->where('a.dateExecution <= :date')
                    ->setParameter('date', new \DateTime())
                ->andWhere('a.executed == :executed')
                    ->setParameter('executed', false)
                ->orderBy('a.dateExecution', 'ASC')
                ->getQuery()
                ->getResult();

            foreach($results as $action)
                $action_service->executeAction($action);
        }
    }
}