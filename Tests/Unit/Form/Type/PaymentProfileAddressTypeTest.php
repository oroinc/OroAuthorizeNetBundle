<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileAddressType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Component\Testing\Unit\AddressFormExtensionTestCase;
use Oro\Component\Testing\Unit\Form\EventListener\Stub\AddressCountryAndRegionSubscriberStub;
use Oro\Component\Testing\Unit\PreloadedExtension;

class PaymentProfileAddressTypeTest extends AddressFormExtensionTestCase
{
    /**
     * @var PaymentProfileAddressType
     */
    protected $formType;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->formType = new PaymentProfileAddressType(new AddressCountryAndRegionSubscriberStub());
        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension(
                [
                    PaymentProfileAddressType::class => $this->formType
                ],
                [
                ]
            ),
            $this->getValidatorExtension(true)
        ]);
    }

    /**
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     * @param array $options
     * @param bool $isValid
     *
     * @dataProvider submitProvider
     */
    public function testSubmit($submittedData, $expectedData, $defaultData = null, $options = [], $isValid = true)
    {
        $form = $this->factory->create(PaymentProfileAddressType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
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
