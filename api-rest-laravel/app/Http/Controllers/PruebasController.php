<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class PruebasController extends Controller
{
    public function index()
    {
        $titulo = 'Animales';
        $animales = ['Perro', 'Gato', 'Rana','Tortuga'];
        
        return view("pruebas.index", array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
    
    public function testOrm()
    {
        $posts = Post::All();
        //var_dump($post);
        foreach($posts as $post){
            echo $post->title;
            echo '<br/>';
            echo $post->content;
            echo '<hr/>';
        }
        die();
    }
}
