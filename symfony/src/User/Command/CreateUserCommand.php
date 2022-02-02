<?php

declare(strict_types=1);

namespace App\User\Command;

use App\User\Service\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

class CreateUserCommand extends Command
{
    public function __construct(private UserManager $userManager)
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setName('app:user:create');
        $this
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $questionHelper = $this->getHelper('question');
        Assert::isInstanceOf($questionHelper, QuestionHelper::class);
        $questionEmail = new Question('User email: ', false);
        $questionPassword = new Question('User password: ', false);
        $questionPassword->setHidden(true);
        $questionRole = new Question('User role [ROLE_USER]: ', 'ROLE_USER');
        $email = $questionHelper->ask($input, $output, $questionEmail);
        $password = $questionHelper->ask($input, $output, $questionPassword);
        $role = $questionHelper->ask($input, $output, $questionRole);

        Assert::string($email);
        Assert::string($password);
        Assert::string($role);

        if (! $email || ! $password) {
            $output->writeln('<error>Failed: Enter valid credentials</error>');

            return Command::FAILURE;
        }

        if (
            ! $this->userManager->create(
                $email,
                $password,
                $role
            )
        ) {
            $output->writeln('<error>Failed: User with entered email already exists</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>Success: User with email ' . $email . ' created</info>');

        return Command::SUCCESS;
    }
}
