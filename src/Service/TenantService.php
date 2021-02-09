<?php
namespace App\Service;

use App\Entity\Tenants;
use App\Entity\Users;
use App\Repository\TenantRepository;
use Doctrine\Common\Collections\Criteria;

class TenantService {

    private ?Tenants $activeTenant;

    const TENANT_DB_PREFIX = 'tenant_';

    public function __construct(
        TenantRepository $tenantRepository
    ) {
        $this->tenantRepository = $tenantRepository;
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
        $this->checkTenantDb($tenant);
    }

    public function unsetActiveTenant() {
        $this->activeTenant = null;
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
        //$this->createTenantDb($tenant);

        return $tenant;
    }
}