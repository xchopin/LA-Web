<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TranslatorExtension
 * Useful Twig function for translating words or getting the languages available.
 *
 * @package AppBundle\TwigExtension
 */
class TranslatorExtension extends Twig_Extension
{

    /**
     * @var string
     */
    protected $country_id;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TranslatorExtension constructor.
     *
     * @param ContainerInterface $container
     * @param RequestStack $request_stack
     */
    public function __construct(ContainerInterface $container, RequestStack $request_stack)
    {
        $this->container = $container;
        $request = $request_stack->getCurrentRequest();
        $this->country_id = $request->get('_locale');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translate';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('dictionary', [$this, 'dictionary']),
            new Twig_SimpleFunction('languages', [$this, 'languagesAvailable']),
        ];
    }

    /**
     * Returns a dictionary of keywords for the current language.
     *
     * @return array
     */
    public function dictionary()
    {
        $dictionary = $this->container->getParameter('dictionaries')[$this->country_id];
        return $dictionary;
    }

    /**
     * Returns the list of the languages translated.
     *
     * @return array
     */
    public function languagesAvailable()
    {
       $languages = [];
       foreach ($this->container->getParameter('dictionaries') as $languageId => $dictionary) {
           $languages += [ $dictionary['self_name'] => $languageId ];
       }
        return $languages;
    }
}

