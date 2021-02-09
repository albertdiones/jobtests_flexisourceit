<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
use App\Entity\Tenants;
use App\Form\Type\CategoryForm;
use App\Service\TenantService;
use http\Exception\UnexpectedValueException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
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

            if (!$this->setTenantId($tenantId)) {
                throw new \Exception("Failed to set tenant id");
            }
            $templateData['newCategoryForm'] = $this->createForm(CategoryForm::class,['tenant_id' => $tenantId ],['method' => 'POST'])->createView();
            $templateData['tenant'] = $this->tenantService->getActiveTenant();
            $templateData['categoryProducts'] = $this->getCategoryProducts();
        }
        catch (\Throwable $e) {
            $templateData['error'] = $e;
            $templateData['message'] = $e->getMessage();
            $templateData['message_type'] = 'error';
        }


        return $this->render('products/index.html.twig', $templateData);
    }

    private function getCategoryProducts() {
        $products = $this->tenantService->getActiveTenantProducts() ?: [];
        $categories  = $this->tenantService->getActiveTenantCategories() ?: [];
        $categoriesProducts = [];
        /** @var Categories $category */
        foreach ($categories as $category) {
            /** @var Products $product */
            $categoriesProducts[$category->getName()] = [];
            foreach ($products as $product) {
                if ($category == $product->getCategory()) {
                    $categoriesProducts[$category->getName()][] = $product;
                }
            }
        }
        return $categoriesProducts;
    }

    #[Route('/products/category', name: 'products_new_category',methods:['POST'])]
    public function newCategoryAction(Request $request) {

        try {
            $templateData = [
                'message' => 'Creating new category...',
                'message_type' => 'unknown'
            ];


            # I think there's a formtype method for extracting this, but I'll figure it out later if I still have time
            # I also think I should check the csrf token using the formtype
            $requestCategory = $request->request->get('category');

            if (!$this->setTenantId($requestCategory['tenant_id'])) {
                throw new \Exception("Failed to set tenant id");
            }
            if (!$requestCategory) {
                throw new \UnexpectedValueException("Category data is blank");
            }

            $newCategory = new Categories();
            $newCategory->setLastUpdated(new \DateTimeImmutable());
            $newCategory->setDateCreated(new \DateTimeImmutable());
            $newCategory->setName($requestCategory['name']);
            $this->tenantService->saveCategory($newCategory);
            $templateData['message'] = 'Successfully create new category';
            $templateData['message_type'] = 'success';
        }
        catch (\Exception $e) {
            $templateData['exception'] = $e;
            $templateData['message'] = $e->getMessage();
            $templateData['message_type'] = 'error';
        }


        # Might as well support json early on because I'm gonna make it API ready soon anyway
        if ($this->isJsonRequest($request)) {
            return new JsonResponse([
                "message" => $templateData['message'],
                "message_type" => $templateData['message_type'],
            ],$templateData['message_type'] == 'error' ? 500 : 200);
        }
        else {
            return $this->render('products/index.html.twig', $templateData);
        }
    }

    private function isJsonRequest(Request $request) {
        return in_array('application/json', $request->getAcceptableContentTypes());
    }

    private function setTenantId( $requestTenantId ) {
        if ($this->tenantService->isValidTenantId($requestTenantId)) {

            $tenant = $this->tenantService->getTenantById($requestTenantId);
            if (!$tenant) {
                $tenant = $this->tenantService->createNewTenant("New Tenant #$requestTenantId", $requestTenantId);
            }
            $this->tenantService->setActiveTenant($tenant);
            return true;
        }
        return false;
    }
}
