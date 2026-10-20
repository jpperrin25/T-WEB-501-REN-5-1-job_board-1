<?php

namespace App\Form;

use App\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $builder->getData();

        $builder
            ->add('title')
            ->add('description')
            ->add('salary')
            ->add('schedule')
            ->add('type')
            ->add('address')
            ->add('postal_code')
            ->add('city')
            ->add('company', CompanyType::class)
        ;
        // if ($options['admin-creation'] || $options['company-creation']) {
        //     $builder->add('company', EntityType::class, [
        //         'class' => CompanyType::class,
        //         'choice_label' => 'name'
        //     ]);
        // }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job::class,
        ]);
    }
}
