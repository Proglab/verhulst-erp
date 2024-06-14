<?php

declare(strict_types=1);

namespace App\Twig\Components\Projects;

use App\Entity\Project;
use App\Form\Type\NewProjectType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('new_project_form', template: 'app/projects/components/new_form.html.twig')]
class NewProjectComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $locale;

    #[LiveProp(writable: true)]
    public ?string $filename = null;

    #[LiveProp(writable: true)]
    public ?string $errorFile = null;

    public function __construct(private ProjectRepository $projectRepository, private KernelInterface $kernel, private ValidatorInterface $validator)
    {
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        /** @var Project $post */
        $post = $this->getForm()->getData();

        if ($this->filename) {
            $post->setDoc($this->filename);
        }

        $this->projectRepository->save($post, true);

        $this->addFlash('success', 'Project saved!');

        return $this->redirectToRoute('project_details', ['project' => $post->getId()]);
    }

    #[LiveAction]
    public function uploadFile(Request $request)
    {
        $file = $request->files->get('my_file');
        if ($file) {
            $filename = md5(uniqid()) . '.' . $file->guessExtension();

            $constraint = [
                new File([
                    'maxSize' => '2048k',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                    ],
                    'mimeTypesMessage' => 'Ce fichier n\'est pas un PDF valide',
                ]),
            ];

            $validate = $this->validator->validate($file, $constraint);

            if (\count($validate) > 0) {
                $this->errorFile = $validate[0]->getMessage();
                unlink($file->getPathname());
                $this->filename = null;
            } else {
                $file->move($this->kernel->getProjectDir() . \DIRECTORY_SEPARATOR . $this->getParameter('files.projects.upload_dir'), $filename);
                $this->filename = $filename;
                $this->errorFile = null;

                return $filename;
            }
        }
    }

    public function getFileUrl(): string
    {
        return $this->getParameter('files.projects.upload_dir') . '/' . $this->filename;
    }

    public function getFileDetails(): array
    {
        $path = $this->kernel->getProjectDir() . \DIRECTORY_SEPARATOR . $this->getParameter('files.projects.upload_dir') . \DIRECTORY_SEPARATOR . $this->filename;

        return [
            'size' => filesize($path),
            'mime' => mime_content_type($path),
        ];
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewProjectType::class);
    }
}
