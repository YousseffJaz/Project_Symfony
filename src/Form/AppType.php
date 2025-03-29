<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class AppType extends AbstractType
{
    /**
     * getConfig : Configuration label et attr.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getConfig($label, $placeholder, $options = [])
    {
        return array_merge_recursive([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder,
                'class' => 'form-control',
            ],
        ], $options);
    }
}
