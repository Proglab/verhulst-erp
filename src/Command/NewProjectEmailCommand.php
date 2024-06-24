<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:new-project-email',
    description: 'Envoyer des mails pour les nouveaux projets'
)]
#[AsCronTask(
    expression: '0 8 * * *',
)]
class NewProjectEmailCommand extends AbstractCommand
{
    public function __construct(private readonly ProjectRepository $projectRepository, private readonly UserRepository $userRepository, private readonly Mailer $mailer, private readonly MailerInterface $mailerInterface)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Envoi de mail pour les nouveaux projets</info>');

        $projects = $this->projectRepository->findAllNewProjects();
        $users = $this->userRepository->findAll();
        $progressBar = new ProgressBar($output, \count($projects) * \count($users));
        foreach ($users as $user) {
            $this->sendEmail($projects, $user);
            $progressBar->advance();
        }
        $progressBar->finish();

        foreach ($projects as $project) {
            $project->setNew(false);
            $this->projectRepository->save($project, true);
        }

        $output->writeln('<info>DONE</info>');
        return Command::SUCCESS;
    }

    private function sendEmail(array $projects, User $user): void
    {
        // Code to send email
        $mail = $this->mailer->sendNewProjectMail($projects, $user);
        $this->mailerInterface->send($mail);
    }
}