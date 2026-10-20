<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $builder->getData();
        $builder
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('phone')
            ->add('address')
            ->add('postal_code')
            ->add('city')
        ;
        if ($user?->getCompany() !== null) {
            $builder->add('company', CompanyType::class);
        } else {
            $builder->add('graduate');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
