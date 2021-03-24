<?php

namespace App\Form\Type;

use App\Form\Model\SoporteDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/*
 * Descripción: Es clase la que define el formulario solicitud de soporte   
 */
class SoporteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoPeticion', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => ['Reportar incidencia'=>'incidencia', 
                              'Realizar consulta'=> 'consulta',
                              'Solicitar mejora' => 'mejora'],
                'attr' => [
                    'class' => 'dropdown',
                ],
                'placeholder' => 'Seleccione una opción...',
                'help'=>'Indique el tipo de soporte que desea obtener',
                'label' => 'Tipo Petición',
                'required' => true])
            ->add('titulo', TextType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'placeholder' => 'Escriba el titulo de su solicitud',
                ],
                'label' => 'Título',
                'required' => true,
                'help'=>'Este texto será el titulo del correo que se le envía al administrador' 
            ])
            ->add('descripcion', TextareaType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'spellcheck' => 'true',
                    'placeholder' => 'Escriba su solicitud de soporte',
                 ],
               'required' => true,
               'label' => 'Descripción',
               'help'=>'Detalle en que consiste el soporte que desea obtener, o incidencia , o error que ha observado para poder ayudarle' 
            ])
            ->add('nombre', TextType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'placeholder' => 'Escriba su nombre, para dirigirnos a usted y poder identificarle',
                ],
                'label' => 'Su nombre',
                'required' => true,
                'help'=>'El administrador se dirigirá a usted, por este nombre' 
            ])
            ->add('emailContacto', TextType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'placeholder' => 'Escriba su email',
                ],
                'label' => 'Email de contacto',
                'required' => true,
                'help'=>'El administrador se pondrá en contacto con usetes a traves de este correo' 
            ])
            ->add('emailContacto2', TextType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'placeholder' => 'Escriba su email otra vez',
                ],
                'label' => 'Confirmanacion Email de contacto',
                'required' => true,
                'help'=>'Confirme su email de contacto' 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SoporteDto::class,
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
    public function validate(SoporteDto $data, ExecutionContextInterface $context,$payload): void
    {
        $email = $data->emailContacto;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $context->buildViolation('No es un email valido')
                ->atPath("emailContacto")
                ->addViolation();
        }

        if ($data->emailContacto != $data->emailContacto2) {
            $context->buildViolation('El email no es el mismo')
                ->atPath("emailContacto2")
                ->addViolation();
        }
    }
}