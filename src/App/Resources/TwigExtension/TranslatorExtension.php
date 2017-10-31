<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Resources\TwigExtension;

use Slim\Http\Request;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class TranslatorExtension
 * Useful Twig function for translating words or getting the languages available
 * @package App\Resources\TwigExtension
 */
class TranslatorExtension extends Twig_Extension
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $country_id;

    public function __construct(Request $request, $country_id = 'fr')
    {
        $this->request = $request;
        $this->country_id = $country_id;
    }

    public function getName()
    {
        return 'translate';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('dictionary', [$this, 'dictionary']),
            new Twig_SimpleFunction('languages', [$this, 'languagesAvailable']),
        ];
    }

    public function dictionary()
    {
        $file = __DIR__ . '/../' . DICTIONARY_PATH . $this->country_id . '.json';
        $dictionary = json_decode(file_get_contents($file), true);

        return $dictionary;
    }

    public function languagesAvailable()
    {
        $languages = [];

        foreach (glob(__DIR__ . '/../' . DICTIONARY_PATH . '*.json') as $file) {
            $language = json_decode(file_get_contents($file), GLOB_BRACE);
            $languages += [ $language['self_name'] => substr(basename($file), 0, 2) ];
        }

        return $languages;
    }
}

