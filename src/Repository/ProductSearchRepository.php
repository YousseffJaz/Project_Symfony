<?php

namespace App\Repository;

use FOS\ElasticaBundle\Repository;

class ProductSearchRepository extends Repository
{
    public function searchByTitle(string $query)
    {
        $query = [
            'query' => [
                'match' => [
                    'title' => $query
                ]
            ]
        ];

        return $this->find($query);
    }

    public function searchByPriceRange(float $min, float $max)
    {
        $query = [
            'query' => [
                'range' => [
                    'price' => [
                        'gte' => $min,
                        'lte' => $max
                    ]
                ]
            ]
        ];

        return $this->find($query);
    }
} 