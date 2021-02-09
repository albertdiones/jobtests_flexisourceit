<?php
namespace App\Service;

use App\Entity\Tenants;
use App\Entity\Users;
use App\Repository\TenantRepository;
use Doctrine\Common\Collections\Criteria;

class TenantService {

    private ?Tenants $activeTenant;

    public function __construct(
        TenantRepository $tenantRepository
    ) {
        $this->tenantRepository = $tenantRepository;
    }

    # Since I'm coding on a newly set up machine, I haven't set the auto code-formatting yet
    public function getTenantById( int $id) : Tenants {
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
        // Here I wanna check if the db exists and create it and the base table if not
    }

    private function setTenantDb(Tenants $tenant) {
        // Here I wanna set the session to be the db of that tenant
    }
}