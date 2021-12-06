<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TransactionComponent extends Component
{

    public $transaction;
    public $istest;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($transaction, $istest)
    {
        $this->transaction = $transaction;
        $this->istest = $istest;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.transaction-component');
    }
}
