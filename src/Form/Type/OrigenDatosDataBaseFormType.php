<?php

namespace App\Form\Type;

use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\TipoBaseDatosEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/*
 * Descripción: Es clase la que define el formulario paso2 en su formato Base datos       
 */
class OrigenDatosDataBaseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoOrigen', ChoiceType::class,[
                  "row_attr" => [
                    "class" => "form-group"
                  ],
                  'choices' => TipoOrigenDatosEnum::getValues(),
                  'attr' => [
                      'class' => 'select big',
                  ],
                  'data' => 'database'
                ])
            ->add('tipoBaseDatos', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'required' => true,
                'placeholder' => 'Seleccione una opción...',
                'choices' => TipoBaseDatosEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown'
                ]
            ])
            ->add('host', TextType::class,[ 
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escriba el nombre del host',
                ],
                'help' =>'El nombre del host o ip de la conexión ejemplos: en SQLserever localhost\sqlexpress, en Mysql localhost',
                'required' => true
            ])
            ->add('puerto', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escriba el puerto',
                ],
                'help' =>'El puerto obligatorio aunque sea el de por defecto',
                'required' => true
            ])
            /*
            ->add('servicio', TextType::class,[
                'attr' => [
                    'placeholder' => 'Escriba el servicio',
                ],
                'help' =>'Nombre del servicio o instancia de la BD',
                'required' => true
            ])
            */
            ->add('esquema', TextType::class,[ 
                "row_attr" => [ 
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escriba el esquema, o nombre de la Base datos',
                ],
                'help' =>'Nombre del esquema de la BD. En sqlserver por ejemplo sería Northwind, en Mysql el nombre del esquema',
                'required' => true
            ])
            ->add('tabla', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escriba el la tabla o vista',
                ],
                'help' =>'Nombre de la tabla o vista',
                'required' => true
            ])
            ->add('usuarioDB', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escriba del usuario de la BD',
                ],
                'help' =>'Nombre del usuario de la BD',
                'label' => 'Usuario',
                'required' => true
            ])
            ->add('contrasenaDB', TextType::class,[
                "row_attr" => [
                    "name" => "contrasenaDB",
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escriba la contraseña  de la BD',
                ],
                'help' =>'Contraseña del usuario de la BD',
                'label' => 'Contraseña',
                'required' => true
            ])
            ->add('modoFormulario', HiddenType::class,[
                "row_attr" => [
                    "id" => "modoFormulario",
                    "class" => "form-group",
                ],
                "data" => "test"
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
        if (empty($data->tipoBaseDatos)) {
            $context->buildViolation('Seleccione una un tipo de base datos')
            ->atPath('tipoBaseDatos')
            ->addViolation();
        }

        if (empty($data->host)) {
            $context->buildViolation('El host no es valido')
            ->atPath('host')
            ->addViolation();
        }
        if (empty($data->puerto) || strlen($data->puerto)<=3) {
            $context->buildViolation('El puerto no es valido')
            ->atPath('puerto')
            ->addViolation();
        }
        if (empty($data->esquema)) {
            $context->buildViolation('El esquema no es valido')
            ->atPath('esquema')
            ->addViolation();
        }
        if (empty($data->tabla)) {
            $context->buildViolation('La tabla no es valida')
            ->atPath('tabla')
            ->addViolation();
        }
        if (empty($data->usuarioDB)) {
            $context->buildViolation('El usuario no es valido')
            ->atPath('usuarioDB')
            ->addViolation();
        }

        if (empty($data->contrasenaDB)) {
            $context->buildViolation('La contraseña no es valida')
            ->atPath('contrasenaDB')
            ->addViolation();
        }        
    }
}