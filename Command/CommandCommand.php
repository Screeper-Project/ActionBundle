<?php
namespace Screeper\ActionBundle\Command;

use Screeper\ServerBundle\Services\ServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('screeper:execute:command')
            ->setDescription('Execute une commande')
            ->addArgument('action', InputArgument::OPTIONAL, 'Action a efféctuer')
            ->addArgument('server', InputArgument::OPTIONAL, 'Nom du serveur')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = ($input->getArgument('server')) ? $input->getArgument('server') : ServerService::DEFAULT_SERVER_KEY;
        $jsonapi_service = $this->getContainer()->get('screeper.json_api.services.api');

        $checkConnection = $jsonapi_service->getServerStatus($server);

        if($checkConnection) // On vérifie que le serveur est opérationnel
            $jsonapi_service->executeCommand($input->getArgument('action'), $server);
    }
}
