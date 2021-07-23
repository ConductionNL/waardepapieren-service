<?php

namespace App\DataFixtures;

use App\Entity\OrganizationConfig;
use App\Entity\Type;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WaardepapierenFixtures extends Fixture
{
    private $params;
    /**
     * @var CommonGroundService
     */
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public function load(ObjectManager $manager)
    {
        if (
        !$this->params->get('app_build_all_fixtures') &&
        $this->params->get('app_domain') != 'waardepapieren-gemeentehoorn.commonground.nu' && strpos($this->params->get('app_domain'), 'waardepapieren-gemeentehoorn.commonground.nu') == false &&
            $this->params->get('app_domain') != 'zuiddrecht.nl' && strpos($this->params->get('app_domain'), 'zuiddrecht.nl') == false &&
            $this->params->get('app_domain') != 'zuid-drecht.nl' && strpos($this->params->get('app_domain'), 'zuid-drecht.nl') == false
        ) {
            return false;
        }

        // Rotterdam
        $id = Uuid::fromString('4aa687d4-c5bc-42d6-bbc0-ef37d84e690f');
        $hoorn = new OrganizationConfig();
        $hoorn->setName('Config gemeente Hoorn');
        $hoorn->setDescription('Organization config for gemeente Hoorn');
        $hoorn->setRsin('001516814');
        $manager->persist($hoorn);
        $hoorn->setId($id);
        $manager->persist($hoorn);
        $manager->flush();
        $hoorn = $manager->getRepository('App:OrganizationConfig')->findOneBy(['id'=> $id]);

        $type = new Type();
        $type->setName('Akte van geboorte');
        $type->setValue('akte_van_geboorte');
        $type->setOrganizationConfig($hoorn);

        $manager->persist($type);
        $manager->flush();

        $type = new Type();
        $type->setName('Akte van huwelijk');
        $type->setValue('akte_van_huwelijk');
        $type->setOrganizationConfig($hoorn);

        $manager->persist($type);
        $manager->flush();

        $type = new Type();
        $type->setName('Akte van omzetting van een registratie van een partnerschap');
        $type->setValue('akte_van_omzetting_van_een_registratie_van_een_partnerschap');
        $type->setOrganizationConfig($hoorn);

        $manager->persist($type);
        $manager->flush();

        $type = new Type();
        $type->setName('Akte van overlijden');
        $type->setValue('akte_van_overlijden');
        $type->setOrganizationConfig($hoorn);

        $manager->persist($type);
        $manager->flush();

        $type = new Type();
        $type->setName('Akte van registratie van een partnerschap');
        $type->setValue('akte_van_registraite_van_een_partnerschap');
        $type->setOrganizationConfig($hoorn);

        $manager->persist($type);
        $manager->flush();

        $type = new Type();
        $type->setName('Historisch uittreksel basis registratie personen');
        $type->setValue('historisch_uittreksel_basis_registratie_personen');
        $type->setOrganizationConfig($hoorn);

        $type = new Type();
        $type->setName('Uittreksel basis registratie personen');
        $type->setValue('uittreksel_basis_registratie_personen');
        $type->setOrganizationConfig($hoorn);

        $manager->persist($type);
        $manager->flush();
    }
}
