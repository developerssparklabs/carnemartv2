<?php

class SearchStoreController
{
    public static function render_formulario(): string
    {
        $form = new SearchForm();
        return $form->formulario_search_store();
    }
}
