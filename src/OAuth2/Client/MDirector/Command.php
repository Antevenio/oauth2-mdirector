<?php
namespace MDOAuth\OAuth2\Client\MDirector;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
    protected $clientFactory;

    public function __construct(Factory $clientFactory, string $name = null)
    {
        $this->clientFactory = $clientFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('oauth2:mdirector')
            ->setDescription('Calls an mdirector api endpoint using OAuth2.')
            ->setHelp('Calls an mdirector api endpoint using OAuth2.')
            ->addArgument(
                'companyId',
                InputArgument::REQUIRED,
                'Company Identifier.'
            )
            ->addArgument(
                'secret',
                InputArgument::REQUIRED,
                'Company Api Secret.'
            )
            ->addArgument(
                'uri',
                InputArgument::REQUIRED,
                'MDirector API endpoint. i.e.: https://api.mdirector.com/api_contact'
            )
            ->addArgument(
                'method',
                InputArgument::REQUIRED,
                'HTTP Method. i.e: POST'
            )
            ->addArgument(
                'parameters',
                InputArgument::REQUIRED,
                'Request parameters in JSON format. i.e.: '.
                '\'{"email":"email@domain.com", "movil":"+34232423422"}\''
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->clientFactory->create(
            $input->getArgument('companyId'),
            $input->getArgument('secret')
        );

        $response = $client->setMethod($input->getArgument('method'))
            ->setUri($input->getArgument('uri'))
            ->setParameters(json_decode($input->getArgument('parameters'), true))
            ->request();

        $output->writeln($response->getBody()->getContents());
    }
}
