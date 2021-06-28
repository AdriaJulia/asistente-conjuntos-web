<?php

namespace App\Form\Type;

use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\TipoBaseDatosEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                  'label'=>'Acceso al recurso:',
                  'help' => 'Seleccione la forma de cómo se va a obtener el recurso.',
                  'data' => 'database'
                ])
            ->add('nombre', textType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'attr' => [
                    'class' => 'select big',
                    'placeholder' => 'Introduce un nombre descriptivo',  
                ],
                'label'=>'Nombre:',
                'help' => 'Introduce un nombre descriptivo puedes utilizar el mismo nombre que para el conjunto de datos.',
                'required' => true
            ])
            ->add('descripcion', TextareaType::class,[
                "row_attr" => [
                  "class" => "form-group"
                ],
                'attr' => [
                    'class' => 'select big',
                    'placeholder' => 'Escribe un texto detallado',  
                ],
                'label'=>'Descripción:',
                'help' => 'Si deseas dar más detalle sobre los datos',
                'required' => false
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
                    'placeholder' => 'Escribe el nombre del host',
                ],
                'help' =>'El nombre del host o ip de la conexión ejemplos: en SQLserever localhost\sqlexpress, en Mysql localhost',
                'required' => true
            ])
            ->add('puerto', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escribe el puerto',
                ],
                'help' =>'El puerto obligatorio aunque sea el de por defecto',
                'required' => true
            ])
            ->add('esquema', TextType::class,[ 
                "row_attr" => [ 
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escribe el esquema, o nombre de la Base datos',
                ],
                'help_html' => true,
                'help' =>'Nombre del esquema de la BD. En sqlserver por ejemplo sería Northwind, en Mysql el nombre del esquema, en Oracle el workspace. <br>' .
                         'Opcional para Oracle.',
                'required' => false,
                'label' => "Esquema*"
            ])
            ->add('servicio', TextType::class,[ 
                "row_attr" => [ 
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escribe otros parámetros',
                ],
                'help_html' => true,
                'help' => 'Otros parámetros para la conexión en formato uri. <br>' .
                          'En Oracle se añadirá a CONNECT_DATA. Ejemplo de textos posibles: SID=orac, o SERVICE_NAME=preapp1.dga.eso <br>' .
                          'En Mysql y/o PostgresSQL se añadirá como parámetro de la uri, Ejemplo de textos posibles: charset=utf8&init_command=SET NAMES UTF8',
                'label' => 'Otros parámetros',
                'required' => false
            ])
            ->add('tabla', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escribe el la tabla o vista',
                ],
                'help_html' => true,
                'help' =>'Nombre de la tabla o vista.<br>'. 
                         'Es posible que en algunos casos, se necesite especificar el prefijo de la tabla o vista como el esquema de seguridad.<br>' .
                         'Ejemplo de textos posibles: tabla, prefijo.tabla',
                'required' => true
            ])
            ->add('usuarioDB', TextType::class,[
                "row_attr" => [
                    "class" => "form-group"
                  ],
                'attr' => [
                    'placeholder' => 'Escribe del usuario de la BD',
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
                    'placeholder' => 'Escribe la contraseña  de la BD',
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
        if (empty($data->nombre)) {
            $context->buildViolation('El nombre no puede estar vacío')
            ->atPath('nombre')
            ->addViolation();
        }
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
        if ($data->tipoBaseDatos!=TipoBaseDatosEnum::ORACLE) {
            if (empty($data->esquema)) {
                $context->buildViolation('El esquema no es valido')
                ->atPath('esquema')
                ->addViolation();
            }
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