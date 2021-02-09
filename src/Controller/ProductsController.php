<?php

namespace App\Controller;

use App\Entity\Tenants;
use App\Service\TenantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{

    private TenantService $tenantService;

    public function __construct(
        TenantService $tenantService
    ) {
        $this->tenantService = $tenantService;
    }


    #[Route('/products', name: 'products')]
    public function index(Request $request): Response
    {
        $templateData = [];
        $tenantId = $request->get('tenant_id');

        try {

            if ($this->tenantService->isValidTenantId($tenantId)) {


                // todo: fetch it from the database
                //$tenant = $this->tenantService->getTenantById($tenantId);
                $tenant = new Tenants();
                $tenant->setId($tenantId);
                $tenant->setTenantName("Dummy tenant #$tenantId");

                $this->tenantService->setActiveTenant($tenant);
                $templateData['tenant'] = $this->tenantService->getActiveTenant();
            }
        }
        catch (\Throwable $e) {
            $templateData['error'] = $e;
        }


        return $this->render('products/index.html.twig', $templateData);
    }
}
