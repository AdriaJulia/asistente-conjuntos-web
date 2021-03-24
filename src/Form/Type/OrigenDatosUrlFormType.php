<?php

namespace App\Form\Type;

use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\TipoBaseDatosEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

/*
 * Descripción: Es clase la que define el formulario paso2 en su formato url      
 */
class OrigenDatosUrlFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoOrigen', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                  ],
                  'choices' => TipoOrigenDatosEnum::getValues(),
                  'attr' => [
                      'class' => 'select big',
                  ],
                  'data' => 'url'
            ])
            ->add('url', UrlType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'placeholder' => 'Escriba url para el origen de los datos',
                ],
                'help' =>' formatos admitidos xml, json, csv, xls',
                'required' => true
            ])
            ->add('modoFormulario', HiddenType::class,[
                "row_attr" => [
                    "class" => "form-group",
                ],
                "data" =>  "test",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allowed_states' => null,
            'data_class' => OrigenDatosDto::class,
            'csrf_protection' => false,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
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
    public function validate($data, ExecutionContextInterface $context,$payload): void
    {
        $url = $data->url;
        $extension = strtolower(substr($url, -5));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $context->buildViolation('Por favor inserte una url')
                ->atPath('url')
                ->addViolation();
        } else if (!strpos($extension,"json") && !strpos($extension,"xml") && !strpos($extension,"csv") && ! strpos($extension,"xls")) {
            $context->buildViolation('Por favor inserte una url con los formatos señalados validos')
            ->atPath('url')
            ->addViolation();
        }
    }
}