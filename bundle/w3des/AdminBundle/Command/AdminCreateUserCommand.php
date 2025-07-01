<?php
namespace w3des\AdminBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use w3des\AdminBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminCreateUserCommand extends Command
{

    private $em;
    private $enc;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $encoder)
    {
        parent::__construct();
        $this->em = $em;
        $this->enc = $encoder;
    }

    protected function configure()
    {
        $this->setName('admin:create-user')
            ->setDescription('...')
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail')
            ->addArgument('password', InputArgument::REQUIRED, 'Hasło');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->em;

        $user = $em->getRepository(User::class)->findOneBy([
            'email' => strtolower($input->getArgument('email'))
        ]);
        if ($user) {
            $output->writeln('<error>User already exists!</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($input->getArgument('email'));

        $pass = $this->enc
            ->hashPassword($user, $input->getArgument('password'));
        $user->setPassword($pass);
        $user->setEnabled(true);
        $em->persist($user);
        $em->flush();

        return Command::SUCCESS;
    }
}
