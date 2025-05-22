<?php

declare(strict_types=1);

namespace App\Twig\Components\CompanyContact;

use App\Entity\CompanyContact;
use App\Form\Type\CompanyContactType;
use App\Repository\CompanyContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('contact_form', template: 'app/contact/components/form.html.twig')]
class ContactForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $locale;

    #[LiveProp(writable: true)]// , useSerializerForHydration: true
    public ?CompanyContact $contact = null;

    public function __construct(private CompanyContactRepository $contactRepository)
    {
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $this->submitForm();
        /** @var CompanyContact $post */
        $post = $this->getForm()->getData();
        $post->setUpdatedDt(new \DateTime());

        $this->contactRepository->save($post, true);

        $this->addFlash('success', 'Contact saved!');

        return $this->redirectToRoute('contact_details', ['contact' => $post->getId()]);
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CompanyContactType::class, $this->contact);
    }
}
