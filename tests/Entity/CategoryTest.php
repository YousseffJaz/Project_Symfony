<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use TypeError;

class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    public function testInitialState(): void
    {
        // Vérifier l'état initial de l'objet
        $this->assertNull($this->category->getId());
        $this->assertNull($this->category->getName());
        $this->assertInstanceOf(Collection::class, $this->category->getProduct());
        $this->assertTrue($this->category->getProduct()->isEmpty());
    }

    public function testNameGetterAndSetter(): void
    {
        $name = "Électronique";
        
        // Test du setter
        $this->category->setName($name);
        
        // Test du getter
        $this->assertEquals($name, $this->category->getName());
    }

    public function testAddProduct(): void
    {
        $product = new Product();
        
        // Test d'ajout d'un produit
        $this->category->addProduct($product);
        
        // Vérifications
        $this->assertTrue($this->category->getProduct()->contains($product));
        $this->assertSame($this->category, $product->getCategory());
        $this->assertEquals(1, $this->category->getProduct()->count());
    }

    public function testAddSameProductTwice(): void
    {
        $product = new Product();
        
        // Ajouter le même produit deux fois
        $this->category->addProduct($product);
        $this->category->addProduct($product);
        
        // Vérifier qu'il n'y a qu'une seule instance du produit
        $this->assertEquals(1, $this->category->getProduct()->count());
    }

    public function testRemoveProduct(): void
    {
        $product = new Product();
        
        // Ajouter puis supprimer un produit
        $this->category->addProduct($product);
        $this->category->removeProduct($product);
        
        // Vérifications
        $this->assertFalse($this->category->getProduct()->contains($product));
        $this->assertNull($product->getCategory());
        $this->assertEquals(0, $this->category->getProduct()->count());
    }

    public function testRemoveNonExistentProduct(): void
    {
        $product = new Product();
        
        // Tenter de supprimer un produit qui n'a pas été ajouté
        $this->category->removeProduct($product);
        
        // Vérifier que la collection est toujours vide
        $this->assertEquals(0, $this->category->getProduct()->count());
    }

    public function testMultipleProducts(): void
    {
        $products = [
            new Product(),
            new Product(),
            new Product()
        ];
        
        // Ajouter plusieurs produits
        foreach ($products as $product) {
            $this->category->addProduct($product);
        }
        
        // Vérifications
        $this->assertEquals(count($products), $this->category->getProduct()->count());
        foreach ($products as $product) {
            $this->assertTrue($this->category->getProduct()->contains($product));
            $this->assertSame($this->category, $product->getCategory());
        }
    }

    /**
     * @dataProvider invalidNameProvider
     */
    public function testNameWithInvalidValues(string $invalidName, string $expectedExceptionClass): void
    {
        $this->expectException($expectedExceptionClass);
        $this->category->setName($invalidName);
    }

    public function invalidNameProvider(): array
    {
        return [
            'nom vide' => ['', InvalidArgumentException::class],
            'nom avec espaces uniquement' => ['   ', InvalidArgumentException::class],
            'nom trop long' => [str_repeat('a', 256), InvalidArgumentException::class]
        ];
    }

    public function testNameWithNull(): void
    {
        $this->expectException(TypeError::class);
        $this->category->setName(null);
    }
} 