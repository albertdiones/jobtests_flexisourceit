<?php
namespace App\Service;

use App\Entity\Categories;
use App\Entity\Products;
use App\Entity\Tenants;
use App\Entity\Users;
use App\Repository\TenantRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Connection;

class TenantService {

    private ?Tenants $activeTenant;

    const TENANT_DB_PREFIX = 'tenant_';

    public ?EntityManagerInterface $tenantDbEntityManager;
    public ?EntityRepository $productRepository;
    public ?EntityRepository $categoryRepository;

    # Connection: https://stackoverflow.com/questions/6939214/how-do-you-access-doctrine-dbal-in-a-symfony2-service-class
    public function __construct(
        TenantRepository $tenantRepository,
        Connection $connection,
        EntityManagerInterface $entityManager
    ) {
        $this->tenantRepository = $tenantRepository;
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }

    # Since I'm coding on a newly set up machine, I haven't set the auto code-formatting yet
    public function getTenantById( int $id) : ?Tenants {
        return $this->tenantRepository->findOneBy(
            [ 'id' => $id ]
        );
    }

    public function isValidTenantId($id) : bool {
        return filter_var($id,FILTER_VALIDATE_INT);
    }

    # It bothers me that doctrine created "tenants" instead of "tenant" but I'll roll with it for this test
    public function getActiveTenant( ) {
        return $this->activeTenant;
    }

    public function setActiveTenant( Tenants $tenant) {
        $this->activeTenant = $tenant;
        $this->tenantDbEntityManager = $this->createNewEmTenantDb($tenant);
        $this->productRepository = new EntityRepository(
            $this->tenantDbEntityManager,
            $this->tenantDbEntityManager->getClassMetadata(Products::class)
        );
        $this->categoryRepository = new EntityRepository(
            $this->tenantDbEntityManager,
            $this->tenantDbEntityManager->getClassMetadata(Categories::class)
        );
    }

    public function unsetActiveTenant() {
        $this->activeTenant = null;
        $this->tenantDbEntityManager = null;
        $this->productRepository = null;
        $this->categoryRepository = null;
    }

    private function checkTenantDb(Tenants $tenant) {

    }

    private function setTenantDb(Tenants $tenant) {
        // Here I wanna set the session to be the db of that tenant
    }

    # Before calling this function, make sure that the tenant actually does not exist
    public function createNewTenant( string $name, int $id) : ?Tenants {
        $newTenant = new Tenants();

        # I wanted to make this optional, but tenant_db is required
        # I can make tenant to be randomly generated, but I got no time for that
        $newTenant->setId($id);

        $newTenant->setTenantName($name);

        # just to shut up "CURRENT_TIMESTAMP" string issue on mysql
        $newTenant->setDateCreated(new \DateTimeImmutable());
        $newTenant->setLastUpdated(new \DateTimeImmutable());

        $newTenant->setTenantDb(self::TENANT_DB_PREFIX.$id);

        $this->tenantRepository->save($newTenant);

        # Take note that the `tenant_name` field is unique constrained
        $tenant = $this->tenantRepository->findOneBy(['tenantName' => $name]);
        $this->createTenantDb($tenant);

        return $tenant;
    }

    private function createTenantDb(Tenants $tenant) {

        $tenantDb = $tenant->getTenantDb();

        # https://stackoverflow.com/questions/36306923/dynamic-databases-and-schema-creation-in-symfony-doctrine
        $this->connection->getSchemaManager()->createDatabase($tenantDb);
        $newEm = $this->createNewEmTenantDb($tenant);
        $classMetas = [
            $this->entityManager->getClassMetadata(Products::class),
            $this->entityManager->getClassMetadata(Categories::class),
        ];

        $tool = new SchemaTool($newEm);
        $tool->createSchema($classMetas);
    }

    private function createNewEmTenantDb( Tenants $tenant ) {

        # For additional security you can create a new mysql user for each tenant or user
        # But I don't have time for that
        $params = $this->connection->getParams();
        $tenantDbParameters = array(
            'driver' => $params['driver'],
            'host' => $params['host'],
            'user' => $params['user'],
            'dbname' => $tenant->getTenantDb()
        );
        return EntityManager::create($tenantDbParameters,$this->entityManager->getConfiguration(), $this->entityManager->getEventManager());
    }

    public function getActiveTenantProducts() {
        return $this->productRepository->findBy(['enabled' => 1]);
    }
}