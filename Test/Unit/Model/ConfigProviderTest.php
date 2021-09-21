<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Model;

use JsonException;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Model\ConfigProvider;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var PaymentMethods|MockObject
     */
    private $paymentMethodHelperMock;

    /**
     * @var PaymentMethodInterface|MockObject
     */
    private $paymentMethodMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {

        $this->paymentMethodHelperMock = $this->createMock(PaymentMethods::class);
        $this->paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $logMock = $this->createMock(Log::class);
        $storeManagerInterfaceMock = $this->createMock(StoreManagerInterface::class);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeManagerInterfaceMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getCode')->willReturn('SE');

        $this->configProvider = new ConfigProvider(
            $logMock,
            $this->paymentMethodHelperMock,
            $storeManagerInterfaceMock
        );
    }

    /**
     * Assert that mapPaymentMethod works when provided with an object instance
     * containing raw data.
     *
     * @return void
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testMapPaymentMethodWithRawData(): void
    {
        $raw = ['type' => 'card', 'specificType' => 'visa'];
        $expected = [
            'code' => 'invoice',
            'title' => 'Faktura',
            'maxOrderTotal' => 505.12,
            'sortOrder' => 10,
            'type' => 'card',
            'specificType' => 'visa',
            'customerType' => []
        ];

        $this->paymentMethodHelperMock->expects(self::once())
            ->method('getRaw')
            ->willReturn($raw);

        $this->paymentMethodMock->expects(self::once())
            ->method('getCode')
            ->willReturn($expected['code']);
        $this->paymentMethodMock->expects(self::once())
            ->method('getTitle')
            ->willReturn($expected['title']);
        $this->paymentMethodMock->expects(self::once())
            ->method('getMaxOrderTotal')
            ->willReturn($expected['maxOrderTotal']);
        $this->paymentMethodMock->expects(self::once())
            ->method('getSortOrder')
            ->willReturn(10);

        $actual = $this->getMapPaymentMethodMethod()->invoke(
            $this->configProvider,
            $this->paymentMethodMock
        );

        static::assertSame($expected, $actual);
    }

    /**
     * Assert that mapPaymentMethod works when provided with an object instance
     * without raw data.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testMapPaymentMethodWithoutRawData(): void
    {
        $expected = [
            'code' => 'partpayment_nisse_1',
            'title' => 'Great partpayment',
            'maxOrderTotal' => 34534.00,
            'sortOrder' => 10,
            'type' => '',
            'specificType' => '',
            'customerType' => []
        ];

        $this->paymentMethodMock->expects(self::once())
            ->method('getCode')
            ->willReturn($expected['code']);
        $this->paymentMethodMock->expects(self::once())
            ->method('getTitle')
            ->willReturn($expected['title']);
        $this->paymentMethodMock->expects(self::once())
            ->method('getMaxOrderTotal')
            ->willReturn($expected['maxOrderTotal']);
        $this->paymentMethodMock->expects(self::once())
            ->method('getSortOrder')
            ->willReturn(10);

        $actual = $this->getMapPaymentMethodMethod()->invoke(
            $this->configProvider,
            $this->paymentMethodMock
        );

        static::assertSame($expected, $actual);
    }

    /**
     * Test that the getConfig method converted PaymentMethod model instances
     * to an anonymous array.
     *
     * @throws JsonException
     * @throws ValidatorException
     */
    public function testGetConfigResult(): void
    {
        // Data which should be generated by the getConfig method.
        $data = [
            [
                'code' => 'partpayment',
                'title' => 'Delbetalning',
                'maxOrderTotal' => 543.00,
                'sortOrder' => 12,
                'type' => '',
                'specificType' => '',
                'customerType' => []
            ],
            [
                'code' => 'some_method_12314',
                'title' => 'Some method',
                'maxOrderTotal' => 6054.20,
                'sortOrder' => 13,
                'type' => 'resursCard',
                'specificType' => 'internal',
                'customerType' => []
            ]
        ];

        $expected = [
            'payment' => [
                'resursbank_simplified' => [
                    'methods' => $data
                ]
            ]
        ];

        // Create mocked PaymentMethod model instances, utilising $data.
        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName'])
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resourceCollectionMock = $this->createMock(AbstractDb::class);

        $method1 = new PaymentMethod(
            $contextMock,
            $registryMock,
            $resourceMock,
            $resourceCollectionMock,
            $data[0]
        );
        $method1->setMaxOrderTotal($data[0]['maxOrderTotal'])
            ->setSortOrder($data[0]['sortOrder']);

        $method2 = new PaymentMethod(
            $contextMock,
            $registryMock,
            $resourceMock,
            $resourceCollectionMock,
            $data[1]
        );
        $method2
            ->setMaxOrderTotal($data[1]['maxOrderTotal'])
            ->setSortOrder($data[1]['sortOrder'])
            ->setRaw(json_encode(
                ['type' => 'resursCard', 'specificType' => 'internal'],
                JSON_THROW_ON_ERROR
            ));

        // Mock response from method that collects payment methods from DB.
        $this->paymentMethodHelperMock
            ->expects(static::once())
            ->method('getMethodsByCredentials')
            ->willReturn([$method1, $method2]);

        $this->paymentMethodHelperMock
            ->expects(static::exactly(2))
            ->method('getRaw')
            ->willReturnOnConsecutiveCalls([], ['type' => 'resursCard', 'specificType' => 'internal']);

        // Assert the value returned by getConfig matches out expectation.
        static::assertSame($expected, $this->configProvider->getConfig());
    }

    /**
     * Retrieve accessible mapPaymentMethod method mock.
     *
     * @return ReflectionMethod
     */
    private function getMapPaymentMethodMethod(): ReflectionMethod
    {
        $obj = new ReflectionObject($this->configProvider);
        $method = $obj->getMethod('mapPaymentMethod');
        $method->setAccessible(true);

        return $method;
    }
}
