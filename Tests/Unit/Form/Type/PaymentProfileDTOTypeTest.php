<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Tests\Unit\Form\EventListener\Stub\AddressCountryAndRegionSubscriberStub;
use Oro\Bundle\AddressBundle\Tests\Unit\Form\Type\AddressFormExtensionTestCase;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardExpirationDateType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileAddressType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileDTOType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileEncodedDataDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentProfileDTOTypeTest extends AddressFormExtensionTestCase
{
    private PaymentProfileDTOType $formType;

    protected function setUp(): void
    {
        $this->formType = new PaymentProfileDTOType();
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension(
                [
                    $this->formType,
                    new CreditCardType(),
                    new CreditCardExpirationDateType(),
                    new PaymentProfileAddressType(
                        new AddressCountryAndRegionSubscriberStub(),
                        $this->createMock(TranslatorInterface::class)
                    )
                ],
                []
            ),
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
        $form = $this->factory->create(PaymentProfileDTOType::class, $defaultData, $options);

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

        $filledAddressDTO = new PaymentProfileAddressDTO();
        $filledAddressDTO->setFirstName('first_stripped');
        $filledAddressDTO->setLastName('last_stripped');
        $filledAddressDTO->setCompany('company_stripped');
        $filledAddressDTO->setStreet('street_stripped');
        $filledAddressDTO->setCountry($country);
        $filledAddressDTO->setRegion($region);
        $filledAddressDTO->setZip('zip_stripped');
        $filledAddressDTO->setCity('city_stripped');
        $filledAddressDTO->setPhoneNumber('phone_stripped');

        $filledEncodedDTO = new PaymentProfileEncodedDataDTO();
        $filledEncodedDTO->setDescriptor('encoded descriptor');
        $filledEncodedDTO->setValue('encoded value');

        $filledMaskedDTO = new PaymentProfileMaskedDataDTO();
        $filledMaskedDTO->setAccountNumber('XXXX1234');
        $filledMaskedDTO->setRoutingNumber('XXXX4321');
        $filledMaskedDTO->setNameOnAccount('first last');

        $filledProfile = new CustomerPaymentProfile();
        $filledProfile->setName('name_stripped');
        $filledProfile->setDefault(true);
        $filledProfile->setLastDigits('9999');

        $filledDTO = new PaymentProfileDTO($filledProfile);
        $filledDTO->setAddress($filledAddressDTO);
        $filledDTO->setEncodedData($filledEncodedDTO);
        $filledDTO->setMaskedData($filledMaskedDTO);

        $emptyDTO =  new PaymentProfileDTO();
        $emptyDTO->setUpdatePaymentData(true);

        return [
            'empty data not valid' => [
                'submittedData' => [],
                'expectedData' => $emptyDTO,
                'defaultData' => null,
                'options' => [],
                'isValid' => false
            ],
            'full data valid' => [
                'submittedData' => [
                    'profile' => [
                        'name' => 'name',
                        'default' => true,
                        'lastDigits' => '9999'
                    ],
                    'address' => [
                        'firstName' => 'first',
                        'lastName' => 'last',
                        'company' => 'company',
                        'street' => 'street',
                        'country' => self::COUNTRY_WITH_REGION,
                        'region' => self::REGION_WITH_COUNTRY,
                        'zip' => 'zip',
                        'city' => 'city',
                        'phoneNumber' => 'phone'
                    ],
                    'encodedData' => [
                        'descriptor' => 'encoded descriptor',
                        'value' => 'encoded value',
                    ],
                    'maskedData' => [
                        'accountNumber' => 'XXXX1234',
                        'routingNumber' => 'XXXX4321',
                        'nameOnAccount' => 'first last'
                    ]
                ],
                'expectedData' => $filledDTO,
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ]
        ];
    }
}
