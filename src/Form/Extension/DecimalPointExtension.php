<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DecimalPointExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [NumberType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('html5', true);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $value = $event->getData();

            if (is_string($value)) {
                $event->setData(str_replace([',', ' ', "\u{a0}", "\u{202f}"], ['.', '', '', ''], trim($value)));
            }
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['type'] = 'text';
        $view->vars['attr']['inputmode'] = 'decimal';
    }
}
