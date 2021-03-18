<?php

namespace App\Form\Type;

use App\Form\Model\DescripcionDatosDto;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Descripción: Es clase la que define el formulario popup para el cambio de estado de un conjunto datos
 *               El que sale desde la ficha del conjunto de datos al pulsar un botón.         
 */

class DescripcionDatosWorkFlowFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('descripcion', TextType::class)
            ->add('estado', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DescripcionDatosDto::class,
            'csrf_protection' => false
        ]);

    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function getName()
    {
        return '';
    }
}