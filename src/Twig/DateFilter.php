<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('date_diff', [$this, 'dateFilter']),
        ];
    }

    public function dateFilter($date)
    {
        $now = new \DateTime('now', timezone_open('Europe/Paris'));
        $diff = $now->diff($date);

        if ($diff->format('%y') > 0) {
            if ($diff->format('%y') > 1) {
                return 'il y a '.$diff->format('%y').' ans';
            }

            return 'il y a '.$diff->format('%y').' an';
        } elseif ($diff->format('%m') > 0) {
            return 'il y a '.$diff->format('%m').' mois';
        } elseif ($diff->format('%a') > 0) {
            if ($diff->format('%a') > 1) {
                return 'il y a '.$diff->format('%a').' jours';
            }

            return 'il y a '.$diff->format('%a').' jour';
        } elseif ($diff->format('%h') > 0) {
            if ($diff->format('%h') > 1) {
                return 'il y a '.$diff->format('%h').' heures';
            }

            return 'il y a '.$diff->format('%h').' heure';
        } elseif ($diff->format('%i') > 0) {
            if ($diff->format('%i') > 1) {
                return 'il y a '.$diff->format('%i').' minutes';
            }

            return 'il y a '.$diff->format('%i').' minute';
        } elseif ($diff->format('%s') > 0) {
            if ($diff->format('%s') > 1) {
                return 'il y a '.$diff->format('%s').' secondes';
            }

            return 'il y a '.$diff->format('%s').' seconde';
        }
    }
}
