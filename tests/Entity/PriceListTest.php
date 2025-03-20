<?php

namespace App\Tests\Entity;

use App\Entity\PriceList;
use App\Entity\Variant;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class PriceListTest extends TestCase
{
    private PriceList $priceList;

    protected function setUp(): void
    {
        $this->priceList = new PriceList();
    }

    public function testInitialState(): void
    {
        // Vérifier l'état initial de l'objet
        $this->assertNull($this->priceList->getId());
        $this->assertNull($this->priceList->getTitle());
        $this->assertEquals(0.0, $this->priceList->getPrice());
        $this->assertNull($this->priceList->getVariant());
    }

    public function testTitleGetterAndSetter(): void
    {
        $title = "Prix standard";
        
        // Test du setter
        $this->priceList->setTitle($title);
        
        // Test du getter
        $this->assertEquals($title, $this->priceList->getTitle());
    }

    public function testPriceGetterAndSetter(): void
    {
        $price = 19.99;
        
        // Test du setter
        $this->priceList->setPrice($price);
        
        // Test du getter
        $this->assertEquals($price, $this->priceList->getPrice());
    }

    public function testVariantGetterAndSetter(): void
    {
        $variant = new Variant();
        
        // Test du setter
        $this->priceList->setVariant($variant);
        
        // Test du getter
        $this->assertSame($variant, $this->priceList->getVariant());
    }

    /**
     * @dataProvider invalidPriceProvider
     */
    public function testPriceWithInvalidValues(float $invalidPrice): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->priceList->setPrice($invalidPrice);
    }

    public function invalidPriceProvider(): array
    {
        return [
            'prix négatif' => [-10.0],
            'prix trop élevé' => [1000000.0],
            'prix zéro' => [0.0]
        ];
    }

    public function testTitleWithEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->priceList->setTitle('');
    }

    public function testTitleWithTooLongString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $longTitle = str_repeat('a', 256); // Crée une chaîne de 256 caractères
        $this->priceList->setTitle($longTitle);
    }

    public function testVariantRelationship(): void
    {
        $variant1 = new Variant();
        $variant2 = new Variant();

        // Test changement de variant
        $this->priceList->setVariant($variant1);
        $this->assertSame($variant1, $this->priceList->getVariant());

        $this->priceList->setVariant($variant2);
        $this->assertSame($variant2, $this->priceList->getVariant());
        $this->assertNotSame($variant1, $this->priceList->getVariant());

        // Test avec null
        $this->priceList->setVariant(null);
        $this->assertNull($this->priceList->getVariant());
    }

    public function testPriceFormat(): void
    {
        $prices = [
            19.99,
            20.00,
            99.99,
            100.50
        ];

        foreach ($prices as $price) {
            $this->priceList->setPrice($price);
            // Vérifie que le prix est bien un float avec 2 décimales maximum
            $this->assertIsFloat($this->priceList->getPrice());
            $this->assertLessThanOrEqual(2, strlen(substr(strrchr((string)$this->priceList->getPrice(), "."), 1)));
        }
    }
} 