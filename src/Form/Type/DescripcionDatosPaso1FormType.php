<?php

namespace App\Form\Type; 

use App\Enum\FinalidadDatosEnum;
use App\Service\Processor\Tool\IdentificadorUnicoTool;
use App\Form\Type\CoberturaGeograficaType;
use App\Form\Type\CoberturaTemporalType;
use App\Form\Type\IdiomasType;
use App\Form\Model\DescripcionDatosDto;
use App\Enum\FrecuenciaActualizacionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

/*
 * Descripción: Es clase la que define el formulario paso 1.1 de la descripcion de los datos de los datos          
 */

class DescripcionDatosPaso1FormType extends AbstractType
{

    private $identificadorUnicoTool;

    public function __construct(
        IdentificadorUnicoTool $IdentificadorUnicoTool
    ) {
        $this->identificadorUnicoTool = $IdentificadorUnicoTool;      
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $myCustomFormData = array(
            'aragon' => true, // checked
            'provincia' => false, // unchecked
            'comarca' => false, // unchecked
            'municipio' => false, // unchecked
            'otros' => false, // unchecked
        );

        $builder 
                 
            ->add('titulo', TextType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'placeholder' => 'Escribe un texto',
                    'class' => 'select big',
                ],
                'label' => 'Título*',
                'required' => false,
                'help'=>'Por favor, dale una denominación del conjunto de datos. El nombre que des al conjunto de datos es importante porque se convierte en su identificador.' 
            ])
            ->add('descripcion', TextareaType::class, [
                "row_attr" => [
                    "class" => "form-group",
                    "spellcheck"=>"true"
                ],
                'attr' => [
                    'spellcheck' => 'true',
                    'placeholder' => 'Escribe un texto detallado',
                 ],
                'required' => false,
                'label' => 'Descripción*',
                'help'=>'La descripción es la primera aproximación de un usuario a tu conjunto de datos, así que se debería comenzar contando brevemente qué contiene el mismo. 
                         Si el conjunto de datos contiene informaciones parciales, limitaciones o deficiencias este es el lugar en el que puedes mencionarlas de forma que los usuarios 
                         puedan saber el alcance de la información. En algunos casos los usuarios ayudan a mejorar la información, así que no desaproveches la oportunidad de acercarles 
                         la realidad del dato.'
            ])
            ->add('tematica', ChoiceType::class, [ 
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => FinalidadDatosEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown'
                ],
                'label' => 'Temática*',
                'placeholder' => 'Selecciona una opción...',
                'required' => false,
                'help'=>'Estos son los temas conforme a la Norma Técnica de Interoperabilidad: elige el que crea que se adapta mejor a la información que contiene tu conjunto de datos.'
            ])
            ->add('etiquetas', TextType::class,[
                "row_attr" => [
                    'id' => 'divetiquetas',
                    "class" => "form-group"
                ],
                "attr"=>[
                    'id' => 'inputetiquetas',
                    'data-role' => 'tagsinput',
                    'placeholder' => 'Inserta una y pulsa ENTER'
                ],
                "required" => false,
                'help_html' => true,
                'label'=>'Etiquetas',
                'help' => "Por favor, introduce un listado de etiquetas que describan el contenido de tu conjunto de datos. 
                           Utiliza palabras comunes para describirlo. A poder ser utiliza palabras de las que te sugiere el formulario, ya que son palabras que provienen de 
                           EuroVoc ( <a href ='http://eurovoc.europa.eu/drupal/?q=es'>http://eurovoc.europa.eu/drupal/?q=es</a>) y su uso mejora mucho la interoperabilidad 
                           del conjunto de datos.<br> Escribe la primera letra en mayúscula y el resto en minúscula."
            ])
            ->add('frecuenciaActulizacion', ChoiceType::class, [
                "row_attr" => [
                    "class" => "form-group"
                ],
                'choices' => FrecuenciaActualizacionEnum::getValues(),
                'attr' => [
                    'class' => 'dropdown',
                ],
                'placeholder' => 'Selecciona una opción...',
                'help'=>'Por favor, indica la frecuencia con la que se actualiza la información del conjunto de datos',
                'label' => 'Frecuencia de actualización',
                'required' => false
            ])
            ->add('coberturaTemporal', CoberturaTemporalType::class,[
                    "row_attr" => [
                        'id' => 'coberturaTemporal',
                        "class" => "form-group"
                    ],
                    'invalid_message' => 'Una de las fechas introducida no tiene un formato correcto',
                    'label' =>'Cobertura temporal del conjunto de datos',
                    'help' => 'Por favor, indica en este campo el periodo temporal del que contiene información tu conjunto de datos. Si tu conjunto de datos está vivo y 
                               se va refrescando a medida que pasa el tiempo, deja seleccionada la casilla de selección que aparece en la parte de "hasta…". 
                               En ese caso entenderemos que tu conjunto de datos contiene información desde la fecha que indiques hasta la actualidad',
                    'required' => false
            ])  
            ->add('coberturaGeografica', HiddenType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
            ]) 
            ->add('coberturasGeograficas', CoberturaGeograficaType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                'label' => 'Cobertura geográfica',
                'data' =>  $myCustomFormData,
                'help' => 'Por favor introduce el ámbito geográfico del que tu conjunto de datos contiene información. 
                           Únicamente es posible escribir dentro de una de las opciones que se muestran y además hay que hacerlo con uno de los territorios que se da en los 
                           listados (salvo si se rellena el campo otro).'
            ])
            ->add('coberturaIdioma', IdiomasType::class,[
                "row_attr" => [
                    "class" => "form-group",
                ],
                'label' => 'Idiomas',
                'help' => ' Por favor, selecciona el idioma o idiomas en los que existe información en tu conjunto de datos',
                'required' => false,
            ])
            ->add('nivelDetalle', TextareaType::class,[
                "row_attr" => [
                    "class" => "form-group"
                ],
                "attr"=>[
                    'placeholder' => 'Escribe un texto detallado...',
                    "spellcheck"=>"true"
                ],
                'required' => false,
                'label'=>'Nivel de detalle',
                'help_html' => true,
                'help' => 'Este campo debe indicar el menor nivel de detalle al que se refiere el conjunto de datos. 
                           El menor nivel de detalle se puede referir a diferentes "dimensiones" del conjunto de datos si es que este las tuviera, 
                           por lo que en este campo se admite más de una palabra. Por ejemplo el menor nivel de detalle dentro de una "dimensión" 
                           temporal podrían ser segundos, minutos, horas…; en la "dimensión" espacial podría ser calle, código postal, municipio…; 
                           en la "dimensión" de entidades podría ser persona, escuela, parque natural. <br> Escribe la primera letra de cada nivel 
                           de detalle en mayúscula y el resto en minúscula'
             ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'help' => null,
            'data_class' => DescripcionDatosDto::class,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
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

    public function validate($data, ExecutionContextInterface $context,$payload): void
    {
       if (empty($data->titulo)){
            $context->buildViolation('El título no puede estar vacío')
            ->atPath("titulo")
            ->addViolation();
       }
       $this->identificadorUnicoTool->Inicializa();
       if (!isset($data->id)) {
            if ($this->identificadorUnicoTool->ExiteIdentificador($data->titulo)){
                $context->buildViolation('Ya existe un conjunto de datos con ese título')
                ->atPath("titulo")
                ->addViolation();
            }
       }

       if (empty($data->descripcion)){
            $context->buildViolation('La descripción no puede estar vacía')
            ->atPath("descripcion")
            ->addViolation();
       }

       if (empty($data->tematica)){
        $context->buildViolation('La temática no puede estar vacía')
        ->atPath("tematica")
        ->addViolation();
       }
    }
}