<?php

namespace App\Presentation\AboutUs;

use Nette;

final class AboutUsPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    )
    {}

    public function renderAbout(): void
    {

    }
}