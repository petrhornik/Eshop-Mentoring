<?php declare(strict_types=1); // deklarace nutnosti psaní typů

namespace App\Presentation\Home;

use Nette; // deklarace užití nette


final class HomePresenter extends Nette\Application\UI\Presenter    // inicializace presenteru z DI kontejneru (továrny)
{
    public function __construct(    // konstruktor fce. (jako v JS class) tvoří proměnné dostupné uvnitř tohoto odjektu
      private Nette\Database\Explorer $database,
    ){}

    public function renderDefault(): void   // Za keyword render napíšu cammelCased název šablony která se má vyrenderovat a jak
    {
        $this->template->products = $this->database    // uložím si data zavolaná z databáze do místní prom. posts (dostupné v default.latte)
            ->table('products')    // název tabulky
            ->order('creation_date DESC') // atributy SQL příkazu - toto je konkrétně order
            ->limit(10); // další atribut SQL
    }
}
