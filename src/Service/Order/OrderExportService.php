<?php

namespace App\Service\Order;

use App\Entity\Order;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\PaymentMethod;

class OrderExportService
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function exportOrder(Order $order, string $format = 'pdf'): Response
    {
        return match($format) {
            'csv' => $this->exportToCsv($order),
            'pdf' => $this->exportToPdf($order),
            default => throw new \InvalidArgumentException('Format non supporté')
        };
    }

    private function exportToPdf(Order $order): Response
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        // CSS styles for PDF
        $styles = '
        <style>
            body {
                font-family: Helvetica, Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: #fff;
            }
            
            .css-1qm6rmu {
                padding: 2rem;
                background-color: #fff;
            }
            
            .css-1tbjw1x {
                margin-bottom: 2rem;
            }
            
            .css-1j266sj {
                margin-bottom: 2rem;
            }
            
            .css-adnbnt {
                width: 100%;
                border-collapse: collapse;
            }
            
            .css-1tjvop5 td {
                padding: 0.5rem;
                vertical-align: top;
            }
            
            .css-1oaifqe {
                width: 70%;
            }
            
            .css-qja4fv {
                width: 30%;
                text-align: right;
            }
            
            .css-85hqc7 {
                max-width: 200px;
                max-height: 150px;
            }
            
            .css-1wp1anf {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .css-vfx11v {
                margin-bottom: 1rem;
            }
            
            .css-ksw96z {
                font-size: 24px;
                margin: 0;
                color: #333;
            }
            
            .css-1e6vnmf {
                color: #666;
                font-size: 14px;
                margin-bottom: 0.25rem;
            }
            
            .css-1h1mkoa {
                font-size: 16px;
                font-weight: bold;
                color: #333;
            }
            
            .css-blu5nf {
                border: none;
                border-top: 1px solid #eee;
                margin: 2rem 0;
            }
            
            .css-880ie2 {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 2rem;
            }
            
            .css-880ie2 th {
                background-color: #f8f9fa;
                padding: 1rem;
                text-align: left;
                border-bottom: 2px solid #dee2e6;
            }
            
            .css-880ie2 td {
                padding: 1rem;
                border-bottom: 1px solid #dee2e6;
            }
            
            .css-1thp6wa {
                color: #333;
            }
            
            .css-zoqlj {
                text-align: right;
                padding: 0.5rem;
                color: #666;
            }
            
            .css-qewpne {
                text-align: right;
                padding: 0.5rem;
                font-weight: bold;
                color: #333;
            }
            
            .css-1euzpax {
                text-align: right;
                padding: 0.5rem;
                font-size: 18px;
                color: #333;
            }
            
            .css-teyi8j {
                text-align: right;
                padding: 0.5rem;
                font-size: 24px;
                font-weight: bold;
                color: #333;
            }

            .css-mu7w39 {
                color: #dc3545;
            }

            footer {
                margin-top: 100px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
        </style>';

        // Render the template with embedded CSS
        $html = $this->twig->render('admin/order/export.html.twig', [
            'order' => $order,
            'embedded_styles' => $styles
        ]);

        // Create PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        // Generate response
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="facture_' . $order->getId() . '.pdf"');

        return $response;
    }

    private function exportToCsv(Order $order): Response
    {
        $data = [
            'Numéro de commande' => $order->getId(),
            'Date' => $order->getCreatedAt()->format('d/m/Y'),
            'Client' => $order->getFirstname() . ' ' . $order->getLastname(),
            'Total' => number_format($order->getTotal(), 2, ',', ' ') . '€',
            'Statut' => $this->getStatusLabel($order->getStatus()),
            'Mode de paiement' => $this->getPaymentMethodLabel($order->getPaymentMethod() !== null ? (int) $order->getPaymentMethod() : null),
            'Type de paiement' => $this->getPaymentTypeLabel($order->getPaymentType() !== null ? (int) $order->getPaymentType() : null),
        ];

        $csv = '';
        foreach ($data as $key => $value) {
            $csv .= "$key;$value\n";
        }

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="commande_' . $order->getId() . '.csv"');

        return $response;
    }

    public function generatePrintView(Order $order): array
    {
        return [
            'order' => $order,
            'lineItems' => $order->getLineItems(),
            'total' => $order->getTotal(),
            'paid' => $order->getPaid(),
            'remaining' => $order->getTotal() - $order->getPaid()
        ];
    }

    private function getStatusLabel(?int $status): string
    {
        return match ($status) {
            OrderStatus::WAITING->value => OrderStatus::WAITING->getLabel(),
            OrderStatus::PARTIAL->value => OrderStatus::PARTIAL->getLabel(),
            OrderStatus::PAID->value => OrderStatus::PAID->getLabel(),
            OrderStatus::REFUND->value => OrderStatus::REFUND->getLabel(),
            default => 'Inconnu',
        };
    }

    private function getPaymentMethodLabel(?int $method): string
    {
        return match ($method) {
            PaymentMethod::CASH->value => PaymentMethod::CASH->getLabel(),
            PaymentMethod::TRANSCASH->value => PaymentMethod::TRANSCASH->getLabel(),
            PaymentMethod::CARD->value => PaymentMethod::CARD->getLabel(),
            PaymentMethod::PAYPAL->value => PaymentMethod::PAYPAL->getLabel(),
            PaymentMethod::PCS->value => PaymentMethod::PCS->getLabel(),
            PaymentMethod::CHECK->value => PaymentMethod::CHECK->getLabel(),
            PaymentMethod::PAYSAFECARD->value => PaymentMethod::PAYSAFECARD->getLabel(),
            PaymentMethod::BANK->value => PaymentMethod::BANK->getLabel(),
            default => 'Inconnu',
        };
    }

    private function getPaymentTypeLabel(?int $type): string
    {
        return match ($type) {
            PaymentType::ONLINE->value => PaymentType::ONLINE->getLabel(),
            PaymentType::LOCAL->value => PaymentType::LOCAL->getLabel(),
            default => 'Inconnu',
        };
    }
} 