<?php

namespace App\Controller;

use App\Repository\SettingRepository;
use App\Service\SettingsBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin/parametres', name: 'app_admin_settings')]
    public function settings(Request $request, SettingRepository $repo, SettingsBag $bag): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_settings', $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }
            $repo->setAll([
                'company_name' => $request->request->getString('company_name', null) ?: null,
                'company_tagline' => $request->request->getString('company_tagline', null) ?: null,
                'company_address' => $request->request->getString('company_address', null) ?: null,
                'company_email' => $request->request->getString('company_email', null) ?: null,
                'company_phone' => $request->request->getString('company_phone', null) ?: null,
                'company_legal' => $request->request->getString('company_legal', null) ?: null,
                'default_vat_rate' => $request->request->getString('default_vat_rate', null) ?: null,
            ]);
            $bag->clear();
            $this->addFlash('success', 'Paramètres enregistrés.');

            return $this->redirectToRoute('app_admin_settings');
        }

        return $this->render('admin/settings.html.twig', [
            'name' => $bag->get('company_name'),
            'tagline' => $bag->get('company_tagline'),
            'address' => $bag->get('company_address'),
            'email' => $bag->get('company_email'),
            'phone' => $bag->get('company_phone'),
            'legal' => $bag->get('company_legal'),
            'vat' => $bag->get('default_vat_rate', '20.00'),
        ]);
    }
}
