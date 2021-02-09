<?php

namespace App\Form\Type;

use App\Service\TenantService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

# https://symfony.com/doc/current/form/create_custom_field_type.html
class ProductForm extends AbstractType
{

    /**
     * @var Router
     */
    private $router;
    private $tenantService;


    public function __construct(UrlGeneratorInterface $router, TenantService $tenantService)
    {
        $this->router = $router;
        # I need tenant service here because I need to get the categories available to the current tenant
        $this->tenantService = $tenantService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $this->tenantService->getActiveTenantCategories();
        $categoryChoices = [];

        foreach ($categories as $category) {
            $categoryChoices[$category->getName()] = $category->getId();
        }
        $builder
            ->setAction($this->router->generate('products_new'))
            ->add('tenant_id', HiddenType::class, ['data' => $this->tenantService->getActiveTenant()->getId()])
            ->add('name', TextType::class)
            ->add('category_id', ChoiceType::class, ['choices' => $categoryChoices])
            ->add('price', NumberType::class);

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product';
    }
}