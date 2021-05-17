<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class RecipeSlugger
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Takes a string and turns it into a slug
     *
     * @param string $string
     * @return string
     */
    public function slugify(string $string, int $id): string
    {
        $slug = $this->slugger->slug($string) . '-' . $id;
        return strtolower($slug);
    }
}