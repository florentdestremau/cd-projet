<?php

namespace App\Controller\Admin;

use App\Dto\CompanySettings;
use App\Form\SettingsForm;
use App\Repository\SettingRepository;
use App\Service\SettingsBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/parametres', name: 'app_admin_settings', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
final class SettingsController extends AbstractController
{
    public function __invoke(
        Request $request,
        SettingRepository $repository,
        SettingsBag $bag,
    ): Response {
        $settings = new CompanySettings(
            companyName: $bag->get('company_name'),
            companyTagline: $bag->get('company_tagline') ?: null,
            companyAddress: $bag->get('company_address') ?: null,
            companyEmail: $bag->get('company_email') ?: null,
            companyPhone: $bag->get('company_phone') ?: null,
            companyLegal: $bag->get('company_legal') ?: null,
            defaultVatRate: $bag->get('default_vat_rate', '20.00'),
        );

        $form = $this->createForm(SettingsForm::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->setAll([
                'company_name' => $settings->companyName ?: null,
                'company_tagline' => $settings->companyTagline,
                'company_address' => $settings->companyAddress,
                'company_email' => $settings->companyEmail,
                'company_phone' => $settings->companyPhone,
                'company_legal' => $settings->companyLegal,
                'default_vat_rate' => $settings->defaultVatRate,
            ]);
            $bag->clear();
            $this->addFlash('success', 'Paramètres enregistrés.');

            return $this->redirectToRoute('app_admin_settings');
        }

        return $this->render('admin/settings.html.twig', ['form' => $form]);
    }
}
