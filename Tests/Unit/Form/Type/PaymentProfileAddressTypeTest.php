<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Tests\Unit\Form\EventListener\Stub\AddressCountryAndRegionSubscriberStub;
use Oro\Bundle\AddressBundle\Tests\Unit\Form\Type\AddressFormExtensionTestCase;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileAddressType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentProfileAddressTypeTest extends AddressFormExtensionTestCase
{
    private PaymentProfileAddressType $formType;

    protected function setUp(): void
    {
        $this->formType = new PaymentProfileAddressType(
            new AddressCountryAndRegionSubscriberStub(),
            $this->createMock(TranslatorInterface::class)
        );
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension([$this->formType], []),
            $this->getValidatorExtension(true)
        ]);
    }

    /**
     * @dataProvider submitProvider
     */
    public function testSubmit(
        array $submittedData,
        mixed $expectedData,
        mixed $defaultData = null,
        array $options = [],
        bool $isValid = true
    ) {
        $form = $this->factory->create(PaymentProfileAddressType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $country = new Country(self::COUNTRY_WITH_REGION);
        $region = new Region(self::REGION_WITH_COUNTRY);
        $region->setCountry($country);
        $country->addRegion($region);

        $filledDTO = new PaymentProfileAddressDTO();
        $filledDTO->setFirstName('first_stripped');
        $filledDTO->setLastName('last_stripped');
        $filledDTO->setCompany('company_stripped');
        $filledDTO->setStreet('street_stripped');
        $filledDTO->setCountry($country);
        $filledDTO->setRegion($region);
        $filledDTO->setZip('zip_stripped');
        $filledDTO->setCity('city_stripped');
        $filledDTO->setPhoneNumber('phone_stripped');
        $filledDTO->setFaxNumber('fax_stripped');

        $filledDTONoFax = clone $filledDTO;
        $filledDTONoFax->setFaxNumber(null);

        return [
            'empty data not valid' => [
                'submittedData' => [],
                'expectedData' => new PaymentProfileAddressDTO(),
                'defaultData' => null,
                'options' => [],
                'isValid' => false
            ],
            'full data valid' => [
                'submittedData' => [
                    'firstName' => 'first',
                    'lastName' => 'last',
                    'company' => 'company',
                    'street' => 'street',
                    'country' => self::COUNTRY_WITH_REGION,
                    'region' => self::REGION_WITH_COUNTRY,
                    'zip' => 'zip',
                    'city' => 'city',
                    'phoneNumber' => 'phone',
                    'faxNumber' => 'fax',
                ],
                'expectedData' => $filledDTO,
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ],
            'full data valid no fax number' => [
                'submittedData' => [
                    'firstName' => 'first',
                    'lastName' => 'last',
                    'company' => 'company',
                    'street' => 'street',
                    'country' => self::COUNTRY_WITH_REGION,
                    'region' => self::REGION_WITH_COUNTRY,
                    'zip' => 'zip',
                    'city' => 'city',
                    'phoneNumber' => 'phone',
                ],
                'expectedData' => $filledDTONoFax,
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ],
        ];
    }
}
