<?php

namespace App\Form\Type;
use App\Service\Processor\Tool\OntologiasAlineacionTool;
use App\Form\Type\EntidadesCampoType;
use App\Form\Model\AlineacionDatosDto;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

/*
 * Descripción: Es la clase la que define el formulario de la alineación de los datos          
 */

class AlineacionDatosCamposFormType extends AbstractType
{

    private $ontologiasAlineacionTool;

    function __construct(OntologiasAlineacionTool $ontologiasAlineacionTool){
        $this->ontologiasAlineacionTool = $ontologiasAlineacionTool;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('alineacionRelaciones', HiddenType::class,[
            "row_attr" => [
                "class" => "form-group",
            ],
            'required' => false
        ]);

        $builder->add('modoFormulario', HiddenType::class,[
            "row_attr" => [
                "class" => "form-group",
            ],
            "data"=>"seleccion",
            'required' => false
        ]);

        $enlace =  "";
        if (array_values($options['allowed_decripcion'])[0] !== ""){
            $enlace =   array_values($options['allowed_decripcion'])[0] . 
            '<p></p><p>'
           . 'Para más información sobre la entidad seleccionada, puede acceder al siguiente enlace: <a target="_blank" href="' 
           . array_values($options['allowed_enlace'])[0]  
           . '">Haz click Aquí</a></p>';
            $enlace  =    $enlace = str_replace("\\n", "<br>", $enlace);
        }


        $builder->add('tipoAlineacion', ChoiceType::class, [
            "row_attr" => [
                "class" => "form-group"
            ],   
            'choices' => ["Alineamiento mediante XML de mapeo"=>"xml", "Alineamiento mediante asignación de atributos"=>"campos"],
            'attr' => [
                'class' => 'select big',
                'onchange' => ''
            ],
            //'placeholder' => 'Selecciona un modo de alineamiento...',
            'help'=>'',
            'label' => 'Selecciona modo de integración con el EI2A',
            'required' => true,
            'data'=> 'campos'
        ]);

        $builder->add('alineacionEntidad', ChoiceType::class, [
            "row_attr" => [
                "class" => "form-group"
            ],   
            'choices' => $this->ontologiasAlineacionTool->GetOntologias(),
            'attr' => [
                'class' => 'select big',
                'onchange' => ''
            ],
            'placeholder' => 'Selecciona la entidad principal...',
            'help'=>'',
            'label' => 'Selecciona entidad principal',
            'required' => false
        ]);
    
        $subentidades = $options['allowed_subentidades'];
        $subentidad =  ($options['allowed_subentidad']!=null) ? $options['allowed_subentidad'] : "";
        $display = (count($subentidades)==0) ? "display:none" : "display:block";
        $builder->add('subtipoEntidad', ChoiceType::class, [
            "row_attr" => [
                "class" => "form-group",
                "style" => $display
            ],   
            'choices' => $subentidades,
            'attr' => [
                'class' => 'select big',
                'onchange' => ''
            ],
            'placeholder' => 'Selecciona tipo de entidad',
            'help'=>'',
            'expanded' => false,
            'multiple' => false,
            'label' => 'Selecciona tipo de entidad',
            'required' => false
        ]);
        
        $descripcion = array_values($options['allowed_decripcion'])[0] ;
        $descripcion = str_replace("\\n", "<br>", $descripcion);
        $builder->add('descripcionEntidad', TextareaType::class, [
            "row_attr" => [
                "class" => "form-group",
                "id" => "descripcionEntidadId",
                "value" => "",
            ],  
            'data' =>  $descripcion,
            'help_html' => true,
            'help'=>$enlace,
            'label' => 'Descripción de la entidad seleccionada', 

            'disabled' =>true,
            'required' => false
        ]);

        // si recibo desde el constructor una lista de entidades principales creo un control para cada campo
        // con un combo con las opciones
        if (count($options['allowed_ontologias'])>0) {
            $builder->add('alineacionEntidades', EntidadesCampoType::class, [
                'label_attr' =>  [
                    "style" =>"display:none"
                ],
                'allowed_campos' => $options['allowed_campos'],
                'allowed_ontologias' => $options['allowed_ontologias'],
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allowed_campos' => null,
            'allowed_ontologias' => null,
            'allowed_enlace' => null,
            'allowed_decripcion' => null,
            'allowed_subentidades' => null,
            'allowed_subentidad' => null,
            'data_class' => AlineacionDatosDto::class,
            'csrf_protection' => false,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);

        $resolver->setAllowedTypes('allowed_campos', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('allowed_ontologias', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('allowed_enlace', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('allowed_decripcion', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('allowed_subentidades', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('allowed_subentidad', ['null', 'string', 'array']);

        $resolver->setNormalizer('allowed_enlace', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states), array_values($states));
        });

        $resolver->setNormalizer('allowed_decripcion', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states), array_values($states));
        });

        $resolver->setNormalizer('allowed_campos', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states), array_values($states));
        });

               
        $resolver->setNormalizer('allowed_ontologias', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_values($states), array_keys($states));
        });

        $resolver->setNormalizer('allowed_subentidades', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_keys($states), array_values($states));
        });

        $resolver->setNormalizer('allowed_subentidad', static function (Options $options, $states) {
            if (null === $states) {
                return $states;
            }

            if (is_string($states)) {
                $states = (array) $states;
            }

            return array_combine(array_keys($states), array_values($states));
        });
        
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
        $alineacionRelaciones = str_replace("{}","",$data->alineacionRelaciones);
        if ($data->modoFormulario=="guardar" && empty($alineacionRelaciones)) {
            $context->buildViolation('Por favor relaciona algún campo con la entidad en en la lista de campos a alinear. Recuerda pulsar el botón \'Asignar atributo\' en cada uno de tus seleccionados.')
            ->atPath('tipoAlineacion')
            ->addViolation();
        } else if ($data->modoFormulario=="guardar" && (strpos($alineacionRelaciones,"(requerido)")==false)) {
            $context->buildViolation('Por favor relaciona el identificador de la entidad (requerido) en la lista de campos a alinear.')
            ->atPath('tipoAlineacion')
            ->addViolation();
        }   

    }
}