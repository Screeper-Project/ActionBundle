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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $action_service = $container->get('screeper.action.services.action');
        $repository = $container
            ->get('doctrine')
            ->getRepository('ScreeperActionBundle:Action');

        $results = $repository
            ->createQueryBuilder('a')
            ->where('a.dateExecution <= :date')
                ->setParameter('date', new \DateTime())
            ->orderBy('a.dateExecution', 'ASC')
            ->getQuery()
            ->getResult();

        foreach($results as $action)
            $action_service->executeAction($action);
    }
}