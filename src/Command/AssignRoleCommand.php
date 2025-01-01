<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignRoleCommand extends Command
{
    protected static $defaultName = 'app:assign-role';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Assign a role to a user')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addArgument('role', InputArgument::REQUIRED, 'The role to assign (e.g., ROLE_ADMIN)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>User not found!</error>');
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $user->setRoles($roles);
            $this->entityManager->flush();

            $output->writeln('<info>Role assigned successfully!</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<comment>The user already has this role.</comment>');
        return Command::SUCCESS;
    }
}
