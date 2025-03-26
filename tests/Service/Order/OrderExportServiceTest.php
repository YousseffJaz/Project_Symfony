<?php

namespace App\Tests\Service\Order;

use App\Entity\Order;
use App\Entity\LineItem;
use App\Entity\Customer;
use App\Service\Order\OrderExportService;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\PaymentMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class OrderExportServiceTest extends TestCase
{
    private OrderExportService $orderExportService;
    private Environment $twig;
    private Order $order;
    private Customer $customer;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->orderExportService = new OrderExportService($this->twig);
        
        // Création du client
        $this->customer = new Customer();
        $this->customer->setFirstName('John');
        $this->customer->setLastName('Doe');
        
        // Création d'une commande de test
        $this->order = new Order();
        $this->order->setCustomer($this->customer);
        $this->order->setTotal(150.50);
        $this->order->setPaid(100.00);
        $this->order->setStatus(OrderStatus::PARTIAL->value);
        $this->order->setPaymentMethod(PaymentMethod::CARD->value);
        $this->order->setPaymentType(PaymentType::ONLINE->value);
        $this->order->setCreatedAt(new \DateTimeImmutable('2024-03-26 14:00:00'));
        
        // Ajout d'un LineItem
        $lineItem = new LineItem();
        $lineItem->setQuantity(2);
        $lineItem->setPrice(75.25);
        $this->order->addLineItem($lineItem);

        // Utilisation de la réflexion pour définir l'ID
        $reflectionClass = new \ReflectionClass(Order::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->order, 123);
    }

    public function testExportOrderWithInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format non supporté');
        
        $this->orderExportService->exportOrder($this->order, 'invalid');
    }

    public function testExportOrderToCsv(): void
    {
        $response = $this->orderExportService->exportOrder($this->order, 'csv');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertEquals(
            'attachment; filename="commande_123.csv"',
            $response->headers->get('Content-Disposition')
        );
        
        $content = $response->getContent();
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('150,50€', $content);
        $this->assertStringContainsString(OrderStatus::PARTIAL->getLabel(), $content);
        $this->assertStringContainsString(PaymentMethod::CARD->getLabel(), $content);
        $this->assertStringContainsString(PaymentType::ONLINE->getLabel(), $content);
    }

    public function testExportOrderToPdf(): void
    {
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'admin/order/export.html.twig',
                $this->callback(function ($params) {
                    return isset($params['order']) 
                        && $params['order'] === $this->order
                        && isset($params['embedded_styles']);
                })
            )
            ->willReturn('<html><body>Test PDF Content</body></html>');

        $response = $this->orderExportService->exportOrder($this->order, 'pdf');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals(
            'attachment; filename="facture_123.pdf"',
            $response->headers->get('Content-Disposition')
        );
    }

    public function testGeneratePrintView(): void
    {
        $result = $this->orderExportService->generatePrintView($this->order);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('order', $result);
        $this->assertArrayHasKey('lineItems', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('paid', $result);
        $this->assertArrayHasKey('remaining', $result);
        
        $this->assertSame($this->order, $result['order']);
        $this->assertEquals(150.50, $result['total']);
        $this->assertEquals(100.00, $result['paid']);
        $this->assertEquals(50.50, $result['remaining']);
        $this->assertCount(1, $result['lineItems']);
    }
} 