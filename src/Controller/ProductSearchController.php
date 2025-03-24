<?php

namespace App\Controller;

use App\Repository\ProductSearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductSearchController extends AbstractController
{
    #[Route('/api/products/search', name: 'api_products_search', methods: ['GET'])]
    public function search(Request $request, ProductSearchRepository $searchRepository, SerializerInterface $serializer): JsonResponse
    {
        $query = $request->query->get('q', '');
        $products = $searchRepository->searchByTitle($query);

        $data = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
                'digital' => $product->getDigital(),
            ];
        }, $products);

        return new JsonResponse(['results' => $data]);
    }

    #[Route('/api/products/search/price', name: 'api_products_search_price', methods: ['GET'])]
    public function searchByPrice(Request $request, ProductSearchRepository $searchRepository): JsonResponse
    {
        $min = (float) $request->query->get('min', 0);
        $max = (float) $request->query->get('max', PHP_FLOAT_MAX);
        
        $products = $searchRepository->searchByPriceRange($min, $max);

        $data = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
            ];
        }, $products);

        return new JsonResponse(['results' => $data]);
    }
} 