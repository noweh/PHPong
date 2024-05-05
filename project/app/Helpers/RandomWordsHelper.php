<?php

namespace App\Helpers;

class RandomWordsHelper
{
    public static function generateWords()
    {
        // List of words and their colors
        $words = [
            'namespace' => 'brun',
            'use' => 'brun',
            'class' => 'brun',
            'extends' => 'brun',
            'public' => 'brun',
            'private' => 'brun',
            'protected' => 'brun',
            'static' => 'brun',
            'new' => 'brun',
            'this' => 'brun',
            'self' => 'brun',
            'parent' => 'brun',
            'true' => 'brun',
            'false' => 'brun',
            'null' => 'brun',
            'if' => 'brun',
            'else' => 'brun',
            'elseif' => 'brun',
            'while' => 'brun',
            'do' => 'brun',
            'for' => 'brun',
            'foreach' => 'brun',
            'as' => 'brun',
            'try' => 'brun',
            'catch' => 'brun',
            'finally' => 'brun',
            'throw' => 'brun',
            'die' => 'brun',
            'exit' => 'brun',
            ';' => 'white',
            '(' => 'white',
            ')' => 'white',
            '{' => 'white',
            '}' => 'white',
            '[' => 'white',
            ']' => 'white',
            '->' => 'white',
            '::' => 'white',
            '=>' => 'white',
            '=' => 'white',
            '==' => 'white',
            '===' => 'white',
            '!=' => 'white',
            '!==' => 'white',
            '<' => 'white',
            '>' => 'white',
            '<=' => 'white',
            '>=' => 'white',
            '+' => 'white',
            '-' => 'white',
            '*' => 'white',
            '/' => 'white',
            '%' => 'white',
            '++' => 'white',
            '--' => 'white',
            '.' => 'white',
            '()' => 'white',
            '[]' => 'white',
            'render' => 'blue',
            'mount' => 'blue',
            'hydrate' => 'blue',
            'dehydrate' => 'blue',
            'updating' => 'blue',
            'view' => 'blue',
            'component' => 'blue',
            'shuffle' => 'blue',
            'array_keys' => 'blue',
            'wire' => 'blue',
            '$fillable' => 'violet',
            '$hidden' => 'violet',
            '$casts' => 'violet',
            '$table' => 'violet',
            '$primaryKey' => 'violet',
            '$timestamps' => 'violet',
            '$dateFormat' => 'violet',
            '$attributes' => 'violet',
            '$relations' => 'violet',
            '$connection' => 'violet',
            '$appends' => 'violet',
            '$dispatchesEvents' => 'violet',
            '$observables' => 'violet',
            '$with' => 'violet',
            '$withCount' => 'violet',
            '$perPage' => 'violet',
            '$exists' => 'violet',
            '$wasRecentlyCreated' => 'violet',
            '$incrementing' => 'violet',
            '<span>' => 'yellow',
            '<div>' => 'yellow',
            '<a>' => 'yellow',
            '<p>' => 'yellow',
            '<h1>' => 'yellow',
            '<h2>' => 'yellow',
            '<h3>' => 'yellow',
            '<h4>' => 'yellow',
            '<h5>' => 'yellow',
            '<label>' => 'yellow',
            '<input>' => 'yellow',
            '<button>' => 'yellow',
            '<form>' => 'yellow',
            '<img>' => 'yellow',
            '<ul>' => 'yellow',
            '<ol>' => 'yellow',
            '<li>' => 'yellow',
            '<table>' => 'yellow',
            '<tr>' => 'yellow',
            '<td>' => 'yellow',
            '<th>' => 'yellow',
            '<thead>' => 'yellow',
            '<tbody>' => 'yellow',
            '<tfoot>' => 'yellow',
            '<caption>' => 'yellow',
            '<iframe>' => 'yellow',
            '<video>' => 'yellow',
            '<audio>' => 'yellow'
        ];

        $randomWords = [];
        for ($i = 0; $i < 20; $i++) {
            // Shuffle the words
            $keys = array_keys($words);
            shuffle($keys);

            // Return the shuffled words
            foreach ($keys as $key) {
                $randomWords[] = ['word' => $key, 'color' => $words[$key]];
            }
        }

        return $randomWords;
    }
}