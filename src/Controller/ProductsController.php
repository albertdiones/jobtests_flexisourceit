<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
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
                $tenant = $this->tenantService->getTenantById($tenantId);
                if (!$tenant) {
                    $tenant = $this->tenantService->createNewTenant("New Tenant #$tenantId", $tenantId);
                }

                $this->tenantService->setActiveTenant($tenant);
                $templateData['tenant'] = $this->tenantService->getActiveTenant();
                $products = $this->tenantService->getActiveTenantProducts() ?: [];
                $categories  = $this->tenantService->getActiveTenantCategories() ?: [];
                $templateData['categoryProducts'] = [];
                /** @var Categories $category */
                foreach ($categories as $category) {
                    /** @var Products $product */
                    foreach ($products as $product) {
                        $categoryProducts = &$templateData['categoryProducts'][$category->getName()];
                        if ($category == $product->getCategory()) {
                            if (!$categoryProducts) {
                                $categoryProducts = [];
                            }
                            $categoryProducts[] = $product;
                        }
                    }
                }
            }
        }
        catch (\Throwable $e) {
            $templateData['error'] = $e;
        }


        return $this->render('products/index.html.twig', $templateData);
    }
}
