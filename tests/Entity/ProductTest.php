<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\StockList;
use App\Entity\Variant;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        $this->product = new Product();
    }

    public function testInitialState(): void
    {
        // Vérifier l'état initial de l'objet
        $this->assertNull($this->product->getId());
        $this->assertNull($this->product->getTitle());
        $this->assertNull($this->product->getPrice());
        $this->assertNull($this->product->getCategory());
        $this->assertEquals(0.0, $this->product->getPurchasePrice());
        $this->assertEquals(10, $this->product->getAlert());
        $this->assertFalse($this->product->getArchive());
        $this->assertFalse($this->product->getDigital());
        
        // Vérifier les collections
        $this->assertInstanceOf(Collection::class, $this->product->getVariants());
        $this->assertTrue($this->product->getVariants()->isEmpty());
        $this->assertInstanceOf(Collection::class, $this->product->getStockLists());
        $this->assertTrue($this->product->getStockLists()->isEmpty());
    }

    public function testTitleGetterAndSetter(): void
    {
        $title = "Smartphone XYZ";
        
        $this->product->setTitle($title);
        
        $this->assertEquals($title, $this->product->getTitle());
    }

    public function testPriceGetterAndSetter(): void
    {
        $price = 299.99;
        
        $this->product->setPrice($price);
        
        $this->assertEquals($price, $this->product->getPrice());
    }

    public function testPurchasePriceGetterAndSetter(): void
    {
        $purchasePrice = 199.99;
        
        $this->product->setPurchasePrice($purchasePrice);
        
        $this->assertEquals($purchasePrice, $this->product->getPurchasePrice());
    }

    public function testAlertGetterAndSetter(): void
    {
        $alert = 5;
        
        $this->product->setAlert($alert);
        
        $this->assertEquals($alert, $this->product->getAlert());
    }

    public function testArchiveGetterAndSetter(): void
    {
        $this->assertFalse($this->product->getArchive());
        
        $this->product->setArchive(true);
        $this->assertTrue($this->product->getArchive());
        
        $this->product->setArchive(false);
        $this->assertFalse($this->product->getArchive());
    }

    public function testDigitalGetterAndSetter(): void
    {
        $this->assertFalse($this->product->getDigital());
        
        $this->product->setDigital(true);
        $this->assertTrue($this->product->getDigital());
        
        $this->product->setDigital(false);
        $this->assertFalse($this->product->getDigital());
    }

    public function testCategoryRelation(): void
    {
        $category = new Category();
        
        // Test setter
        $this->product->setCategory($category);
        
        // Vérifications
        $this->assertSame($category, $this->product->getCategory());
        $this->assertTrue($category->getProduct()->contains($this->product));
        
        // Test avec null
        $this->product->setCategory(null);
        $this->assertNull($this->product->getCategory());
        $this->assertFalse($category->getProduct()->contains($this->product));
    }

    public function testAddAndRemoveVariant(): void
    {
        $variant = new Variant();
        
        // Test d'ajout
        $this->product->addVariant($variant);
        $this->assertTrue($this->product->getVariants()->contains($variant));
        $this->assertSame($this->product, $variant->getProduct());
        
        // Test de suppression
        $this->product->removeVariant($variant);
        $this->assertFalse($this->product->getVariants()->contains($variant));
        $this->assertNull($variant->getProduct());
    }

    public function testAddAndRemoveStockList(): void
    {
        $stockList = new StockList();
        
        // Test d'ajout
        $this->product->addStockList($stockList);
        $this->assertTrue($this->product->getStockLists()->contains($stockList));
        $this->assertSame($this->product, $stockList->getProduct());
        
        // Test de suppression
        $this->product->removeStockList($stockList);
        $this->assertFalse($this->product->getStockLists()->contains($stockList));
        $this->assertNull($stockList->getProduct());
    }
} 